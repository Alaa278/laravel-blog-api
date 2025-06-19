<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class);
    Route::delete('/posts/slug/{slug}/images/{media}', [PostController::class, 'deleteImage']);

});
