<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

//Models:
use App\Models\Functionality;
use App\Models\Module;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        //Creación role Super Admin y manager:
        Role::create(['name' => 'Super Admin']);

        //Módulos:
        $modules = Module::all();

        foreach($modules as $module){
            Permission::create(['name' => 'module_'.$module->slug]);
        }

        //Funcionalidades:
        $functionalities = Functionality::all();

        //Métodos para los permisos:
        $methods = config('constants.METHODS_');

        //Excepciones (no se generan permisos)

        //Creamos los permisos de cada funcionalidad:
        foreach($functionalities as $func){
            foreach($methods as $method){
                Permission::create(['name' => strtolower($func->slug).'.'.$method[0]]);
            }
        }
    }
}
