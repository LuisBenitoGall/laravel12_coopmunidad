<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize{
    public function __construct(public Builder $query) {}

    public function query(){
        return $this->query;
    }

    public function headings(): array{
        return ['ID', __('nombre'), __('apellidos'), __('email'), __('telefonos'), __('alta')];
    }

    public function map($row): array{
        $phones = collect($row->phones ?? [])->pluck('phone_number')->implode(', ');
        return [
            $row->id,
            $row->name,
            $row->surname,
            $row->email,
            $phones,
            optional($row->created_at)->format('Y-m-d H:i'),
        ];
    }
}

