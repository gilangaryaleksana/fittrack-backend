<?php

namespace App\Http\Controllers\Api;

use App\Models\Kudo;
use Illuminate\Http\Request;

class KudoController extends ApiCrudController
{
    protected string $model = Kudo::class;
    protected array $with = ['user:id,name,username,avatar'];
    protected array $filterable = ['activity_id'];
    protected ?string $ownerColumn = 'user_id';

    public function store(Request $request)
    {
        $data = $request->validate(['activity_id' => 'required|exists:activities,id']);

        // Idempotent: giving kudos twice just returns the existing row.
        $kudo = Kudo::firstOrCreate([
            'activity_id' => $data['activity_id'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json($kudo->load($this->with), 201);
    }
}
