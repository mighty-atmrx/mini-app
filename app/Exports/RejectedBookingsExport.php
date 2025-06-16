<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RejectedBookingsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        \Log::info('Rejected Bookings Export');
        return  Booking::with('user', 'expert')
            ->where('status', 'rejected')
            ->get()
            ->map(function ($booking) {
                return [
                    'user_first_name' => $booking->user->first_name,
                    'user_last_name' => $booking->user->last_name,
                    'user_phone' => $booking->user->phone,
                    'expert_first_name' => $booking->expert->user->first_name,
                    'expert_last_name' => $booking->expert->user->last_name,
                    'expert_phone' => $booking->expert->user->phone,
                    'status' => 'Отменено',
                    'reason' => $booking->reject_reason
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Имя Клиента',
            'Фамилия Клиента',
            'Номер Клиента',
            'Имя Эксперта',
            'Фамилия Эксперта',
            'Номер Эксперта',
            'Статус записи',
            'Причина'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
