<?php

namespace Tests\Unit\V1;

use App\Models\People;
use App\Models\User;
use App\Repositories\Contracts\PeopleRepositoryInterface;
use App\Services\PeopleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PeopleServiceTest extends TestCase
{
    protected MockInterface $peopleRepo;

    protected PeopleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->peopleRepo = Mockery::mock(PeopleRepositoryInterface::class);
        $this->app->instance(PeopleRepositoryInterface::class, $this->peopleRepo);
        $this->service = $this->app->make(PeopleService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_paginate_recommended_returns_paginator()
    {
        $perPage = 10;
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->peopleRepo
            ->shouldReceive('paginateRecommended')
            ->with($perPage)
            ->once()
            ->andReturn($paginator);

        $result = $this->service->listRecommended($perPage);

        $this->assertSame($paginator, $result);
    }

    public function test_increment_like_count_calls_repository_and_returns_likes_count()
    {
        $peopleId = 1;
        $userId = 2;
        $likesCountAfter = 5;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn($userId);

        $people = Mockery::mock(People::class);
        $people->shouldReceive('getAttribute')->with('id')->andReturn($peopleId);
        $people->shouldReceive('fresh')->once()->andReturn((object) ['likes_count' => $likesCountAfter]);

        $this->peopleRepo
            ->shouldReceive('updateLikeStatus')
            ->with($peopleId, $userId, true)
            ->once();

        $this->peopleRepo
            ->shouldReceive('incrementLikeCount')
            ->with($people)
            ->once();

        $result = $this->service->like($people, $user);

        $this->assertIsArray($result);
        $this->assertEquals(['liked' => true, 'likes_count' => $likesCountAfter], $result);
    }

    public function test_decrement_like_count_calls_repository_and_returns_likes_count()
    {
        $peopleId = 1;
        $userId = 2;
        $likesCountAfter = 3;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn($userId);

        $people = Mockery::mock(People::class);
        $people->shouldReceive('getAttribute')->with('id')->andReturn($peopleId);
        $people->shouldReceive('fresh')->once()->andReturn((object) ['likes_count' => $likesCountAfter]);

        $this->peopleRepo
            ->shouldReceive('updateLikeStatus')
            ->with($peopleId, $userId, false)
            ->once();

        $this->peopleRepo
            ->shouldReceive('decrementLikeCount')
            ->with($people)
            ->once();

        $result = $this->service->dislike($people, $user);

        $this->assertIsArray($result);
        $this->assertEquals(['liked' => false, 'likes_count' => $likesCountAfter], $result);
    }

    public function test_get_liked_by_returns_paginator()
    {
        $userId = 123;
        $perPage = 20;
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn($userId);

        $this->peopleRepo
            ->shouldReceive('getLikedBy')
            ->with($userId, $perPage)
            ->once()
            ->andReturn($paginator);

        $result = $this->service->likedList($user, $perPage);

        $this->assertSame($paginator, $result);
    }
}
