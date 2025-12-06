<?php

namespace App\Services;

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

        $this->repo->updateLikeStatus($people->id, $user->id, true);
        $this->repo->incrementLikeCount($people);

        return ['liked' => true, 'likes_count' => $people->fresh()->likes_count];
    }

    public function dislike(People $people, User $user): array
    {

        $this->repo->updateLikeStatus($people->id, $user->id, false);
        $this->repo->decrementLikeCount($people);

        return ['liked' => false, 'likes_count' => $people->fresh()->likes_count];
    }

    public function likedList(User $user, int $perPage = 20)
    {
        return $this->repo->getLikedBy($user->id, $perPage);
    }
}
