<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityType;

class ActivityTypeController extends ApiCrudController
{
    protected string $model = ActivityType::class;

    protected array $rules = [
        'name' => 'required|string|max:100',
        'icon' => 'nullable|string|max:100',
    ];
}
