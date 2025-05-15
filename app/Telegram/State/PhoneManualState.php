<?php

namespace App\Telegram\State;

use App\Telegram\InputValidator;
use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class PhoneManualState implements RegistrationState
{
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $phone = $input->toString();
        \Log::info('Phone received manually', ['telegram_id' => $chat->chat_id, 'phone' => $phone]);

        $userData = $this->stateManager->getUserData($chat);
        $userData['last_attempted_phone'] = $phone;
        $this->stateManager->setUserData($chat, $userData);

        if (!InputValidator::validatePhone($phone)) {
            $chat->message('Неверный формат телефона. Попробуй ещё раз (например, +77007073355).')
                ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
                ->send();
            \Log::info('Invalid phone rejected', ['telegram_id' => $chat->chat_id, 'phone' => $phone]);
            return null;
        }

        $userData = $this->stateManager->getUserData($chat);
        $userData['phone'] = $phone;
        $this->stateManager->setUserData($chat, $userData);
        $this->stateManager->setStep($chat, 'birthdate');

        \Log::info('Phone saved, moving to birthdate', [
            'telegram_id' => $chat->chat_id,
            'user_data' => $userData,
        ]);

        return new BirthdateState($this->stateManager);
    }

    public function prompt(TelegraphChat $chat): void
    {
        $chat->message('Пожалуйста, введи свой номер телефона (например, +77007073355).')
            ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
            ->send();
        \Log::info('Manual phone request sent', ['telegram_id' => $chat->chat_id]);
    }
}
