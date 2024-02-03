<?php

use App\Http\Controllers\ConversationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/threads', [ConversationController::class, 'index'])->name('threads.index');

    Route::post('/threads/create', [ConversationController::class, 'createThread'])->name('threads.create');
    Route::post('/threads/update-title/{threadId}', [ConversationController::class, 'updateThreadTitle'])->name('threads.updateTitle');
    Route::get('/threads/{threadId}', [ConversationController::class, 'showThread'])->name('threads.show');
    Route::post('/threads/answer/{threadId}', [ConversationController::class, 'answerThread'])->name('threads.answer');

    Route::post('/messages/send/{threadId}', [ConversationController::class, 'sendMessage'])->name('messages.send');

    Route::post('/models/select', [ConversationController::class, 'updateUserSelectedModel'])->name('models.select');
});
