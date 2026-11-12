<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityType;
use App\Models\User;
use Database\Seeders\ActivityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivitySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_token_can_create_and_list_an_activity(): void
    {
        $this->seed(ActivityTypeSeeder::class);
        $user = $this->createUser();

        $login = $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['user' => ['id'], 'token']);

        $typeList = $this->withToken($login->json('token'))
            ->getJson('/api/activity-types?per_page=50')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name']],
                'current_page',
                'last_page',
            ]);

        $this->assertContains('Lari', array_column($typeList->json('data'), 'name'));

        $activityType = ActivityType::where('name', 'Lari')->firstOrFail();
        $payload = [
            'activity_type_id' => $activityType->id,
            'title' => 'Lari pagi',
            'description' => 'Tes sinkronisasi',
            'distance' => 3.5,
            'moving_time' => 1200,
            'elapsed_time' => 1250,
            'start_date' => '2026-11-12T06:00:00+07:00',
            'calories' => 250,
            'visibility' => 'private',
            'start_lat' => -6.2,
            'start_lng' => 106.8,
            'end_lat' => -6.21,
            'end_lng' => 106.81,
        ];

        $created = $this->withToken($login->json('token'))
            ->postJson('/api/activities', $payload)
            ->assertCreated()
            ->assertJsonPath('user_id', $user->id)
            ->assertJsonPath('activity_type_id', $activityType->id)
            ->assertJsonPath('visibility', 'private');

        $activityId = $created->json('id');
        $this->assertIsInt($activityId);
        $this->assertDatabaseHas('activities', [
            'id' => $activityId,
            'user_id' => $user->id,
            'activity_type_id' => $activityType->id,
        ]);

        $this->getJson('/api/activities?per_page=50&page=1')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'activity_type_id', 'user_id']],
                'current_page',
                'last_page',
            ])
            ->assertJsonPath('data.0.id', $activityId)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 1);
    }

    public function test_activity_creation_returns_validation_errors_for_invalid_input(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/activities', [
                'activity_type_id' => 999999,
                'title' => '',
                'start_date' => 'not-a-date',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'activity_type_id',
                'title',
                'start_date',
            ]);

        $this->assertSame(0, Activity::count());
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Sync Test',
            'username' => 'sync-test',
            'email' => 'sync-test@example.com',
            'password' => 'password123',
        ]);
    }
}
