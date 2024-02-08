<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AI\AIModels;
use App\Services\AI\Chat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::query()->where('user_id', auth()->id())->orderByDesc('updated_at')->get()->map(function ($conversation) {
            return [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updatedAtDiff' => $conversation->updated_at->diffForHumans(),
            ];
        });

        return Inertia::render('Conversations/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function create(Request $request)
    {
        $conversation = $request->user()->conversations()->create([
            'title' => 'Sans titre',
        ]);

        return redirect()->route('conversations.show', ['conversationId' => $conversation->id]);
    }

    public function show(int $conversationId)
    {
        $this->authorize('view', Conversation::findOrFail($conversationId));
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->load('messages');
        $models = AIModels::toArray();

        $selectedModel = session('selectedModel', AIModels::NeuralHermes);

        return Inertia::render('Conversations/Show', [
            'conversation' => $conversation,
            'models' => $models,
            'selectedModel' => $selectedModel->value,
        ]);
    }

    public function updateTitle(int $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $this->authorize('update', $conversation);

        $model = AIModels::Mistral;
        if ($conversation->token_count > AIModels::toArray()[AIModels::Mistral->value]['maxTokens']) {
            $model = AIModels::Mixtral;
        }

        try {
            $title = Chat::title($conversation, $model);
        } catch (\Throwable $th) {
            $title = $conversation->title;
        }

        $conversation->update([
            'title' => $title,
        ]);
    }

    public function send(int $conversationId, Request $request)
    {
        $this->authorize('update', Conversation::findOrFail($conversationId));

        $request->validate([
            'prompt' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        $conversation->messages()->create([
            'role' => 'user',
            'body' => $request->input('prompt'),
        ]);

        $conversation->touch();
    }

    public function answer(int $conversationId, Request $request)
    {
        $this->authorize('update', Conversation::findOrFail($conversationId));

        $conversation = Conversation::findOrFail($conversationId);

        try {
            Chat::create($conversation, session('selectedModel', AIModels::NeuralHermes));
        } catch (\Throwable $th) {
            $conversation->messages()->create([
                'role' => 'assistant',
                'body' => 'Une erreur est survenue lors de la génération de la réponse. Veuillez réessayer.',
            ]);
        }
    }

    public function delete(int $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        $this->authorize('delete', $conversation);

        $title = $conversation->title;

        $conversation->delete();

        session()->flash('flash.banner', "Conversation \"{$title}\" supprimée avec succès.");
    }

    public function updateUserSelectedModel(Request $request)
    {
        $request->validate([
            'model' => ['required', 'string', Rule::in(collect(AIModels::toArray())->map(fn ($model) => $model['value']->value)->toArray())],
        ]);

        $model = AIModels::tryFrom(request('model'));
        session(['selectedModel' => $model]);

        $modelName = AIModels::toArray()[$model->value]['name'];
        session()->flash('flash.banner', "Modèle de langage mis à jour avec succès. Nouveau modèle : {$modelName}.");
    }
}
