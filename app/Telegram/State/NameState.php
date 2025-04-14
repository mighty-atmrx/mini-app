<?php

namespace App\Telegram\State;

use App\Telegram\InputValidator;
use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class NameState implements RegistrationState
{
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $name = $input->toString();
        \Log::info('Name received', ['telegram_id' => $chat->chat_id, 'name' => $name]);

        if (!InputValidator::validateName($name)) {
            $chat->message('Имя слишком короткое. Попробуй ещё раз.')
                ->send();
            \Log::info('Short name rejected', ['telegram_id' => $chat->chat_id, 'name' => $name]);
            return $this;
        }

        $userData = $this->stateManager->getUserData($chat);
        $userData['first_name'] = $name;
        $this->stateManager->setUserData($chat, $userData);
        $this->stateManager->setStep($chat, 'phone');

        \Log::info('Name saved, moving to phone', [
            'telegram_id' => $chat->chat_id,
            'user_data' => $userData,
        ]);

        return new PhoneState($this->stateManager);
    }

    public function prompt(TelegraphChat $chat): void
    {
        $chat->message('Пожалуйста, напиши своё имя.')
            ->replyKeyboard(KeyboardFactory::makeEmptyKeyboard())
            ->send();
        \Log::info('Name request sent', ['telegram_id' => $chat->chat_id]);
    }
}
