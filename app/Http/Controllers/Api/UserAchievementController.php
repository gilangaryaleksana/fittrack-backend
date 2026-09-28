<?php

namespace App\Http\Controllers\Api;

use App\Models\UserAchievement;
use Illuminate\Http\Request;

class UserAchievementController extends ApiCrudController
{
    protected string $model = UserAchievement::class;
    protected array $with = ['achievement'];
    protected ?string $ownerColumn = 'user_id';
    protected bool $scopeIndexToOwner = true;

    /** Unlock an achievement (idempotent — earning it twice is a no-op). */
    public function store(Request $request)
    {
        $data = $request->validate(['achievement_id' => 'required|exists:achievements,id']);

        $earned = UserAchievement::firstOrCreate(
            ['user_id' => $request->user()->id, 'achievement_id' => $data['achievement_id']],
            ['earned_at' => now()]
        );

        return response()->json($earned->load($this->with), 201);
    }
}
