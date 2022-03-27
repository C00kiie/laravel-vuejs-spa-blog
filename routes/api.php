<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;



Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

   // posts

Route::group(['middleware' => ['jwt.verify']], function() {
    // user
    Route::get('logout', [UserController::class, 'logout']);
    Route::get('get_user', [UserController::class, 'get_user']);

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/create', [PostController::class, 'store']);
        Route::delete('/delete/{post_id}', [PostController::class, 'destroy']);
        Route::put('/update/{post_id}', [PostController::class, 'update']);
        Route::get('/me',[PostController::class, 'me']);
    });

    Route::prefix('comments')->group(function(){
        route::get('/{post_id}', [CommentController::class, 'index']);
        route::post('/{post_id}/create', [CommentController::class, 'store']);
        route::delete('/{comment_id}', [CommentController::class, 'destroy']);
        route::post('/{post_id}/update/{comment_id}', [CommentController::class, 'update']);
    });

});
