<?php

namespace App\Telegram;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Telegram\State\AwaitingBookingDateTimeState;
use App\Telegram\State\AwaitingRejectReasonState;
use App\Telegram\State\BirthdateState;
use App\Telegram\State\NameState;
use App\Telegram\State\PhoneManualState;
use App\Telegram\State\PhoneState;
use App\Telegram\State\StateManager;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Stringable;

class Handler extends WebhookHandler
{
    private UserRepository $userRepository;
    private $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->userRepository = new UserRepository();
    }

    public function start(): void
    {
        \Log::info('Start method triggered', ['telegram_id' => $this->chat->chat_id]);

        if (!$this->chat) {
            \Log::error('Chat not initialized in start');
            return;
        }

        $imageUrl = config('telegram.image_url', 'https://bluejay-pretty-clearly.ngrok-free.app/expert.jpg');

        $user = $this->userRepository->findByTelegramId(hash('sha256', $this->chat->chat_id));
        \Log::info('User check in start', [
            'telegram_id' => $this->chat->chat_id,
            'user_found' => $user ? $user->toArray() : null,
        ]);

        if ($user) {
            \Log::info('User found, sending greeting', [
                'telegram_id' => $this->chat->chat_id,
                'user_id' => $user->id,
            ]);
            $this->chat->photo($imageUrl)
                ->markdown("*Привет, {$user->first_name}!* Рады видеть тебя снова!")
                ->keyboard(KeyboardFactory::makeAppKeyboard(config('telegram.mini_app_url')))
                ->send();
            return;
        }

        \Log::info('No user found, starting registration', ['telegram_id' => $this->chat->chat_id]);
        $this->chat->photo($imageUrl)
            ->markdown("*Привет! Нажми “Продолжить”* для регистрации.")
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Продолжить')->action('getUserData'),
            ]))
            ->send();

        $stateManager = new StateManager($this->chat);
        $userData = [];
        $step = 'name';
        if ($firstName = $this->message->from()->firstName()) {
            $userData['first_name'] = $firstName;
            $userData['last_name'] = $this->message->from()->lastName() ?? '';
            $step = 'phone';
        }

        $stateManager->setUserData($this->chat, $userData);
        $stateManager->setStep($this->chat, $step);
    }

    public function getUserData(): void
    {
        if (!$this->chat) {
            \Log::error('Chat not initialized in getUserData');
            return;
        }

        \Log::info('getUserData method triggered', ['telegram_id' => $this->chat->chat_id]);

        $stateManager = new StateManager($this->chat);
        $step = $stateManager->getStep($this->chat);
        $stateMap = [
            'name' => NameState::class,
            'phone' => PhoneState::class,
        ];

        if (!isset($stateMap[$step])) {
            \Log::warning('Invalid step in getUserData', ['step' => $step]);
            return;
        }

        $state = new $stateMap[$step]($stateManager);
        $state->prompt($this->chat);
    }

    public function handleChatMessage(Stringable $text): void
    {
        if (!$this->chat) {
            \Log::error('Chat not initialized in handleChatMessage');
            return;
        }

        \Log::info('handleChatMessage triggered', [
            'telegram_id' => $this->chat->chat_id,
            'text' => $text->toString(),
        ]);

        $stateManager = new StateManager($this->chat);
        $step = $stateManager->getStep($this->chat);
        $userData = $stateManager->getUserData($this->chat);

        if ($step === 'awaiting_booking_confirmation') {
            $userResponse = trim($text->toString());

            if ($userResponse === '✅ Да') {
                $stateManager->setStep($this->chat, 'awaiting_booking_datetime');
                \Log::info('User confirmed booking, switching to awaiting_booking_datetime');
                app(AwaitingBookingDateTimeState::class, ['stateManager' => $stateManager])
                    ->prompt($this->chat);
                return;
            }

            if ($userResponse === '❌ Нет') {
                $stateManager->setStep($this->chat, 'awaiting_reject_reason');
                \Log::info('User rejected booking, switching to awaiting_reject_reason');
                app(AwaitingRejectReasonState::class, ['stateManager' => $stateManager])
                    ->prompt($this->chat);
                return;
            }

            $this->chat->message('Пожалуйста, выбери один из вариантов: ✅ Да или ❌ Нет')->send();
            return;
        }

        $stateMap = [
            'name' => NameState::class,
            'phone' => PhoneState::class,
            'phone_manual' => PhoneManualState::class,
            'birthdate' => BirthdateState::class,
            'awaiting_booking_datetime' => AwaitingBookingDateTimeState::class,
            'awaiting_reject_reason' => AwaitingRejectReasonState::class,

        ];

        if (!isset($stateMap[$step])) {
            \Log::warning('Invalid step', ['step' => $step]);
            return;
        }

        $state = new $stateMap[$step]($stateManager);
        $nextState = $state->handle($this->chat, $text, $this->message);

        if ($nextState === $state && $state instanceof AwaitingBookingDateTimeState) {
            if (!$this->isFirstInput($this->chat, $text)) {
                \Log::info('Skipping prompt after invalid input', ['step' => $step, 'chat_id' => $this->chat->chat_id]);
            } else {
                $state->prompt($this->chat);
            }
        } elseif ($nextState) {
            \Log::info('Calling prompt for next state', ['next_step' => $stateManager->getStep($this->chat)]);
            $nextState->prompt($this->chat);
        } else {
            \Log::info('No next state, checking user data');
            $userData = $stateManager->getUserData($this->chat);

            if (!isset($userData['first_name'], $userData['phone'], $userData['birthdate'])) {
                \Log::warning('Incomplete user data, registration not completed', [
                    'telegram_id' => $this->chat->chat_id,
                    'user_data' => $userData,
                ]);

                if (in_array($step, ['awaiting_booking_datetime', 'awaiting_reject_reason'])) {
                    \Log::info('Skipping registration check for booking-related step', ['step' => $step, 'chat_id' => $this->chat->chat_id]);
                    $stateManager->clear($this->chat);
                    return;
                }

                if (isset($userData['last_attempted_phone']) && $text->toString() === $userData['last_attempted_phone']) {
                    \Log::info('Validation error message already sent, skipping prompt');
                    return;
                }

                $state->prompt($this->chat);
                return;
            }

            try {
                $user = DB::transaction(function () use ($userData) {
                    $user = $this->userService->userCreate($userData, $this->chat->chat_id);
                    \Log::info('User created in transaction', ['user_id' => $user->id]);
                    return $user;
                });
                \Log::info('User synced', ['user_id' => $user->id]);
                $response = $this->chat->message("Регистрация завершена!\nИмя: {$userData['first_name']}\nФамилия: {$userData['last_name']}\nТелефон: {$userData['phone']}\nДата рождения: {$userData['birthdate']}")
                    ->keyboard(KeyboardFactory::makeAppKeyboard(config('telegram.mini_app_url')))
                    ->send();
                \Log::info('Registration completed', [
                    'telegram_id' => $this->chat->chat_id,
                    'response_status' => $response->status(),
                ]);
                $stateManager->clear($this->chat);
            } catch (\Exception $e) {
                $this->chat->message('Ошибка при сохранении данных. Попробуй позже.')->send();
                \Log::error('Registration failed', [
                    'telegram_id' => $this->chat->chat_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function openapp(): void
    {
        if (!$this->chat) {
            \Log::error('Chat not initialized in openapp');
            return;
        }

        $telegramUserId = hash('sha256', $this->chat->chat_id);
        if (!User::where('telegram_user_id', $telegramUserId)->exists()) {
            \Log::error('User not found');
            $this->chat->message('Вы не зарегистрированы. Чтобы начать регистрацию нажмите на кнопку ниже👇')
                ->keyboard(Keyboard::make()->buttons([
                    Button::make('Начать (/start)')->action('start')
                ]))
                ->send();
            return;
        }

        \Log::info('Openapp method called', ['telegram_id' => $this->chat->chat_id]);

        $this->chat->message('Click to open the Mini App')
            ->keyboard(KeyboardFactory::makeAppKeyboard(config('telegram.mini_app_url')))
            ->send();
    }

    private function isFirstInput(TelegraphChat $chat, Stringable $text): bool
    {
        $stateManager = new StateManager($chat);
        $userData = $stateManager->getUserData($chat);
        return !isset($userData['last_input_time']) || $userData['last_input_time'] !== $text->toString();
    }

    public function handleAction(Stringable $action): void
    {
        if (str_starts_with($action, 'booking_yes_')) {
            $bookingId = (int)str_replace('booking_yes_', '', $action);
            $this->handleBookingYes($bookingId);
        } elseif (str_starts_with($action, 'booking_no_')) {
            $bookingId = (int)str_replace('booking_no_', '', $action);
            $this->handleBookingNo($bookingId);
        } else {
            \Log::warning('Неизвестный action получен', ['action' => $action]);
            $this->chat->message('Неизвестное действие')->send();
        }
    }

    public function handleBookingYes(int $bookingId): void
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $this->chat->message('Отлично! Пожалуйста, укажите дату и время записи. Например: 01.01.2025 10:00.')
                ->send();

            $stateManager = new StateManager($this->chat);
            $stateManager->setStep($this->chat, 'awaiting_booking_datetime');
            $currentData = $stateManager->getUserData($this->chat);
            $currentData['booking_id'] = $bookingId;
            $stateManager->setUserData($this->chat, $currentData);
        }
    }

    public function handleBookingNo(int $bookingId): void
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $this->chat->message('Пожалуйста, укажите причину отмены?')->send();

            $stateManager = new StateManager($this->chat);
            $stateManager->setStep($this->chat, 'awaiting_reject_reason');
            $currentData = $stateManager->getUserData($this->chat);
            $currentData['booking_id'] = $bookingId;
            $stateManager->setUserData($this->chat, $currentData);
        }
    }
}
