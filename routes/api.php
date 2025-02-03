<?php

use App\Helpers\TextHelper;
use App\Helpers\ArrayHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReplyController;

// Route::options('{any}', function () {
//     return response('', 200)
//         ->header('Access-Control-Allow-Origin', '*')
//         ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
//         ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
// })->where('any', '.*');

// Route::options('{any}', function () {
//     return response()->json('OK', 200, [
//         'Access-Control-Allow-Origin' => 'http://localhost:5173', // Change to your frontend URL
//         'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
//         'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
//         'Access-Control-Allow-Credentials' => 'true',
//     ]);
// })->where('any', '.*');

Route::options('{any}', function (Request $request) {
    return Response::make('', 200, [
        'Access-Control-Allow-Origin' => $request->header('Origin') ?? '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept',
        'Access-Control-Allow-Credentials' => 'true',
    ]);
})->where('any', '.*');



Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/abilities', 'getAbilities');
    Route::post('/auth/login', 'login')->middleware('throttle:5,1');
    Route::post('/auth/register', 'register')->middleware('throttle:5,1');
    Route::post('/auth/logout', 'logout')->middleware('auth:sanctum');
});


Route::controller(PostController::class)->group(function () {
    Route::get('/posts', 'getPosts');
    Route::post('/posts', 'store')->middleware('auth:sanctum');
    Route::get('/posts/{post}', 'getPost');
    Route::get('/posts/{post}/comments', 'getPostComments');
    Route::put('/posts/{post}', 'update');
    Route::delete('/posts/{post}', 'delete');
});

Route::controller(MediaController::class)->group(function () {
    Route::get('/media', 'getMedia');
});

Route::controller(CommentController::class)->group(function () {
    Route::post('/comments', 'store')->middleware('auth:sanctum');
    Route::delete('/comments/{comment}', 'delete')->middleware('auth:sanctum');
});

Route::controller(ReplyController::class)->group(function() {
    Route::post('/replies','store')->middleware('auth:sanctum');
});

Route::controller(DraftController::class)->group(function () {
    Route::post('/drafts', 'store')->middleware('auth:sanctum');
    Route::put('/drafts/{draft}', 'update')->middleware('auth:sanctum');
    Route::delete('/drafts/{draft}', 'delete')->middleware('auth:sanctum');
});

Route::prefix('/users')->controller(UserController::class)->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', 'getCurrentUser');
        Route::get('/me/posts', 'getCurrentUserPosts');
        Route::get('/me/comments', 'getCurrentUserComments');
        Route::get('/me/drafts', 'getCurrentUserDrafts');
    });

    Route::get('/', 'getUsers');
    Route::get('/{user}', 'getUser');
    Route::get('/{user}/posts', 'getUserPosts');
    Route::get('/{user}/comments', 'getUserComments');
    Route::get('/{user}/drafts', 'getUserDrafts');

    Route::put('/{user}', 'update')->middleware('auth:sanctum');
    Route::delete('/{user}', 'delete')->middleware('auth:sanctum');
});


Route::prefix('/notifications')->controller(NotificationController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'getUnreadNotifications');
    Route::get('/all', 'getAllNotifications');
    Route::post('/{id}/mark-as-read', 'markAsRead');
    Route::post('/mark-all-as-read', 'markAllAsRead');
});
