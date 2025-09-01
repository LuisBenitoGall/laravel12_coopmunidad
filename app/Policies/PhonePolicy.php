<?php

namespace App\Policies;

//Models:
use App\Models\Phone;
use App\Models\User;

class PhonePolicy{
    public function __construct(){
        
    }

    /**
     * ¿Puede listar teléfonos?
     */
    public function viewAny(User $user): bool{
        // Acepta cualquiera de los dos permisos como "listar"
        return $user->can('phones.viewAny') || $user->can('phones.view');
    }

    /**
     * ¿Puede ver este teléfono concreto?
     */
    public function view(User $user, Phone $phone): bool{
        if (! $user->can('phones.view')) {
            return false;
        }

        // Regla por instancia: mismo ámbito (empresa activa) o pertenencia directa
        return $this->inCurrentCompany($user, $phone) || $this->belongsToUser($user, $phone);
    }

    /**
     * ¿Puede crear teléfonos?
     */
    public function create(User $user): bool{
        return $user->can('phones.create');
    }

    /**
     * ¿Puede actualizar este teléfono?
     */
    public function update(User $user, Phone $phone): bool{
        if (! $user->can('phones.update')) {
            return false;
        }

        // Regla por instancia: autor, mismo ámbito o pertenencia directa
        return $this->ownsPhone($user, $phone)
            || $this->inCurrentCompany($user, $phone)
            || $this->belongsToUser($user, $phone);
    }

    /**
     * ¿Puede eliminar este teléfono?
     */
    public function delete(User $user, Phone $phone): bool{
        if (! $user->can('phones.delete')) {
            return false;
        }

        return $this->ownsPhone($user, $phone)
            || $this->inCurrentCompany($user, $phone)
            || $this->belongsToUser($user, $phone);
    }

    /**
     * (Si usas soft deletes)
     */
    public function restore(User $user, Phone $phone): bool{
        if (! $user->can('phones.restore') && ! $user->can('phones.delete')) {
            return false;
        }

        return $this->ownsPhone($user, $phone)
            || $this->inCurrentCompany($user, $phone);
    }

    /**
     * (Opcional) Eliminación forzada
     */
    public function forceDelete(User $user, Phone $phone): bool{
        if (! $user->can('phones.forceDelete') && ! $user->can('phones.delete')) {
            return false;
        }

        return $this->inCurrentCompany($user, $phone);
    }

    /* =======================
     |   Helpers de contexto
     ========================*/

    /**
     * Comprueba si el teléfono pertenece al usuario (p.ej. phoneable = User)
     */
    protected function belongsToUser(User $user, Phone $phone): bool{
        try {
            if (method_exists($phone, 'phoneable') && $phone->phoneable) {
                $owner = $phone->phoneable;
                // Si el phoneable es el propio usuario
                if ($owner instanceof User && (int) $owner->id === (int) $user->id) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // silencio: política defensiva
        }

        return false;
    }

    /**
     * Comprueba si el usuario es autor/creador del teléfono (si existe la columna created_by)
     */
    protected function ownsPhone(User $user, Phone $phone): bool{
        return isset($phone->created_by) && (int) $phone->created_by === (int) $user->id;
    }

    /**
     * Valida si el teléfono cae dentro de la empresa activa de sesión.
     * Intenta deducir company_id de varias formas:
     *   - $phone->company_id
     *   - $phone->phoneable->company_id
     *   - Si phoneable es Company: company_id = phoneable_id
     */
    protected function inCurrentCompany(User $user, Phone $phone): bool{
        $currentCompanyId = $this->currentCompanyId();
        if (! $currentCompanyId) {
            return false;
        }

        // 1) company_id directo en Phone
        if (isset($phone->company_id) && (int) $phone->company_id === (int) $currentCompanyId) {
            return true;
        }

        // 2) A través del phoneable
        try {
            if (method_exists($phone, 'phoneable') && $phone->phoneable) {
                $owner = $phone->phoneable;

                // 2.a) Si el owner tiene company_id
                if (isset($owner->company_id) && (int) $owner->company_id === (int) $currentCompanyId) {
                    return true;
                }

                // 2.b) Si el owner ES una Company (convención típica)
                //     y su id coincide con la empresa activa
                if ($this->isCompanyModel($owner) && (int) $owner->id === (int) $currentCompanyId) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            // silencio
        }

        return false;
    }

    /**
     * Obtiene el company_id actual desde sesión.
     * Soporta varias claves por compatibilidad.
     */
    protected function currentCompanyId(): ?int{
        return session('currentCompany')
            ?? session('currentCompany.id')
            ?? session('current_company.id')
            ?? null;
    }

    /**
     * Heurística simple para detectar si el owner es un modelo Company,
     * sin acoplar a una clase concreta por si cambias namespaces.
     */
    protected function isCompanyModel($model): bool{
        if (! is_object($model)) {
            return false;
        }

        $class = get_class($model);

        // Ajusta aquí si usas otro nombre/namespace para Company
        return str_ends_with($class, '\\Company') || $class === 'App\\Models\\Company';
    }
}

