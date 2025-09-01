<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use App\Models\Module;

class ModulesTableSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * Levels: 1 = administrador; 2 = básicos; 3 = optativos.
     */
    public function run(): void
    {
        $data = [
            [
                'name'         => 'dashboard',
                'slug'         => 'dashboard',
                'label'        => 'dashboard',
                'color'        => '#e37f7f',
                'icon'         => 'bars',
                'scope'        => 'both',      // company | community | both
                'level'        => '2',
                'translations' => serialize(['es' => 'Dashboard']),
                'status'       => 1,           
                'active'       => true
            ],
            [
                'name'         => 'settings',
                'slug'         => 'settings',
                'label'        => 'configuracion',
                'color'        => '#A13B3B',
                'icon'         => 'cog',
                'scope'        => 'both',
                'level'        => '1',
                'translations' => serialize(['es' => 'Configuración']),
                'status'       => 1,           
                'active'       => true
            ],
            [
                'name'         => 'account',
                'slug'         => 'account',
                'label'        => 'cuenta',
                'color'        => '#f5961f',
                'icon'         => 'user-circle',
                'scope'        => 'both',
                'level'        => '2',
                'translations' => serialize(['es' => 'Cuenta']),
                'status'       => 1,
                'active'       => true
            ],
            [
                'name'         => 'users',
                'slug'         => 'users',
                'label'        => 'usuarios',
                'color'        => '#2AAAB2',
                'icon'         => 'users',
                'scope'        => 'both',
                'level'        => '2',
                'translations' => serialize(['es' => 'Usuarios']),
                'status'       => 1,
                'active'       => true
            ],
            [
                'name'         => 'communities',
                'slug'         => 'communities',
                'label'        => 'comunidades',
                'color'        => '#1fd3e1',
                'icon'         => 'building',
                'scope'        => 'community',
                'level'        => '2',
                'translations' => serialize(['es' => 'Comunidades']),
                'status'       => 1,
                'active'       => true
            ],
            [
                'name'         => 'companies',
                'slug'         => 'companies',
                'label'        => 'empresas',
                'color'        => '#1fd3e1',
                'icon'         => 'landmark',
                'scope'        => 'company',
                'level'        => '2',
                'translations' => serialize(['es' => 'Empresas']),
                'status'       => 1,
                'active'       => true
            ],
            [
                'name'         => 'rrhh',
                'slug'         => 'rrhh',
                'label'        => 'rrhh',
                'color'        => '#8DDE95',
                'icon'         => 'user-shield',
                'scope'        => 'both',
                'level'        => '3',
                'translations' => serialize(['es' => 'RRHH']),
                'status'       => 0,
                'active'       => true
            ],
            [
                'name'         => 'accounting',
                'slug'         => 'accounting',
                'label'        => 'contabilidad',
                'color'        => '#98571D',
                'icon'         => 'money-bill-alt',
                'scope'        => 'both',
                'level'        => '3',
                'translations' => serialize(['es' => 'Contabilidad']),
                'status'       => 0,
                'active'       => true
            ],
            [
                'name'         => 'quality',
                'slug'         => 'quality',
                'label'        => 'calidad',
                'color'        => '#60A433',
                'icon'         => 'check',
                'scope'        => 'both',
                'level'        => '3',
                'translations' => serialize(['es' => 'Calidad']),
                'status'       => 0,
                'active'       => true
            ],
            [
                'name'         => 'procedures',
                'slug'         => 'procedures',
                'label'        => 'procedimientos',
                'color'        => '#e0e610',
                'icon'         => 'sitemap',
                'scope'        => 'both',
                'level'        => '3',
                'translations' => serialize(['es' => 'Procedimientos']),
                'status'       => 0,
                'active'       => true
            ]
        ];

        // Evita disparar observers/listeners durante el seed
        Module::withoutEvents(function () use ($data) {
            foreach ($data as $value) {
                if (isset($value['active']) && $value['active'] !== true) {
                    continue;
                }

                // Idempotente: si existe el slug, actualiza; si no, crea.
                Module::updateOrCreate(
                    ['slug' => $value['slug']],
                    [
                        'name'         => $value['name'],
                        'label'        => $value['label'],
                        'color'        => $value['color'],
                        'icon'         => $value['icon'],
                        'scope'        => $value['scope'],        // 'company' | 'community' | 'both'
                        'level'        => (int) $value['level'],
                        'translations' => $value['translations'], // tal cual (serialize)
                        'status'       => (int) $value['status'], // tu bandera de activación
                    ]
                );
            }
        });

        // Invalidación universal del menú (sin cache tags)
        Cache::increment('secondary_menu_schema_ver');
    }
}
