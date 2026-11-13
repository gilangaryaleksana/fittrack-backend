<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityStream;
use App\Models\ActivityType;
use App\Models\Club;
use App\Models\User;
use Database\Seeders\ActivityTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_can_be_updated_and_invalid_values_are_rejected(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('profile-test')->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'height_cm' => 172.5,
                'weight_kg' => 68.25,
                'birth_date' => '1995-04-12',
                'gender' => 'other',
            ])
            ->assertOk()
            ->assertJsonStructure([
                'id', 'name', 'username', 'email', 'height_cm', 'weight_kg',
                'birth_date', 'gender',
            ])
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('height_cm', 172.5)
            ->assertJsonPath('weight_kg', 68.25)
            ->assertJsonPath('birth_date', '1995-04-12T00:00:00.000000Z')
            ->assertJsonPath('gender', 'other');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'height_cm' => 172.5,
            'weight_kg' => 68.25,
            'birth_date' => '1995-04-12 00:00:00',
            'gender' => 'other',
        ]);

        $this->withToken($token)
            ->putJson('/api/profile', [
                'height_cm' => 301,
                'birth_date' => '2999-01-01',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['height_cm', 'birth_date']);
    }

    public function test_password_change_keeps_current_token_and_revokes_other_tokens(): void
    {
        $user = $this->createUser();
        $currentToken = $user->createToken('current');
        $otherToken = $user->createToken('other');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/change-password', [
                'current_password' => 'password123',
                'new_password' => 'new-password-123',
                'new_password_confirmation' => 'new-password-123',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/change-password', [
                'current_password' => 'wrong-password',
                'new_password' => 'another-password',
                'new_password_confirmation' => 'another-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_account_deletion_removes_related_data_and_rejects_wrong_password(): void
    {
        $this->seed(ActivityTypeSeeder::class);
        $user = $this->createUser();
        $token = $user->createToken('account-test')->plainTextToken;
        $activity = Activity::create([
            'user_id' => $user->id,
            'activity_type_id' => ActivityType::firstOrFail()->id,
            'title' => 'Test activity',
            'start_date' => now(),
        ]);
        $stream = ActivityStream::create([
            'activity_id' => $activity->id,
            'sequence' => 1,
            'latitude' => -6.2,
            'longitude' => 106.8,
            'recorded_at' => now(),
        ]);
        $club = Club::create([
            'name' => 'Test club',
            'created_by' => $user->id,
        ]);
        DB::table('sessions')->insert([
            'id' => 'profile-account-test-session',
            'user_id' => $user->id,
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->withToken($token)
            ->deleteJson('/api/account', ['password' => 'wrong-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->withToken($token)
            ->deleteJson('/api/account', ['password' => 'password123'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        $this->assertDatabaseMissing('activity_streams', ['id' => $stream->id]);
        $this->assertDatabaseMissing('clubs', ['id' => $club->id]);
        $this->assertDatabaseMissing('sessions', ['id' => 'profile-account-test-session']);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user->getMorphClass(),
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Profile Test',
            'username' => 'profile-test',
            'email' => 'profile-test@example.com',
            'password' => 'password123',
        ]);
    }
}
