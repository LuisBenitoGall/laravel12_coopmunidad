<?php

namespace App\Traits;

trait ModulesTrait{
    /**
     * 1. Niveles de módulo.
     * 2. Ámbitos del módulo.
     */
    
    /**
     * 1. Niveles de módulo.
     */
    public static function levels($level = false){
        $data = [
            1 => __('modulos_administrador'),
            2 => __('modulos_basicos'),
            3 => __('modulos_optativos')
        ];

        if($level){
            return $data[$level];
        }else{
            return $data;
        }
    }

    /**
     * 2. Ámbitos del módulo.
     */
    public static function scopes($scope = false){
        $data = [
            'both' => __('todos'),
            'community' => __('comunidad'),
            'company' => __('empresa')
        ];

        if($scope){
            return $data[$scope];
        }else{
            return $data;
        }
    }
}
