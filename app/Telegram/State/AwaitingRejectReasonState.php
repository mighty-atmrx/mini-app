<?php

namespace App\Telegram\State;

use App\Models\Booking;
use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class AwaitingRejectReasonState
{
    private Statemanager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $text = $input->toString();
        $userData = $this->stateManager->getUserData($chat);
        $bookingId = $userData['booking_id'] ?? null;

        $userData['last_input_time'] = $text;
        $this->stateManager->setUserData($chat, $userData);

        \Log::info('Awaiting Reject Reason state:', [
            'input' => $text,
            'user_data' => $userData,
            'booking_id' => $bookingId,
        ]);

        if ($bookingId && $booking = Booking::find($bookingId)) {
            \Log::info('REASON: ' . $text);
            $booking->update([
                'status' => 'rejected',
                'reject_reason' => $text
            ]);
            \Log::info('Booking was updated successfully', ['booking' => $booking]);

            $chat->message('Спасибо! Мы сохранили данные об отмене.')->send();
            $this->stateManager->clear($chat);
            return null;
        } else {
            \Log::warning('Booking not found or booking_id missing', [
                'booking_id' => $bookingId,
                'user_data' => $userData,
            ]);
            $chat->message('Ошибка: бронирование не найдено.')->send();
            return $this;
        }
    }

    public function prompt(TelegraphChat $chat): void
    {
        $chat->message('Пожалуйста, укажите причину отмены.')
            ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
            ->send();
    }
}
