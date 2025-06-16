<?php

namespace App\Telegram\State;

use App\Models\Booking;
use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class AwaitingBookingDateTimeState implements RegistrationState
{
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $text = trim(preg_replace('/\s+/', ' ', $input->toString()));
        $userData = $this->stateManager->getUserData($chat);
        $bookingId = $userData['booking_id'] ?? null;

        $userData['last_input_time'] = $text;
        $this->stateManager->setUserData($chat, $userData);

        \Log::info('Awaiting Booking Date Time state:', [
            'input' => $text,
            'user_data' => $userData,
            'booking_id' => $bookingId,
        ]);

        $dateTime = \DateTime::createFromFormat('d.m.Y H:i', $text);
        \Log::info('DateTime: ', ['input' => $dateTime]);

        if (!$dateTime) {
            \Log::warning('Invalid datetime input', ['input' => $text]);

            $chat->message('Неверный формат даты и времени. Попробуйте ещё раз (например, 01.01.2025 10:00).')
                ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
                ->send();

            return $this;
        }

        if ($bookingId && $booking = Booking::find($bookingId)) {
            $booking->update([
                'date' => $dateTime->format('Y-m-d'),
                'time' => $dateTime->format('H:i:s'),
                'status' => 'paid',
            ]);
            \Log::info('Booking was updated successfully', ['booking' => $booking]);

            $chat->message('Спасибо! Мы сохранили время записи.')->send();
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
        $chat->message('Пожалуйста, укажите дату и время записи. Например: 01.01.2025 10:00.')
            ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
            ->send();
    }
}

