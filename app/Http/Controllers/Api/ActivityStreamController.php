<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityStream;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityStreamController extends ApiCrudController
{
    protected string $model = ActivityStream::class;
    protected array $filterable = ['activity_id'];

    protected array $rules = [
        'activity_id' => 'required|exists:activities,id',
        'sequence' => 'required|integer|min:0',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'elevation' => 'nullable|numeric',
        'recorded_at' => 'required|date',
        'heartrate' => 'nullable|integer',
        'cadence' => 'nullable|integer',
    ];

    public function index(Request $request)
    {
        $request->validate(['activity_id' => 'required|exists:activities,id']);

        return response()->json(
            ActivityStream::where('activity_id', $request->input('activity_id'))
                ->orderBy('sequence')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $this->assertOwnsActivity($request, $data['activity_id']);

        return response()->json(ActivityStream::create($data), 201);
    }

    /** Upload a whole GPS route at once (what the mobile app sends on finish). */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'points' => 'required|array|min:1',
            'points.*.sequence' => 'required|integer|min:0',
            'points.*.latitude' => 'required|numeric',
            'points.*.longitude' => 'required|numeric',
            'points.*.elevation' => 'nullable|numeric',
            'points.*.recorded_at' => 'required|date',
            'points.*.heartrate' => 'nullable|integer',
            'points.*.cadence' => 'nullable|integer',
        ]);

        $this->assertOwnsActivity($request, $data['activity_id']);

        $rows = array_map(fn ($p) => [
            'activity_id' => $data['activity_id'],
            'sequence' => $p['sequence'],
            'latitude' => $p['latitude'],
            'longitude' => $p['longitude'],
            'elevation' => $p['elevation'] ?? null,
            'recorded_at' => Carbon::parse($p['recorded_at'])->format('Y-m-d H:i:s'),
            'heartrate' => $p['heartrate'] ?? null,
            'cadence' => $p['cadence'] ?? null,
        ], $data['points']);

        foreach (array_chunk($rows, 500) as $chunk) {
            ActivityStream::insert($chunk);
        }

        return response()->json(['inserted' => count($rows)], 201);
    }

    protected function authorizeOwner(Request $request, $record): void
    {
        abort_unless(
            (int) $record->activity->user_id === (int) $request->user()->id,
            403,
            'Aktivitas ini bukan milik kamu'
        );
    }
}
