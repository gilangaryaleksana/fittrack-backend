<?php

namespace App\Http\Controllers\Api;

use App\Models\Gear;

class GearController extends ApiCrudController
{
    protected string $model = Gear::class;
    protected ?string $ownerColumn = 'user_id';
    protected bool $scopeIndexToOwner = true;

    protected array $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required|in:shoe,bike',
        'total_distance' => 'nullable|numeric|min:0',
        'is_retired' => 'nullable|boolean',
    ];
}
