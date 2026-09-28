<?php

namespace App\Http\Controllers\Api;

use App\Models\Club;
use App\Models\ClubMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubController extends ApiCrudController
{
    protected string $model = Club::class;
    protected array $with = ['creator:id,name,username,avatar'];
    protected array $filterable = ['sport_type', 'city'];
    protected ?string $ownerColumn = 'created_by';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sport_type' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
        'cover_photo' => 'nullable|string|max:255',
    ];

    protected function query(Request $request)
    {
        return parent::query($request)->withCount('members');
    }

    /** Creator automatically becomes the first admin member. */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $data['created_by'] = $request->user()->id;

        $club = DB::transaction(function () use ($data) {
            $club = Club::create($data);

            ClubMember::create([
                'club_id' => $club->id,
                'user_id' => $data['created_by'],
                'role' => 'admin',
                'joined_at' => now(),
            ]);

            return $club;
        });

        return response()->json($club->load($this->with), 201);
    }
}
