<?php

namespace App\Telegram\State;

use App\Telegram\KeyboardFactory;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class PhoneState implements RegistrationState
{
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState
    {
        $text = $input->toString();
        \Log::info('Phone input received', ['telegram_id' => $chat->chat_id, 'input' => $text]);

        if ($text === 'Ввести вручную') {
            $this->stateManager->setStep($chat, 'phone_manual');
            return new PhoneManualState($this->stateManager);
        }

        if ($message && $message->contact()) {
            $phone = $message->contact()->phoneNumber();
            $phone = strpos($phone, '+') === 0 ? $phone : '+' . $phone;
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

        \Log::warning('Invalid phone input', ['telegram_id' => $chat->chat_id, 'input' => $text]);
        return $this;
    }

    public function prompt(TelegraphChat $chat): void
    {
        $chat->message('Отлично! Поделись номером телефона.')
            ->replyKeyboard(KeyboardFactory::makePhoneKeyboard())
            ->send();
        \Log::info('Phone request sent', ['telegram_id' => $chat->chat_id]);
    }
}
