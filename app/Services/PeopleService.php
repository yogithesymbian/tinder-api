<?php

namespace App\Services;

use App\Models\Like;
use App\Models\People;
use App\Models\User;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PeopleService
{
    public function __construct(protected PeopleRepositoryInterface $repo) {}

    public function listRecommended(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repo->paginateRecommended($perPage);
    }

    public function like(People $people, User $user): array
    {
        Like::updateOrCreate(
            ['people_id' => $people->id, 'user_id' => $user->id],
            ['is_like' => true]
        );

        $this->repo->incrementLikeCount($people);

        return ['liked' => true, 'likes_count' => $people->fresh()->likes_count];
    }

    public function dislike(People $people, User $user): array
    {
        Like::updateOrCreate(
            ['people_id' => $people->id, 'user_id' => $user->id],
            ['is_like' => false]
        );
        $people->likes_count = $people->likes()->where('is_like', true)->count();
        $people->save();

        return ['liked' => false, 'likes_count' => $people->likes_count];
    }

    public function likedList(User $user, int $perPage = 20)
    {
        return People::whereHas('likes', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('is_like', true);
        })->paginate($perPage);
    }
}
