<?php

namespace app\Telegram\State;


use DefStudio\Telegraph\Models\TelegraphChat;

class StateManager
{
    private $chat;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
    }

    public function getStep(TelegraphChat $chat): string
    {
        return $chat->storage()->get('step', 'name');
    }

    public function setStep(TelegraphChat $chat, string $step): void
    {
        $chat->storage()->set('step', $step);
    }

    public function getUserData(TelegraphChat $chat): array
    {
        return $chat->storage()->get('user_data', []);
    }

    public function setUserData(TelegraphChat $chat, array $data): void
    {
        $chat->storage()->set('user_data', $data);
    }

    public function clear(TelegraphChat $chat): void
    {
        $chat->storage()->forget('user_data');
    }
}
