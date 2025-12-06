<?php

namespace App\Repositories\Eloquent;

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

    public function incrementLikeCount(People $people): void
    {
        People::increment('likes_count');
    }

    public function decrementLikeCount(People $people): void
    {
        People::decrement('likes_count');
    }
}
