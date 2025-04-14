<?php

namespace App\Telegram\State;

use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

interface RegistrationState
{
    public function handle(TelegraphChat $chat, Stringable $input, ?Message $message): ?RegistrationState;
    public function prompt(TelegraphChat $chat): void;
}
