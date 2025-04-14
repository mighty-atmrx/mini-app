<?php

namespace App\Telegram;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class KeyboardFactory
{
    public static function makePhoneKeyboard(): ReplyKeyboard
    {
        return ReplyKeyboard::make()->oneTime()->resize()
            ->row([ReplyButton::make('Отправить контакт')->requestContact()])
            ->row([ReplyButton::make('Ввести вручную')]);
    }

    public static function makeEmptyKeyboard(): ReplyKeyboard
    {
        return new ReplyKeyboard([]);
    }

    public static function makeAppKeyboard(string $url): Keyboard
    {
        return Keyboard::make()->buttons([
            Button::make('Открыть приложение')->webApp($url),
        ]);
    }
}
