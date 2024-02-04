<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AI\AIModels;
use App\Services\AI\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class AITest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function testTitleGeneration(): void
    {
        $history = [
            [
                'role' => 'user',
                'content' => 'Hello!',
            ],
            [
                'role' => 'assistant',
                'content' => 'Hi! How can I help you?',
            ],
            [
                'role' => 'user',
                'content' => 'I need help with my account.',
            ],
        ];

        $user = User::factory()->create();
        $conversation = $user->conversations()->create([
            'title' => 'Sans titre',
        ]);

        foreach ($history as $message) {
            $conversation->messages()->create([
                'body' => $message['content'],
                'role' => $message['role'],
            ]);
        }

        $title = Chat::title($conversation, AIModels::Mixtral);

        $this->assertIsString($title);
    }
}
