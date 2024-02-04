<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Services\AI\AIModels;
use App\Services\AI\Chat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function index()
    {
        $threads = Thread::query()->where('user_id', auth()->id())->orderByDesc('updated_at')->get()->map(function ($thread) {
            return [
                'id' => $thread->id,
                'title' => $thread->title,
                'updatedAtDiff' => $thread->updated_at->diffForHumans(),
            ];
        });

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

        $title = Chat::title($thread, AIModels::Mistral);

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

        $thread->touch();
    }

    public function answerThread(int $threadId, Request $request)
    {
        $request->validate([
            'model' => ['required', 'string', Rule::in(collect(AIModels::toArray())->map(fn ($model) => $model['value']->value)->toArray())],
        ]);

        $thread = Thread::findOrFail($threadId);

        Chat::create($thread, AIModels::NeuralHermes);
    }

    public function deleteThread(int $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $thread->delete();

        session()->flash('flash.banner', 'Conversation supprimée avec succès.');
    }

    public function updateUserSelectedModel(Request $request)
    {
        $request->validate([
            'model' => ['required', 'string', Rule::in(collect(AIModels::toArray())->map(fn ($model) => $model['value']->value)->toArray())],
        ]);

        session(['selectedModel' => AIModels::tryFrom(request('model'))]);
        session()->flash('flash.banner', 'Modèle sélectionné mis à jour avec succès.');
    }
}
