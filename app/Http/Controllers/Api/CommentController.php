<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;

class CommentController extends ApiCrudController
{
    protected string $model = Comment::class;
    protected array $with = ['user:id,name,username,avatar'];
    protected array $filterable = ['activity_id'];
    protected ?string $ownerColumn = 'user_id';

    protected array $rules = [
        'activity_id' => 'required|exists:activities,id',
        'body' => 'required|string',
    ];

    /** Only the text of a comment can be edited afterwards. */
    protected function updateRules(): array
    {
        return ['body' => 'required|string'];
    }
}
