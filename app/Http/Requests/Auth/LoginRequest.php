<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

//Models:
use App\Models\BlockedIp;

class LoginRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        // Bloqueo de IP´s:
        if($this->ipIsBlocked()){
            throw new AuthorizationException(__('ip_bloqueada'));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void{
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $ip = $this->ip();

        if(! Auth::attempt($credentials, $this->boolean('remember'))){
            RateLimiter::hit($this->throttleKey());

            $this->registerFailedAttempt($ip);

            throw ValidationException::withMessages([
                'email' => __('failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void{
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string{
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /** 
     * Logout.
     */
    public function logout(Request $request) {
        Auth::logout(); // Llama al método de logout de Laravel
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect('/'); // Redirige a la página de inicio o a donde desees
    }

    /**
     * Bloqueo de IP´s.
     */
    protected function ipIsBlocked(): bool{
        $ip = $this->ip();

        $blocked = BlockedIp::where('ip', $ip)->where('active', true)->exists();

        if($blocked){
            Log::warning("Intento de acceso bloqueado desde IP: $ip");
        }

        return $blocked;
    }

    /**
     * 
     */
    protected function registerFailedAttempt(string $ip): void{
        // Usamos cache para contar intentos por IP
        $key = 'failed_login_ip:' . $ip;
        $attempts = cache()->increment($key);

        // Tiempo de expiración del contador (30 min)
        cache()->put($key, $attempts, now()->addMinutes(30));

        if ($attempts >= 4) {
            // Guardar IP en base de datos si no existe ya
            \App\Models\BlockedIp::updateOrCreate(
                ['ip' => $ip],
                ['reason' => 'Exceso de intentos de login fallidos', 'active' => true]
            );

            Log::alert("IP bloqueada automáticamente por intentos fallidos: $ip");

            // Opcional: limpiar el contador para evitar duplicados
            cache()->forget($key);
        }
    }
}