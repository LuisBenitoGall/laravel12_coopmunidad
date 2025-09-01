<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

//Models:
use App\Models\Phone;

//Traits:
use App\Traits\HasPhoneTypes;

class PhoneStoreRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool{
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array{
        // Clases permitidas como owners (ajusta si usas morphMap y quieres alias)
        $allowedOwners = [
            \App\Models\User::class,
            \App\Models\Company::class,
            \App\Models\Community::class,
            \App\Models\Workplace::class,
        ];

        return [
            'phoneable_type' => ['required', 'string', Rule::in($allowedOwners)],
            'phoneable_id'   => ['required', 'integer', 'min:1'],

            // Puedes enviar 'e164' ya normalizado o 'number' crudo + country_id
            'e164'           => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9]{6,20}$/'],
            'number'         => ['required_without:e164', 'nullable', 'string', 'max:30'],
            'country_id'     => ['required_without:e164', 'nullable', 'integer', 'exists:countries,id'],

            'ext'            => ['nullable', 'string', 'max:10'],
            'type'           => ['nullable', 'string', Rule::in(self::phoneTypeKeys())],
            'label'          => ['nullable', 'string', 'max:50'],

            'is_primary'     => ['sometimes', 'boolean'],
            'is_whatsapp'    => ['sometimes', 'boolean'],

            // Opcionales, normalmente no se establecen en alta
            'is_verified'    => ['sometimes', 'boolean'],
            'verified_at'    => ['nullable', 'date'],
            'notes'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array{
        return [
            'phoneable_type.in' => 'El tipo de entidad no es válido.',
            'country_id.required_without' => 'Indica un país cuando no envíes el número en formato internacional (+...).',
            'e164.regex' => 'El formato del número internacional no es válido.',
        ];
    }
}
