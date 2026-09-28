<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

/**
 * Generic REST CRUD. Child controllers only declare the model, validation
 * rules, eager loads, filters and (optionally) which column marks ownership.
 */
abstract class ApiCrudController extends Controller
{
    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected string $model;
    protected array $with = [];
    protected array $rules = [];
    /** Query-string filters allowed on index, e.g. ?activity_id=5 */
    protected array $filterable = [];
    /** Column holding the owner's user id (set automatically on store). */
    protected ?string $ownerColumn = null;
    /** When true, index only returns rows owned by the current user. */
    protected bool $scopeIndexToOwner = false;

    protected function query(Request $request)
    {
        $q = ($this->model)::with($this->with);

        foreach ($this->filterable as $column) {
            if ($request->filled($column)) {
                $q->where($column, $request->input($column));
            }
        }

        if ($this->ownerColumn && $this->scopeIndexToOwner) {
            $q->where($this->ownerColumn, $request->user()->id);
        }

        return $q;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->query($request)->latest('id')->paginate($request->integer('per_page', 20))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);

        if ($this->ownerColumn) {
            $data[$this->ownerColumn] = $request->user()->id;
        }

        $record = ($this->model)::create($data);

        return response()->json($record->load($this->with), 201);
    }

    public function show(Request $request, $id)
    {
        return response()->json(($this->model)::with($this->with)->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $record = ($this->model)::findOrFail($id);
        $this->authorizeOwner($request, $record);

        $record->update($request->validate($this->updateRules()));

        return response()->json($record->fresh($this->with));
    }

    public function destroy(Request $request, $id)
    {
        $record = ($this->model)::findOrFail($id);
        $this->authorizeOwner($request, $record);
        $record->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    protected function authorizeOwner(Request $request, $record): void
    {
        if ($this->ownerColumn
            && (int) $record->{$this->ownerColumn} !== (int) $request->user()->id) {
            abort(403, 'Kamu tidak punya akses ke data ini');
        }
    }

    /** Same as store rules, but every field becomes optional on update. */
    protected function updateRules(): array
    {
        return array_map(
            fn ($rule) => str_replace('required', 'sometimes', $rule),
            $this->rules
        );
    }

    protected function assertOwnsActivity(Request $request, int|string $activityId): void
    {
        $owns = Activity::where('id', $activityId)
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_unless($owns, 403, 'Aktivitas ini bukan milik kamu');
    }
}
