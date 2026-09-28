<?php

namespace App\Http\Controllers\Api;

use App\Models\SegmentEffort;
use Illuminate\Http\Request;

class SegmentEffortController extends ApiCrudController
{
    protected string $model = SegmentEffort::class;
    protected array $with = ['segment:id,name,distance', 'user:id,name,username,avatar'];
    protected array $filterable = ['segment_id', 'activity_id', 'user_id'];
    protected ?string $ownerColumn = 'user_id';

    protected array $rules = [
        'segment_id' => 'required|exists:segments,id',
        'activity_id' => 'required|exists:activities,id',
        'elapsed_time' => 'required|integer|min:0',
        'moving_time' => 'required|integer|min:0',
        'start_date' => 'required|date',
        'kom_rank' => 'nullable|integer|min:1',
    ];

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $this->assertOwnsActivity($request, $data['activity_id']);
        $data['user_id'] = $request->user()->id;

        return response()->json(SegmentEffort::create($data)->load($this->with), 201);
    }
}
