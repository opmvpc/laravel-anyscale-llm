<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\InstructionController;
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
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');

    Route::get('/conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('/conversations/update-title/{conversationId}', [ConversationController::class, 'updateTitle'])->name('conversations.updateTitle');
    Route::get('/conversations/{conversationId}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations/answer/{conversationId}', [ConversationController::class, 'answer'])->name('conversations.answer');
    Route::delete('/conversations/{conversationId}', [ConversationController::class, 'delete'])->name('conversations.delete');

    Route::post('/messages/send/{conversationId}', [ConversationController::class, 'send'])->name('messages.send');

    Route::post('/models/select', [ConversationController::class, 'updateUserSelectedModel'])->name('models.select');

    Route::get('/instructions', [InstructionController::class, 'index'])->name('instructions.index');
    Route::post('/instructions', [InstructionController::class, 'update'])->name('instructions.update');
});
