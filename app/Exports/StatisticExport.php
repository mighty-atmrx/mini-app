<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        \Log::info('Statistic Export');
        return User::query()
            ->with(['transactions' => fn($q) => $q->orderBy('created_at')])
            ->orderBy('id')
            ->get()
            ->map(function ($user) {
                $firstPurchase = $user->transactions->first();
                $lastPurchase = $user->transactions->last();
                $totalAmount = $user->transactions->sum('amount');
                $totalQty = $user->transactions->count();

                return [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'birthdate' => $user->birthdate,
                    'phone' => $user->phone,
                    'first_purchase_amount' => $firstPurchase?->amount ?? 0,
                    'first_purchase_date' => $firstPurchase?->created_at?->format('Y-m-d') ?? '',
                    'last_purchase_amount' => $lastPurchase?->amount ?? 0,
                    'last_purchase_date' => $lastPurchase?->created_at?->format('Y-m-d') ?? '',
                    'total_purchase_amount' => $totalAmount,
                    'total_purchase_qty' => $totalQty,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Имя',
            'Фамилия',
            'Дата рождения',
            'Телефон',
            'Сумма первой покупки',
            'Дата первой покупки',
            'Сумма послед. покупки',
            'Дата послед. покупки',
            'Общая сумма',
            'Общее кол-во',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
