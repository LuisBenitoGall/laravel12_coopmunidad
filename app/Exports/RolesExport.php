<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RolesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize{
    public function __construct(public Builder $query) {}

    public function query(){
        return $this->query;
    }

    public function headings(): array{
        return ['ID', __('role'), __('descripcion'), __('alta')];
    }

    public function map($row): array{
        return [
            $row->id,
            $row->name,
            $row->description,
            optional($row->created_at)->format('Y-m-d H:i'),
        ];
    }
}
