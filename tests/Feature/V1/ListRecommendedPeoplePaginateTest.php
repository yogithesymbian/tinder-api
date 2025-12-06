<?php

namespace Tests\Feature\V1;

use App\Models\People;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListRecommendedPeoplePaginateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_list_peoples_recommended_people_paginate()
    {
        People::factory()->count(15)->create();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/people?per_page=10&page=1');

        $response->assertStatus(200);

        $this->assertCount(10, $response->json('data'));

        $response->assertJson([
            'total' => 15,
            'per_page' => 10,
            'current_page' => 1,
            'last_page' => 2,
        ]);

        $responsePage2 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/people?per_page=10&page=2');

        $this->assertCount(5, $responsePage2->json('data'));

        $responsePage2->assertJson([
            'current_page' => 2,
        ]);
    }
}
