<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class BookingNotifyCommand extends Command
{
    protected $signature = 'app:booking-notify-command';
    protected $description = 'Send booking reminders';

    public function handle()
    {
        $now = Carbon::now('Asia/Almaty');

        $bookings = Booking::where('status', 'paid')
            ->with('user')
            ->get()
            ->filter(function ($booking) use ($now) {
                $datetimeString = trim("{$booking->date} {$booking->time}");
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString, 'Asia/Almaty')
                    ?: Carbon::parse($datetimeString, 'Asia/Almaty');
                $isFuture = $bookingDateTime->greaterThan($now);
                $minutesDiff = $isFuture ? $now->diffInMinutes($bookingDateTime, false) : $bookingDateTime->diffInMinutes($now, false);
                $booking->isDayBefore = $minutesDiff >= 1439 && $minutesDiff <= 1441;
                $booking->isHourBefore = $minutesDiff >= 59 && $minutesDiff <= 61;
                \Log::info('Checking booking', [
                    'booking_id' => $booking->id,
                    'datetime' => $bookingDateTime->toDateTimeString(),
                    'now' => $now->toDateTimeString(),
                    'is_future' => $isFuture,
                    'is_day_before' => $booking->isDayBefore,
                    'is_hour_before' => $booking->isHourBefore,
                    'minutes_diff' => $minutesDiff,
                ]);
                return $isFuture && ($booking->isDayBefore || $booking->isHourBefore);
            });

        foreach ($bookings as $booking) {
            $telegramUserId = $booking->user->telegram_user_id;
            $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$telegramUserId])->first();

            if (!$chat) {
                \Log::warning('Chat not found for user', ['user_id' => $booking->user->id, 'telegram_user_id' => $telegramUserId]);
                continue;
            }

            $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->date} {$booking->time}", 'Asia/Almaty')
                ?: Carbon::parse("{$booking->date} {$booking->time}", 'Asia/Almaty');
            if ($booking->isHourBefore == true) {
                \Log::info('isHourBefore');
                $timeLeft = 'через час';
            }

            if ($booking->isDayBefore == true) {
                \Log::info('isDayBefore');
                $timeLeft = 'завтра';
            }

            $response = $chat->message("*Напоминание:* {$timeLeft} у Вас состоится консультация. *Время консультации:* {$bookingDateTime->format('H:i')}")->send();
            \Log::info('Reminder sent', [
                'chat_id' => $chat->chat_id,
                'time_left' => $timeLeft,
                'telegram_response' => $response->telegraphOk() ? 'success' : 'failed',
            ]);
        }
    }
}
