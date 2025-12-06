<?php

namespace App\Repositories\Eloquent;

use App\Models\Like;
use App\Models\People;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PeopleRepository implements PeopleRepositoryInterface
{
    public function paginateRecommended(int $perPage = 10): LengthAwarePaginator
    {
        return People::orderByDesc('likes_count')->paginate($perPage);
    }

    public function find(int $id): ?People
    {
        return People::find($id);
    }

    public function updateLikeStatus(int $peopleId, int $userId, bool $isLike): void
    {
        Like::updateOrCreate(
            ['people_id' => $peopleId, 'user_id' => $userId],
            ['is_like' => $isLike]
        );
    }

    public function incrementLikeCount(People $people): void
    {
        $people->increment('likes_count');
    }

    public function decrementLikeCount(People $people): void
    {
        $people->decrement('likes_count');
    }

    public function getLikedBy(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return People::whereHas('likes', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('is_like', true);
        })->paginate($perPage);
    }
}
