<?php

namespace App\Http\Controllers\Api;

use App\Models\Achievement;

class AchievementController extends ApiCrudController
{
    protected string $model = Achievement::class;

    protected array $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:100',
        'criteria' => 'nullable|string|max:255',
    ];
}
