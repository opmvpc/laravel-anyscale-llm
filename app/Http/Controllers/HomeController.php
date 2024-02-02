<?php

namespace App\Http\Controllers;

use App\Models\Thread;
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
            'title' => "Sans titre",
        ]);

        return redirect()->route('threads.show', ['threadId' => $thread->id]);
    }

    public function showThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

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

        return back();
    }

    public function sendMessage(int $threadId, Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $thread = Thread::findOrFail($threadId);

        $thread->messages()->create([
            'role' => 'user',
            'body' => $request->input('message'),
        ]);

        return back();
    }

    public function deleteThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $thread->delete();

        return redirect()->route('dashboard');
    }

    // delete all messages from a user
    public function deleteMessages(int $userId)
    {
        $threads = Thread::where('user_id', $userId)->get();

        foreach ($threads as $thread) {
            $thread->messages()->delete();
        }

        return redirect()->route('dashboard');
    }
}
