<?php

namespace App\Repositories\Contracts;

use App\Models\People;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PeopleRepositoryInterface
{
    public function paginateRecommended(int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?People;

    public function incrementLikeCount(People $people): void;

    public function decrementLikeCount(People $people): void;
}
