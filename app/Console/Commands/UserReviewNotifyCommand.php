<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Expert;
use App\Models\UserReviews;
use App\Telegram\KeyboardFactory;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class UserReviewNotifyCommand extends Command
{
    protected $signature = 'app:user-review-notify-command';
    protected $description = 'Command description';

    public function handle()
    {
        $now = Carbon::now('Asia/Almaty');

        $bookings = Booking::where('status', 'completed')
            ->with('user', 'service')
            ->get()
            ->filter(function ($booking) use ($now) {
                $datetimeString = trim("{$booking->date} {$booking->time}");
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString, 'Asia/Almaty')
                    ?: Carbon::parse($datetimeString, 'Asia/Almaty');
                $reviewTime = $bookingDateTime->copy()->addHours(24);
                $isPast = $reviewTime->lessThanOrEqualTo($now);
                $minutesDiff = $now->diffInMinutes($reviewTime, false);
                $isReviewTime = $minutesDiff >= -1 && $minutesDiff <= 1;

                $reviewCount = UserReviews::where('user_id', $booking->user_id)
                    ->where('expert_id', $booking->expert_id)
                    ->count();

                $bookingCount = Booking::where('user_id', $booking->user_id)
                    ->where('expert_id', $booking->expert_id)
                    ->where('status', 'completed')
                    ->count();

                $shouldNotify = $reviewCount < $bookingCount;

                \Log::info('Checking review conditions for user', [
                    'booking_id' => $booking->id,
                    'booking_datetime' => $bookingDateTime->toDateTimeString(),
                    'review_time' => $reviewTime->toDateTimeString(),
                    'now' => $now->toDateTimeString(),
                    'is_past' => $isPast,
                    'is_review_time' => $isReviewTime,
                    'review_count' => $reviewCount,
                    'booking_count' => $bookingCount,
                    'should_notify' => $shouldNotify,
                    'minutes_diff' => $minutesDiff,
                ]);

                return $isPast && $isReviewTime && $shouldNotify;
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

            $expert = Expert::find($booking->expert_id);
            $expertCategory = Category::find($booking->service->category_id);

            $response = $chat->message("*Напоминание:* Ваша консультация с экспертом была {$bookingDateTime->format('Y-m-d H:i')}. Пожалуйста оставьте отзыв о работе с экспертом " . $expert->first_name . " " . $expert->last_name . "({$expertCategory->title})!")
                ->keyboard(KeyboardFactory::makeAppKeyboard(config('telegram.mini_app_url')))
                ->send();
            \Log::info('Review reminder sent', [
                'chat_id' => $chat->chat_id,
                'telegram_response' => $response->telegraphOk() ? 'success' : 'failed',
            ]);
        }
    }
}
