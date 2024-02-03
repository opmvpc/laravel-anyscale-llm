<?php

namespace App\Services\AI;

use App\Models\Thread;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use OpenAI\Client as OpenAIClient;

class Chat
{
    public static function create(Thread $thread, AIModels $model)
    {
        $date = date('Y-m-d');

        $client = self::client();

        $response = $client->chat()->create([
            'model' => $model->value,
            'messages' => [
                ['role' => 'system', 'content' => <<<EOT
                    # Helpful Assistant

                    ## Instructions
                    - You are a helpful assistant that is trying to help a user with a problem.
                    - Respond to the user's messages in a helpful and informative way.
                    - Answer in the language that the user is speaking to you in.
                    - Use emojis to express emotions and friendly behavior.

                    ## Output format
                    - Use Markdown to format your messages.

                    ## Information
                    Today's date is : {$date}
                    EOT,
                ],
                ...$thread->history(),
            ],
        ]);

        $thread->messages()->create([
            'role' => 'assistant',
            'body' => Str::markdown($response['choices'][0]['message']['content']),
        ]);
    }

    public static function title(Thread $thread, AIModels $model)
    {
        if (AIModels::Mixtral !== $model && AIModels::Mistral !== $model) {
            throw new \InvalidArgumentException('Model does not support json mode. Use Mixtral or Mistral instead.');
        }

        $client = self::client();
        $currentTitle = $thread->title;

        $messages = [
            ['role' => 'system', 'content' => <<<EOT
                # Generate a conversation title

                ## Instructions
                - You are a helpful assistant that needs to generate the best title for a conversation.
                - First, Guess the language used by the user.
                - Analyze the conversation and generate a title that best describes the conversation.
                - The title should be in the language used by the user.
                - Keep the current title if you cannot generate a better one.
                - Use the reasoning field to explain why you generated the title.
                - Your answer should be in the answer field.

                ## Information
                Current title: {$currentTitle}

                ## Output format
                - a json object

                ## Example
                ```json
                {
                    "reasoning": [
                        "The user is speaking in French.",
                        "The conversation is about a problem with the internet connection.",
                        "The assistant is trying to help the user by asking questions and providing solutions.",
                        "The current title is not descriptive enough.",
                        "I should generate a better title.",
                    ],
                    "answer": {
                        "language": "fr",
                        "title": "Problème de connexion à internet"
                    }
                }
                ```

                EOT,
            ],
            ...$thread->history(),
            [
                'role' => 'user',
                'content' => <<<'EOT'
                    Find the best title for this conversation. Respond with the provided output format.
                    EOT,
            ],
        ];

        $response = $client->chat()->create([
            'model' => $model->value,
            'temperature' => 0.7,
            'response_format' => [
                'type' => 'json_object',
                'schema' => <<<'EOT'
                    {
                        "type": "object",
                        "properties": {
                            "reasoning":
                            {
                                "type": "array",
                                "items": {
                                    "type": "string"
                                }
                            },
                            "answer":
                            {
                                "type": "object",
                                "properties": {
                                    "language":
                                    {
                                        "type": "string"
                                    },
                                    "title":
                                    {
                                        "type": "string"
                                    }
                                }
                            }
                        }
                    }
                    EOT,
            ],
            'messages' => $messages,
        ]);

        $json = $response['choices'][0]['message']['content'];
        dump($json);
        $data = json_decode($json, true);

        return $data['answer']['title'] ?? $currentTitle;
    }

    private static function client(): OpenAIClient
    {
        $yourApiKey = config('openai.api_key');
        $yourOrganization = config('openai.organization');
        $apiEndpoint = config('openai.api_endpoint');

        $client = \OpenAI::factory()
            ->withApiKey($yourApiKey)
            ->withOrganization($yourOrganization)
            ->withBaseUri($apiEndpoint)
            ->withHttpClient($client = new Client([]))
            ->make()
        ;

        return $client;
    }
}
