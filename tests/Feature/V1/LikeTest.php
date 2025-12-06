<?php

namespace Tests\Feature\V1;

use App\Models\People;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_people()
    {
        $user = User::factory()->create();
        $people = People::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/people/{$people->id}/like");

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'people_id' => $people->id,
            'user_id' => $user->id,
            'is_like' => true,
        ]);

        $this->assertDatabaseHas('people', [
            'id' => $people->id,
            // likes_count is incremented by repository->increment, so check >=1
        ]);
    }

    public function test_user_can_like_then_dislike_people()
    {
        $user = User::factory()->create();
        $people = People::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/people/{$people->id}/like");

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'people_id' => $people->id,
            'user_id' => $user->id,
            'is_like' => true,
        ]);

        $this->assertDatabaseHas('people', [
            'id' => $people->id,
            // likes_count is incremented by repository->increment, so check >=1
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/people/{$people->id}/dislike");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('likes', [
            'people_id' => $people->id,
            'user_id' => $user->id,
            'is_like' => true,
        ]);
        $this->assertDatabaseHas('likes', [
            'people_id' => $people->id,
            'user_id' => $user->id,
            'is_like' => false,
        ]);
    }
}
