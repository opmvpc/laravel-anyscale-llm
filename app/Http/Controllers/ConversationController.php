<?php

namespace App\Http\Controllers;

use App\Events\NewConversationTitle;
use App\Events\NewMessage;
use App\Events\StopMessage;
use App\Events\StreamText;
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
            'systemPromptTokenCount' => Chat::systemPrompTokenCount($conversation),
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

        // if title starts or ends with ", remove them
        $title = \preg_replace('/^"/', '', $title);
        $title = \preg_replace('/"$/', '', $title);

        $conversation->update([
            'title' => $title,
        ]);

        NewConversationTitle::dispatch($conversation, $title);
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

    public function answer(int $conversationId)
    {
        $this->authorize('update', Conversation::findOrFail($conversationId));

        $conversation = Conversation::findOrFail($conversationId);

        $userMessageTokenCount = Chat::tokenCount($conversation->messages()->latest()->first()->body);

        NewMessage::dispatch($conversation, $userMessageTokenCount);

        $stream = Chat::stream($conversation, session('selectedModel', AIModels::NeuralHermes));

        $buffer = '';
        $finalBuffer = '';
        $lastSendTime = microtime(true); // Enregistrer le temps actuel

        foreach ($stream as $response) {
            if (isset($response->choices[0]->toArray()['delta']['finish_reason'])) {
                if (!empty($buffer)) {
                    StreamText::dispatch($conversation, $buffer); // Envoyer le reste du buffer
                    $finalBuffer .= $buffer;
                }

                break;
            }

            if (isset($response->choices[0]->toArray()['delta']['content'])) {
                $text = $response->choices[0]->toArray()['delta']['content'];
                $buffer .= $text;
                $finalBuffer .= $text;
            }

            // Générer un intervalle aléatoire entre 15ms et 50ms
            $randomInterval = rand(15000, 50000); // us (microsecondes)

            // Vérifier si l'intervalle aléatoire s'est écoulé
            if ((microtime(true) - $lastSendTime) >= ($randomInterval / 1000000.0)) {
                StreamText::dispatch($conversation, $buffer);
                $buffer = ''; // Réinitialiser le buffer
                $lastSendTime = microtime(true); // Réinitialiser le temps du dernier envoi
            }
        }

        $tokenCount = Chat::tokenCount($finalBuffer);

        StopMessage::dispatch($conversation, $tokenCount);

        $conversation->messages()->create([
            'role' => 'assistant',
            'body' => $finalBuffer,
        ]);

        $conversation->update([
            'token_count' => $conversation->token_count + $tokenCount,
        ]);
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
