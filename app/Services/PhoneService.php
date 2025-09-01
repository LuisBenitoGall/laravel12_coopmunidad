<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Phone;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhoneService{
    /**
     * Crea un Phone para un owner dado, normalizando a E.164 y
     * garantizando unicidad y "un principal por owner" si corresponde.
     *
     * @param array $data (validated)
     * @return \App\Models\Phone
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): Phone{
        [$ownerClass, $ownerId] = [$data['phoneable_type'], (int) $data['phoneable_id']];

        // 1) Normalizar a E.164
        $e164 = $this->resolveE164($data);

        // 2) Chequeo de unicidad por owner + e164 (antes de crear)
        $exists = Phone::where('phoneable_type', $ownerClass)
            ->where('phoneable_id', $ownerId)
            ->where('e164', $e164)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'number' => 'Este número ya está registrado para esta entidad.',
            ]);
        }

        // 3) Crear registro
        $phone = new Phone();
        $phone->phoneable_type = $ownerClass;
        $phone->phoneable_id   = $ownerId;
        $phone->e164           = $e164;
        $phone->country_id     = $data['country_id'] ?? null;
        $phone->ext            = $data['ext'] ?? null;
        $phone->type           = $data['type'] ?? 'mobile';
        $phone->label          = $data['label'] ?? null;
        $phone->is_primary     = (bool) ($data['is_primary'] ?? false);
        $phone->is_whatsapp    = (bool) ($data['is_whatsapp'] ?? false);
        $phone->is_verified    = (bool) ($data['is_verified'] ?? false);
        $phone->verified_at    = $data['verified_at'] ?? null;
        $phone->notes          = $data['notes'] ?? null;

        $phone->save();

        // 4) Si es principal, desmarcar el resto del mismo owner
        if ($phone->is_primary) {
            Phone::where('phoneable_type', $ownerClass)
                ->where('phoneable_id', $ownerId)
                ->where('id', '!=', $phone->id)
                ->update(['is_primary' => false]);
        }

        return $phone;
    }

    /**
     * Devuelve el número en E.164 (+XXXXXXXXX), sin espacios ni separadores.
     * - Si llega 'e164', lo limpia y valida.
     * - Si llega 'number' + 'country_id', intenta parsear con libphonenumber usando el ISO2 del país.
     * - Fallback básico: si no hay libphonenumber y el número ya trae '+' y dígitos, lo acepta.
     */
    protected function resolveE164(array $data): string{
        // Preferimos 'e164' si viene
        if (!empty($data['e164'])) {
            return $this->sanitizeToE164($data['e164'], true);
        }

        $raw = (string) ($data['number'] ?? '');
        $raw = trim($raw);

        if (empty($raw)) {
            throw ValidationException::withMessages([
                'number' => 'Indica un número de teléfono.',
            ]);
        }

        // Si el usuario ya envía internacional (+...), lo limpiamos
        if (Str::startsWith($raw, ['+', '00'])) {
            return $this->sanitizeToE164($raw, true);
        }

        // Necesitamos país para interpretar números nacionales
        $countryId = $data['country_id'] ?? null;
        if (!$countryId) {
            throw ValidationException::withMessages([
                'country_id' => 'Indica el país para poder normalizar el número.',
            ]);
        }

        $country = Country::find($countryId);
        if (!$country || empty($country->iso2)) {
            throw ValidationException::withMessages([
                'country_id' => 'El país indicado no es válido o no tiene ISO2.',
            ]);
        }

        // Intentar con libphonenumber (si está instalada)
        $e164 = $this->tryLibPhoneNumber($raw, $country->iso2);
        if ($e164) {
            return $e164; // ya viene como +XXXXXXXX sin separadores
        }

        // Fallback: no podemos deducir prefijo internacional. Avisar.
        throw ValidationException::withMessages([
            'number' => 'No se pudo normalizar el número a formato internacional. Añade el prefijo (+XX) o instala libphonenumber.',
        ]);
    }

    /**
     * Limpia y valida un número internacional.
     * - Convierte prefijo "00" en "+".
     * - Elimina todo excepto "+" y dígitos.
     * - Asegura un único "+" inicial.
     */
    protected function sanitizeToE164(string $value, bool $mustBeInternational = false): string
    {
        $v = trim($value);
        // 00... -> +...
        if (Str::startsWith($v, '00')) {
            $v = '+' . substr($v, 2);
        }

        // Mantener solo + y dígitos
        $v = preg_replace('/[^0-9\+]/', '', $v) ?? '';

        // Asegurar solo un '+' al inicio
        $v = ltrim($v, '+');
        $v = '+' . $v;

        // Validación básica
        if (!preg_match('/^\+[0-9]{6,20}$/', $v)) {
            throw ValidationException::withMessages([
                'e164' => 'El número internacional no tiene un formato válido (+ y 6-20 dígitos).',
            ]);
        }

        return $v;
    }

    /**
     * Usa libphonenumber si está disponible.
     * @return string|null E.164 o null si no está disponible o falla
     */
    protected function tryLibPhoneNumber(string $raw, string $countryIso2): ?string
    {
        try {
            if (!class_exists(\libphonenumber\PhoneNumberUtil::class)) {
                return null;
            }
            $util = \libphonenumber\PhoneNumberUtil::getInstance();
            $proto = $util->parse($raw, strtoupper($countryIso2));
            if (!$util->isValidNumber($proto)) {
                return null;
            }
            return \libphonenumber\PhoneNumberUtil::getInstance()
                ->format($proto, \libphonenumber\PhoneNumberFormat::E164); // "+34XXXXXXXXX"
        } catch (\Throwable $e) {
            return null;
        }
    }
}
