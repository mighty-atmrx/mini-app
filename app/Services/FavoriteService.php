<?php

namespace App\Services;

use App\Models\Expert;
use App\Models\Favorite;
use Illuminate\Http\Exceptions\HttpResponseException;

class FavoriteService
{
    public function createFavorite(array $data)
    {
        if (!Expert::find($data['expert_id'])) {
            \Log::error('Expert not found with id: ' . $data['expert_id']);
            return response()->json([
                'message' => 'Не удалось найти эксперта.'
            ]);
        }

        if (Favorite::where('expert_id', $data['expert_id'])
            ->where('user_id', $data['user_id'])
            ->exists()) {
            \Log::info('Expert has already been added to favorites.');
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт уже добавлен в избранное.'
            ]));
        }

        return Favorite::create($data);
    }
}
