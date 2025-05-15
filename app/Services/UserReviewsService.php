<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\ExpertRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserReviewsRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class UserReviewsService
{
    protected $userReviewsRepository;
    protected $expertRepository;
    protected $userRepository;
    protected $bookingRepository;

    public function __construct(
        UserReviewsRepository $userReviewsRepository,
        ExpertRepository $expertRepository,
        UserRepository $userRepository,
        BookingRepository $bookingRepository,
    ){
        $this->userReviewsRepository = $userReviewsRepository;
        $this->expertRepository = $expertRepository;
        $this->userRepository = $userRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function storeReviewForUser(int $userId, array $data)
    {
        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        if (!$expert) {
            \Log::error('Expert not found.');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом',
            ], Response::HTTP_FORBIDDEN));
        }

        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            \Log::error('User not found with id ' . $userId);
            throw new HttpResponseException(response()->json([
                'message' => 'Пользователь не найден.',
            ], Response::HTTP_NOT_FOUND));
        }

        $userIsClientForThisExpert = $this->bookingRepository
            ->userIsClientForThisExpert($expert->id, $userId);
        if (!$userIsClientForThisExpert) {
            \Log::error('The user is not a client for this expert or the service has not yet been provided.');
            throw new HttpResponseException(response()->json([
                'message' => 'Пользователь не является вашим клиентом или услуга еще не предоставлена.'
            ], Response::HTTP_FORBIDDEN));
        }

        $userCountBookingsForThisExpert = $this->bookingRepository
            ->userCountBookingsForThisExpert($expert->id, $userId);

        $expertReviewsForThisUser = $this->userReviewsRepository
            ->expertReviewsForThisUser($expert->id, $userId);

        if ($userCountBookingsForThisExpert <= $expertReviewsForThisUser) {
            \Log::error('The expert has already left review for this expert.');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы уже оставили отзыв данному пользователю.'
            ], Response::HTTP_FORBIDDEN));
        }

        $data['user_id'] = $userId;
        $data['expert_id'] = $expert->id;

        return $this->userReviewsRepository->store($data);
    }
}
