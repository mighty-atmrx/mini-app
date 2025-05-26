<?php

namespace App\Exports;

use App\Models\Expert;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpertsExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Expert::all();
    }

    public function headings(): array
    {
        return [
            'id', 'user_id', 'Имя', 'Фамилия',
            'Профессия', 'Биография', 'Фото',
            'Опыт', 'Образования', 'Рейтинг', 'created_at', 'updated_at',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
