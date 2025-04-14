<?php

namespace App\Telegram\State;

use App\Telegram\InputValidator;
use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class BirthdateState implements RegistrationState
{
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $birthdate = $input->toString();
        \Log::info('Birthdate received', ['telegram_id' => $chat->chat_id, 'birthdate' => $birthdate]);

        if (!InputValidator::validateBirthdate($birthdate)) {
            $chat->message('Неверный формат даты. Попробуй снова (например, 01.01.2000).')
                ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
                ->send();
            \Log::info('Invalid birthdate rejected', ['telegram_id' => $chat->chat_id, 'birthdate' => $birthdate]);
            return $this;
        }

        $userData = $this->stateManager->getUserData($chat);
        $userData['birthdate'] = InputValidator::formatBirthdate($birthdate);
        $this->stateManager->setUserData($chat, $userData);
        $this->stateManager->setStep($chat, 'completed');

        \Log::info('Birthdate saved, registration completed', [
            'telegram_id' => $chat->chat_id,
            'user_data' => $userData,
        ]);

        return null;
    }

    public function prompt(TelegraphChat $chat): void
    {
        $chat->message('Спасибо! Укажи дату рождения (например, 01.01.2000).')
            ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
            ->send();
        \Log::info('Birthdate request sent', ['telegram_id' => $chat->chat_id]);
    }
}
