<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Services\AI\AIModels;
use App\Services\AI\Chat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function index()
    {
        // sort threads by last message
        $threads = Thread::where('user_id', auth()->id())->with('messages')->get()->sortByDesc(function ($thread) {
            return $thread->messages->last()->created_at;
        })->values();

        return Inertia::render('Conversations/Index', [
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
        $models = AIModels::toArray();

        $selectedModel = session('selectedModel', AIModels::NeuralHermes);

        return Inertia::render('Conversations/Show', [
            'thread' => $thread,
            'models' => $models,
            'selectedModel' => $selectedModel->value,
        ]);
    }

    public function updateThreadTitle(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $title = Chat::title($thread, AIModels::Mixtral);

        $thread->update([
            'title' => $title,
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
        $request->validate([
            'model' => ['required', 'string', 'in:'.implode(',', collect(AIModels::toArray())->map(fn ($model) => $model['value']->value)->toArray())],
        ]);

        $thread = Thread::findOrFail($threadId);

        Chat::create($thread, AIModels::NeuralHermes);
    }

    public function deleteThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $thread->delete();

        return redirect()->route('dashboard');
    }

    public function updateUserSelectedModel(Request $request)
    {
        $request->validate([
            'model' => ['required', 'string', 'in:'.implode(',', collect(AIModels::toArray())->map(fn ($model) => $model['value']->value)->toArray())],
        ]);

        session(['selectedModel' => AIModels::tryFrom(request('model'))]);
        session()->flash('flash.banner', 'Modèle sélectionné mis à jour avec succès.');
    }
}
