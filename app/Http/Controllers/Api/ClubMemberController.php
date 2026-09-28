<?php

namespace App\Http\Controllers\Api;

use App\Models\ClubMember;
use Illuminate\Http\Request;

class ClubMemberController extends ApiCrudController
{
    protected string $model = ClubMember::class;
    protected array $with = ['user:id,name,username,avatar'];
    protected array $filterable = ['club_id', 'user_id'];
    protected ?string $ownerColumn = 'user_id';

    /** Join a club as a regular member (idempotent). */
    public function store(Request $request)
    {
        $data = $request->validate(['club_id' => 'required|exists:clubs,id']);

        $member = ClubMember::firstOrCreate(
            ['club_id' => $data['club_id'], 'user_id' => $request->user()->id],
            ['role' => 'member', 'joined_at' => now()]
        );

        return response()->json($member->load($this->with), 201);
    }

    /** Only a club admin can promote/demote members. */
    public function update(Request $request, $id)
    {
        $member = ClubMember::findOrFail($id);
        $this->assertClubAdmin($request, $member->club_id);

        $member->update($request->validate(['role' => 'required|in:admin,member']));

        return response()->json($member->fresh($this->with));
    }

    /** Leave the club yourself, or an admin removes someone. */
    public function destroy(Request $request, $id)
    {
        $member = ClubMember::findOrFail($id);

        if ((int) $member->user_id !== (int) $request->user()->id) {
            $this->assertClubAdmin($request, $member->club_id);
        }

        $member->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    private function assertClubAdmin(Request $request, int $clubId): void
    {
        $isAdmin = ClubMember::where('club_id', $clubId)
            ->where('user_id', $request->user()->id)
            ->where('role', 'admin')
            ->exists();

        abort_unless($isAdmin, 403, 'Hanya admin klub yang boleh melakukan ini');
    }
}
