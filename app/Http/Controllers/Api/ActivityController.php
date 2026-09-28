<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends ApiCrudController
{
    protected string $model = Activity::class;
    protected array $with = ['activityType', 'user:id,name,username,avatar'];
    protected ?string $ownerColumn = 'user_id';
    protected bool $scopeIndexToOwner = true;

    protected array $rules = [
        'activity_type_id' => 'required|exists:activity_types,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'distance' => 'nullable|numeric|min:0',
        'moving_time' => 'nullable|integer|min:0',
        'elapsed_time' => 'nullable|integer|min:0',
        'elevation_gain' => 'nullable|numeric',
        'start_date' => 'required|date',
        'start_lat' => 'nullable|numeric',
        'start_lng' => 'nullable|numeric',
        'end_lat' => 'nullable|numeric',
        'end_lng' => 'nullable|numeric',
        'average_speed' => 'nullable|numeric',
        'max_speed' => 'nullable|numeric',
        'calories' => 'nullable|numeric|min:0',
        'visibility' => 'nullable|in:public,followers,private',
    ];

    public function show(Request $request, $id)
    {
        $activity = Activity::with([
            'activityType',
            'user:id,name,username,avatar',
            'streams',
            'photos',
            'comments.user:id,name,username,avatar',
        ])->withCount('kudos')->findOrFail($id);

        $this->ensureVisible($request, $activity);

        return response()->json($activity);
    }

    /** Own activities + activities of accepted followings (public/followers). */
    public function feed(Request $request)
    {
        $me = $request->user();

        $followingIds = $me->following()
            ->wherePivot('status', 'accepted')
            ->pluck('users.id');

        $feed = Activity::with(['activityType', 'user:id,name,username,avatar'])
            ->withCount(['kudos', 'comments'])
            ->where(function ($q) use ($me, $followingIds) {
                $q->where('user_id', $me->id)
                    ->orWhere(function ($q2) use ($followingIds) {
                        $q2->whereIn('user_id', $followingIds)
                            ->whereIn('visibility', ['public', 'followers']);
                    });
            })
            ->latest('start_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json($feed);
    }

    private function ensureVisible(Request $request, Activity $activity): void
    {
        $me = $request->user();

        if ((int) $activity->user_id === (int) $me->id || $activity->visibility === 'public') {
            return;
        }

        if ($activity->visibility === 'followers') {
            $isFollower = $me->following()
                ->wherePivot('status', 'accepted')
                ->where('users.id', $activity->user_id)
                ->exists();

            if ($isFollower) {
                return;
            }
        }

        abort(403, 'Aktivitas ini bersifat privat');
    }
}
