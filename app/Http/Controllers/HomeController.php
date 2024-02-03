<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Services\AI\AIModels;
use App\Services\AI\Chat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $threads = Thread::where('user_id', auth()->id())->get();

        return Inertia::render('Home', [
            'threads' => $threads,
        ]);
    }

    public function createThread(Request $request)
    {
        $thread = $request->user()->threads()->create([
            'title' => 'Sans titre',
        ]);

        return redirect()->route('threads.show', ['threadId' => $thread->id]);
    }

    public function showThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);
        $thread->load('messages');

        return Inertia::render('Thread', [
            'thread' => $thread,
        ]);
    }

    public function updateThread(int $threadId, Request $request)
    {
        $thread = Thread::findOrFail($threadId);

        $thread->update([
            'title' => $request->input('title'),
        ]);
    }

    public function sendMessage(int $threadId, Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        $thread = Thread::findOrFail($threadId);

        $thread->messages()->create([
            'role' => 'user',
            'body' => $request->input('prompt'),
        ]);
    }

    public function answerThread(int $threadId, Request $request)
    {
        $thread = Thread::findOrFail($threadId);

        Chat::create($thread, AIModels::NeuralHermes);
    }

    public function deleteThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $thread->delete();

        return redirect()->route('dashboard');
    }
}
