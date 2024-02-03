<?php

use App\Http\Controllers\HomeController;
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
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::post('/threads/create', [HomeController::class, 'createThread'])->name('threads.create');
    Route::get('/threads/{threadId}', [HomeController::class, 'showThread'])->name('threads.show');
    Route::post('/threads/answer/{threadId}', [HomeController::class, 'answerThread'])->name('threads.answer');

    Route::post('/messages/send/{threadId}', [HomeController::class, 'sendMessage'])->name('messages.send');
});
