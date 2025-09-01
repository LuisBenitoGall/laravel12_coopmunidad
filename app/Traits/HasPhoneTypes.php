<?php
// app/Models/Traits/HasPhoneTypes.php

namespace App\Traits;

trait HasPhoneTypes{
    /**
     * Catálogo de tipos de teléfono: clave => [translation_key, icono opcional, ...]
     * Usa claves estables (no traducidas) para almacenamiento.
     */
    public static function phoneTypeCatalog(): array{
        return [
            'mobile'   => ['label' => 'movil',      'icon' => 'la-mobile'],
            'landline' => ['label' => 'fijo',       'icon' => 'la-phone'],
            'work'     => ['label' => 'trabajo',    'icon' => 'la-building'],
            'home'     => ['label' => 'hogar',      'icon' => 'la-home'],
            'fax'      => ['label' => 'fax',        'icon' => 'la-fax'],
            'other'    => ['label' => 'otros',      'icon' => 'la-asterisk'],
        ];
    }

    /** Sólo las claves válidas, útil para validación (Rule::in(...)) */
    public static function phoneTypeKeys(): array{
        return array_keys(static::phoneTypeCatalog());
    }

    /** Opciones “listas para un select” (clave => etiqueta traducida) */
    public static function phoneTypeOptions(?string $locale = null): array{
        $out = [];
        foreach (static::phoneTypeCatalog() as $key => $meta) {
            $out[$key] = __($meta['label'], locale: $locale);
        }
        return $out;
    }

    /** Traduce una clave concreta; devuelve la clave si no existe */
    public static function phoneTypeLabel(string $key, ?string $locale = null): string{
        $catalog = static::phoneTypeCatalog();
        return isset($catalog[$key])
            ? __($catalog[$key]['label'], locale: $locale)
            : $key;
    }

    /** Accessor cómodo: $phone->type_label */
    public function getTypeLabelAttribute(): string{
        return static::phoneTypeLabel($this->type);
    }
}
