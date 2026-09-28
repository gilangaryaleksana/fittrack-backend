<?php

namespace App\Http\Controllers\Api;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoController extends ApiCrudController
{
    protected string $model = Photo::class;
    protected array $filterable = ['activity_id'];
    protected ?string $ownerColumn = 'user_id';

    protected array $rules = [
        'activity_id' => 'required|exists:activities,id',
        'url' => 'required|string|max:255',
        'caption' => 'nullable|string|max:255',
    ];

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $this->assertOwnsActivity($request, $data['activity_id']);
        $data['user_id'] = $request->user()->id;

        return response()->json(Photo::create($data), 201);
    }

    protected function updateRules(): array
    {
        return ['caption' => 'nullable|string|max:255'];
    }
}
