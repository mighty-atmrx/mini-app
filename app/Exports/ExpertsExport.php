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
        return Expert::with('bookings')
            ->get()
            ->map(function ($expert) {
                return [
                    'id' => $expert->id,
                    'user_id' => $expert->user_id,
                    'first_name' => $expert->first_name,
                    'last_name' => $expert->last_name,
                    'profession' => $expert->profession,
                    'biography' => $expert->biography,
                    'photo' => $expert->photo,
                    'experience' => $expert->experience,
                    'education' => $expert->education,
                    'rating' => $expert->rating,
                    'bookings_qty' => $expert->bookings->count(),
                    'created_at' => $expert->created_at,
                    'updated_at' => $expert->updated_at,
               ];
            });
    }

    public function headings(): array
    {
        return [
            'id', 'user_id', 'Имя', 'Фамилия',
            'Профессия', 'Биография', 'Фото',
            'Опыт', 'Образования', 'Рейтинг',
            'Кол-во записей', 'created_at', 'updated_at'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
