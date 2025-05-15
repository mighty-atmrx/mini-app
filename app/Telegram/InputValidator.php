<?php

namespace App\Telegram;

class InputValidator
{
    public static function validateName(string $name): bool
    {
        return strlen($name) >= 2;
    }

    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^\+7?\d{10}$/', $phone) === 1;
    }

    public static function validateBirthdate(string $birthdate): bool
    {
        return preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $birthdate) &&
            \DateTime::createFromFormat('d.m.Y', $birthdate) !== false;
    }

    public static function formatBirthdate(string $birthdate): string
    {
        return \DateTime::createFromFormat('d.m.Y', $birthdate)->format('Y-m-d');
    }
}
