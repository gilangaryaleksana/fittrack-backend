<?php

namespace App\Http\Controllers\Api;

use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends ApiCrudController
{
    protected string $model = Follow::class;
    protected array $with = [
        'follower:id,name,username,avatar',
        'following:id,name,username,avatar',
    ];

    /** Every follow row where I am either side (followers + followings). */
    protected function query(Request $request)
    {
        $me = $request->user()->id;

        return Follow::with($this->with)
            ->where(fn ($q) => $q->where('follower_id', $me)->orWhere('following_id', $me));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['following_id' => 'required|exists:users,id']);

        abort_if(
            (int) $data['following_id'] === (int) $request->user()->id,
            422,
            'Tidak bisa follow diri sendiri'
        );

        $follow = Follow::firstOrCreate(
            ['follower_id' => $request->user()->id, 'following_id' => $data['following_id']],
            ['status' => 'pending']
        );

        return response()->json($follow->load($this->with), 201);
    }

    /** Only the person being followed can accept a request. */
    public function update(Request $request, $id)
    {
        $follow = Follow::findOrFail($id);

        abort_unless(
            (int) $follow->following_id === (int) $request->user()->id,
            403,
            'Hanya user yang di-follow yang bisa menerima request'
        );

        $follow->update($request->validate(['status' => 'required|in:pending,accepted']));

        return response()->json($follow->fresh($this->with));
    }

    /** Unfollow (follower) or reject/remove (followed) — either side may delete. */
    public function destroy(Request $request, $id)
    {
        $follow = Follow::findOrFail($id);
        $me = (int) $request->user()->id;

        abort_unless(
            (int) $follow->follower_id === $me || (int) $follow->following_id === $me,
            403,
            'Kamu tidak punya akses ke data ini'
        );

        $follow->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
