<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ActivityStreamController;
use App\Http\Controllers\Api\ActivityTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClubController;
use App\Http\Controllers\Api\ClubMemberController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\GearController;
use App\Http\Controllers\Api\KudoController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\SegmentController;
use App\Http\Controllers\Api\SegmentEffortController;
use App\Http\Controllers\Api\UserAchievementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Everything below needs "Authorization: Bearer <token>"
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/account', [AuthController::class, 'deleteAccount']);

    Route::apiResource('users', UserController::class)->except(['store']);

    Route::apiResource('activity-types', ActivityTypeController::class);

    Route::get('/feed', [ActivityController::class, 'feed']);
    Route::apiResource('activities', ActivityController::class);

    Route::post('/activity-streams/bulk', [ActivityStreamController::class, 'bulk']);
    Route::apiResource('activity-streams', ActivityStreamController::class);

    Route::apiResource('follows', FollowController::class);
    Route::apiResource('kudos', KudoController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('comments', CommentController::class);
    Route::apiResource('photos', PhotoController::class);

    Route::get('/segments/{id}/leaderboard', [SegmentController::class, 'leaderboard']);
    Route::apiResource('segments', SegmentController::class);
    Route::apiResource('segment-efforts', SegmentEffortController::class);

    Route::apiResource('clubs', ClubController::class);
    Route::apiResource('club-members', ClubMemberController::class);

    Route::apiResource('gears', GearController::class);

    Route::apiResource('achievements', AchievementController::class);
    Route::apiResource('user-achievements', UserAchievementController::class)
        ->only(['index', 'store', 'destroy']);
});
