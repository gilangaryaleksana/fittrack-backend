<?php

namespace App\Http\Controllers\Api;

use App\Models\Segment;
use App\Models\SegmentEffort;

class SegmentController extends ApiCrudController
{
    protected string $model = Segment::class;
    protected array $with = ['activityType'];
    protected array $filterable = ['activity_type_id', 'city'];

    protected array $rules = [
        'name' => 'required|string|max:255',
        'activity_type_id' => 'required|exists:activity_types,id',
        'distance' => 'required|numeric|min:0',
        'average_grade' => 'nullable|numeric',
        'elevation_gain' => 'nullable|numeric',
        'start_lat' => 'required|numeric',
        'start_lng' => 'required|numeric',
        'end_lat' => 'required|numeric',
        'end_lng' => 'required|numeric',
        'city' => 'nullable|string|max:100',
    ];

    /** Fastest efforts on this segment, best first, with rank. */
    public function leaderboard($id)
    {
        Segment::findOrFail($id);

        $efforts = SegmentEffort::with('user:id,name,username,avatar')
            ->where('segment_id', $id)
            ->orderBy('elapsed_time')
            ->limit(50)
            ->get()
            ->values()
            ->map(fn ($effort, $i) => $effort->setAttribute('rank', $i + 1));

        return response()->json($efforts);
    }
}
