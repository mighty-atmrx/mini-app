<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Expert;
use app\Telegram\State\StateManager;
use Carbon\Carbon;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class AskClientIfBookedCommand extends Command
{
    protected $signature = 'app:ask-client-if-booked-command';
    protected $description = 'Command description';

    public function handle()
    {
        $now = Carbon::now('Asia/Almaty');
        \Log::info('AskClientIfBookedCommand started', ['now' => $now->toDateTimeString()]);

        $bookings = Booking::where('status', 'payment')
            ->where('date', null)
            ->where('time', null)
            ->with('user', 'service')
            ->whereBetween('created_at', [
                $now->copy()->subMinutes(1441),
                $now->copy()->subMinutes(1439),
            ])->get();

        \Log::info($bookings->count());

        foreach ($bookings as $booking) {
            \Log::info(11111);
            $telegramUserId = $booking->user->telegram_user_id;
            $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$telegramUserId])->first();
            \Log::info(22222);
            if (!$chat) {
                \Log::warning('Chat not found for user', ['user_id' => $booking->user->id, 'telegram_user_id' => $telegramUserId]);
                continue;
            }
            \Log::info(333333);

            $expert = Expert::find($booking->expert_id);
            \Log::info(444444);
            $keyboard = ReplyKeyboard::make()->oneTime()->resize()
                ->row([ReplyButton::make('✅ Да')])
                ->row([ReplyButton::make('❌ Нет')]);

            \Log::info('ask client if booked command');

            $chat->message("Вам удалось записаться к эксперту {$expert->first_name} {$expert->last_name}?")
                ->replyKeyboard($keyboard)->send();

            app(StateManager::class)->setStep($chat, 'awaiting_booking_confirmation');
            app(StateManager::class)->setUserData($chat, ['booking_id' => $booking->id]);
        }
    }
}
