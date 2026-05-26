<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Refresh must be outside auth:api so it can accept expired tokens
Route::post('refresh', [AuthController::class, 'refresh']);

Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('posts', [PostController::class, 'index']);
    Route::post('posts', [PostController::class, 'store']);
    Route::get('posts/{post}', [PostController::class, 'show']);
    Route::delete('posts/{post}', [PostController::class, 'destroy']);

    Route::get('users/{user}/posts', [PostController::class, 'userPosts']);

    Route::get('posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('posts/{post}/reactions', [ReactionController::class, 'togglePost']);
    Route::get('posts/{post}/reactions', [ReactionController::class, 'postReactions']);

    Route::post('comments/{comment}/reactions', [ReactionController::class, 'toggleComment']);
    Route::get('comments/{comment}/reactions', [ReactionController::class, 'commentReactions']);
});
