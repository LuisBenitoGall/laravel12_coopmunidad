<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

//Models:
use App\Models\Functionality;
use App\Models\Module;

class FunctionalitiesTableSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $data = [
            ['name' => 'dashboard',
            'slug' => 'dashboard',
            'label' => 'dashboard',
            'module' => 'dashboard'
            ],
            ['name' => 'chat',
            'slug' => 'chat',
            'label' => 'chat',
            'module' => 'dashboard'
            ],

            ['name' => 'accounts',
            'slug' => 'accounts',
            'label' => 'cuentas',
            'module' => 'settings'
            ],
            ['name' => 'modules',
            'slug' => 'modules',
            'label' => 'modulos',
            'module' => 'settings'
            ],
            ['name' => 'roles',
            'slug' => 'roles',
            'label' => 'roles',
            'module' => 'settings'
            ],
            ['name' => 'permissions',
            'slug' => 'permissions',
            'label' => 'permisos',
            'module' => 'settings'
            ],
            ['name' => 'currencies',
            'slug' => 'currencies',
            'label' => 'monedas',
            'module' => 'settings'
            ],
            ['name' => 'banks',
            'slug' => 'banks',
            'label' => 'bancos',
            'module' => 'settings'
            ],
            ['name' => 'countries',
            'slug' => 'countries',
            'label' => 'paises',
            'module' => 'settings'
            ],
            ['name' => 'contents',
            'slug' => 'contents',
            'label' => 'contenidos',
            'module' => 'settings'
            ],
            ['name' => 'iva types',
            'slug' => 'iva-types',
            'label' => 'iva_tipos',
            'module' => 'settings'
            ],
            ['name' => 'accounting account types',
            'slug' => 'accounting-account-types',
            'label' => 'grupos_contables',
            'module' => 'settings'
            ],

            ['name' => 'users',
            'slug' => 'users',
            'label' => 'usuarios',
            'module' => 'users'
            ],
            ['name' => 'ip access',
            'slug' => 'ip-access',
            'label' => 'acceso_ip',
            'module' => 'users'
            ],

            ['name' => 'communities',
            'slug' => 'communities',
            'label' => 'comunidades',
            'module' => 'communities'
            ],
            ['name' => 'community settings',
            'slug' => 'community-settings',
            'label' => 'comunidad_configuracion',
            'module' => 'communities'
            ],
            ['name' => 'community accounts',
            'slug' => 'community-accounts',
            'label' => 'comunidad_cuenta',
            'module' => 'communities'
            ],

            ['name' => 'companies',
            'slug' => 'companies',
            'label' => 'empresas',
            'module' => 'companies'
            ],
            ['name' => 'company settings',
            'slug' => 'company-settings',
            'label' => 'empresas_configuracion_',
            'module' => 'companies'
            ],
            ['name' => 'company accounts',
            'slug' => 'company-accounts',
            'label' => 'empresa_cuenta',
            'module' => 'companies'
            ],
            ['name' => 'sectors',
            'slug' => 'sectors',
            'label' => 'directorio_sectores',
            'module' => 'companies'
            ],
            ['name' => 'business areas',
            'slug' => 'business-areas',
            'label' => 'areas_negocio',
            'module' => 'companies'
            ],
            ['name' => 'cost centers',
            'slug' => 'cost-centers',
            'label' => 'centros_coste',
            'module' => 'companies'
            ],
            ['name' => 'workplaces',
            'slug' => 'workplaces',
            'label' => 'centros_trabajo',
            'module' => 'companies'
            ],
            ['name' => 'files',
            'slug' => 'files',
            'label' => 'archivos',
            'module' => 'companies'
            ],
            ['name' => 'customers',
            'slug' => 'customers',
            'label' => 'clientes',
            'module' => 'companies'
            ],
            ['name' => 'providers',
            'slug' => 'providers',
            'label' => 'proveedores',
            'module' => 'companies'
            ],
            ['name' => 'company-sectors',
            'slug' => 'company-sectors',
            'label' => 'sectores',
            'module' => 'companies'
            ],

            ['name' => 'invoices',
            'slug' => 'invoices',
            'label' => 'facturas',
            'module' => 'accounting'
            ],
            ['name' => 'invoice-patterns',
            'slug' => 'invoice-patterns',
            'label' => 'facturas_patrones_',
            'module' => 'accounting'
            ],
            ['name' => 'invoice settings',
            'slug' => 'invoice-settings',
            'label' => 'facturas_configuracion_',
            'module' => 'accounting'
            ],
            ['name' => 'bank accounts',
            'slug' => 'bank-accounts',
            'label' => 'bancos_cuentas',
            'module' => 'accounting'            
            ],
            ['name' => 'accounting accounts',
            'slug' => 'accounting-accounts',
            'label' => 'cuentas_contables',
            'module' => 'accounting'            
            ],
            ['name' => 'accounting concepts',
            'slug' => 'accounting-concepts',
            'label' => 'conceptos_contables',
            'module' => 'accounting'            
            ],
            ['name' => 'accounting options',
            'slug' => 'accounting-options',
            'label' => 'opciones_contables',
            'module' => 'accounting'            
            ],
            ['name' => 'payment methods',
            'slug' => 'payment-methods',
            'label' => 'metodos_pago',
            'module' => 'accounting'            
            ],
            ['name' => 'seats',
            'slug' => 'seats',
            'label' => 'asientos_contables',
            'module' => 'accounting'            
            ],
            ['name' => 'effects',
            'slug' => 'effects',
            'label' => 'efectos',
            'module' => 'accounting'
            ],
            ['name' => 'remittances',
            'slug' => 'remittances',
            'label' => 'remesas',
            'module' => 'accounting'
            ],
            ['name' => 'remittance patterns',
            'slug' => 'remittance-patterns',
            'label' => 'remesa_patrones',
            'module' => 'accounting'
            ],

            ['name' => 'incidences',
            'slug' => 'incidences',
            'label' => 'incidencias',
            'module' => 'quality'
            ],
            ['name' => 'incidence patterns',
            'slug' => 'incidence-patterns',
            'label' => 'incidencias_patrones',
            'module' => 'quality'
            ],

            ['name' => 'procedures',
            'slug' => 'procedures',
            'label' => 'procedimientos',
            'module' => 'procedures'
            ],
            ['name' => 'procedure patterns',
            'slug' => 'procedure-patterns',
            'label' => 'procedimientos_patrones',
            'module' => 'procedures'
            ]
        ];

        //Módulos:
        $modules = Module::all();

        foreach($data as $value){
            $module_id = false;

            //Pasamos el module_id al que pertenece la funcionalidad:
            foreach($modules as $module){
                if($module->slug == $value['module']){
                    $module_id = $module->id;
                    break;
                }
            }

            if($module_id){
                $func = new Functionality();
                $func->name = $value['name'];
                $func->slug = $value['slug'];
                $func->label = $value['label'];
                $func->module_id = $module_id;
                $func->save();
            }
        }    
    }
}
