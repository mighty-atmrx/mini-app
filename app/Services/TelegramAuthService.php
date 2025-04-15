<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class TelegramAuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function verifyInitData(string $initData): ?array
    {
        \Log::info('Raw initData', ['initData' => $initData]);
        parse_str($initData, $params);
        \Log::info('Parsed initData params', ['params' => $params]);

        if (!isset($params['hash'])) {
            \Log::error('Hash missing in initData');
            return null;
        }

        $dataCheck = [];
        foreach ($params as $key => $value) {
            if ($key !== 'hash' && is_string($value)) {
                $dataCheck[] = "$key=$value";
            }
        }
        sort($dataCheck);
        $dataCheckString = implode("\n", $dataCheck);
        \Log::info('data_check_string', ['string' => $dataCheckString]);

        $botToken = env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            \Log::error('Bot token missing');
            return null;
        }
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $computedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));
        \Log::info('Signature check', ['computedHash' => $computedHash, 'receivedHash' => $params['hash']]);

        if (!hash_equals($params['hash'], $computedHash)) {
            \Log::error('Invalid initData signature', ['computedHash' => $computedHash, 'receivedHash' => $params['hash']]);
            return null;
        }

        \Log::info('Raw user param', ['user' => $params['user']]);
        $userData = json_decode($params['user'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($userData['id'])) {
            \Log::error('Invalid user data');
            return null;
        }

        \Log::info('initData verified', ['user_id' => $userData['id']]);
        return $userData;
    }
}
