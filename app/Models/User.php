<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

//Concerns:
use App\Models\Concerns\HasCategories;

class User extends Authenticatable{
    /**
     * 1. Empresas del usuario.
     * 1.1. Relación eloquent de empresas del usuario.
     * 2. Comunidades del usuario.
     * 3. Método de Super Admin.
     * 4. Avatar del usuario.
     * 5. Teléfonos del usuario.
     */

    use HasCategories, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'isAdmin',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 1. Empresas del usuario.
     */
    public function companies(){
        $data = Company::select('companies.id', 'companies.name', 'companies.tradename', 'companies.logo', 'companies.nif', 'companies.status')
            ->join('user_companies', 'companies.id', '=', 'user_companies.company_id')
            ->where('user_companies.user_id', $this->id)
            ->where('companies.status', 1)
            ->get();

        return $data;
    }

    /**
     * 1.1. Relación eloquent de empresas del usuario.
     */
    public function companiesRelation(){
        return $this->belongsToMany(Company::class, 'user_companies')
                ->where('companies.status', 1);
    }

    /**
     * 2. Comunidades del usuario.
     */
    public function communities(){
        $data = Community::select('communities.id', 'communities.name', 'communities.slug', 'communities.nif', 'communities.status')
            ->join('user_communities', 'communities.id', '=', 'user_communities.community_id')
            ->where('user_communities.user_id', $this->id)
            ->where('communities.status', 1)
            ->get();

        return $data;
    }

    /**
     * 3. Método de Super Admin.
     */
    public function isSuperAdmin(): bool{
        return $this->hasRole('Super Admin');
    }

    /**
     * 4. Avatar del usuario.
     */
    public function avatar(){
        return $this->hasOne(UserImage::class)->where('featured', 1)->where('public', 1);
    }

    /**
     * 5. Teléfonos del usuario.
     */
    public function phones(){
        return $this->morphMany(\App\Models\Phone::class, 'phoneable')
            ->orderByDesc('is_primary');
    }
}
