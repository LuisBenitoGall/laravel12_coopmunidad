<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

//Traits:
use App\Traits\LocaleTrait;
use App\Traits\ModulesTrait;

class ModulesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize{
    use Exportable;
    use LocaleTrait;
    use ModulesTrait;

    /** @var \Illuminate\Database\Eloquent\Builder */
    protected Builder $query;

    /** @var array<int,string> */
    protected array $levels;

    public function __construct(Builder $query, array $levels = [], array $scopes = []){
        $this->query  = $query;
        $this->levels = $levels;
        $this->scopes = $scopes;
    }

    public function query(){
        return $this->query->select(['id','label','color','icon','scope','level','explanation','created_at']);
    }

    public function headings(): array{
        return ['ID', __('modulo'), __('color'), __('icono'), __('ambito'), __('nivel'), __('descripcion'), __('alta')];
    }

    public function map($row): array{
        //Niveles:
        $levels = $this->levels();

        return [
            $row->id,
            __($row->label),
            $row->color,
            $row->icon,
            $this->scopes[$row->scope] ?? (string)$row->scope,
            $this->levels[$row->level] ?? (string)$row->level,
            $row->explanation,
            optional($row->created_at)->format('Y-m-d H:i'),
        ];
    }
}
