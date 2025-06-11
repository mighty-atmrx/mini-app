<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Expert;
use App\Models\ExpertReview;
use App\Models\User;
use App\Models\UserReviews;
use App\Telegram\KeyboardFactory;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class ExpertReviewNotifyCommand extends Command
{
    protected $signature = 'app:expert-review-notify-command';
    protected $description = 'Command description';

    public function handle()
    {
        $now = Carbon::now('Asia/Almaty');

        $bookings = Booking::where('status', 'completed')
            ->get()
            ->filter(function ($booking) use ($now) {
                $datetimeString = trim("{$booking->date} {$booking->time}");
                $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $datetimeString, 'Asia/Almaty')
                    ?: Carbon::parse($datetimeString, 'Asia/Almaty');
                $reviewTime = $bookingDateTime->copy()->addHours(24);
                $isPast = $reviewTime->lessThanOrEqualTo($now);
                $minutesDiff = $now->diffInMinutes($reviewTime, false);
                $isReviewTime = $minutesDiff >= -1 && $minutesDiff <= 1;

                $reviewCount = ExpertReview::where('expert_id', $booking->expert_id)
                    ->where('user_id', $booking->user_id)
                    ->count();

                $bookingCount = Booking::where('expert_id', $booking->expert_id)
                    ->where('user_id', $booking->user_id)
                    ->where('status', 'completed')
                    ->count();

                $shouldNotify = $reviewCount < $bookingCount;

                \Log::info('Checking review conditions for expert', [
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
            $expert = Expert::find($booking->expert_id);
            $user = User::find($expert->user_id);
            $telegramUserId = $user->telegram_user_id;
            $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$telegramUserId])->first();

            if (!$chat) {
                \Log::warning('Chat not found for expert', ['expert_id' => $expert->id, 'telegram_user_id' => $telegramUserId]);
                continue;
            }

            $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i:s', "{$booking->date} {$booking->time}", 'Asia/Almaty')
                ?: Carbon::parse("{$booking->date} {$booking->time}", 'Asia/Almaty');

            $user = User::find($booking->expert_id);

            $response = $chat->message("*Напоминание:* Ваша консультация с экспертом была {$bookingDateTime->format('Y-m-d H:i')}. Пожалуйста оставьте отзыв о работе с пользователем {$user->first_name}!")
                ->keyboard(KeyboardFactory::makeAppKeyboard(config('telegram.mini_app_url')))
                ->send();
            \Log::info('Review reminder sent', [
                'chat_id' => $chat->chat_id,
                'telegram_response' => $response->telegraphOk() ? 'success' : 'failed',
            ]);
        }
    }
}
