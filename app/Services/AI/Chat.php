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
        $userName = $thread->user->name;
        $userLanguage = app()->getLocale();

        $systemPrompt = <<<EOT
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
                    User's name is : {$userName}
                    User Language : {$userLanguage}
                    EOT;

        $userInstructions = $thread->user->instruction;

        if ($userInstructions) {
            $personalUserInstructions = $userInstructions?->personal ?? 'No personal instructions provided.';
            $behaviorUserInstructions = $userInstructions?->behavior ?? 'No behavior instructions provided.';

            $systemPrompt .= <<<EOT
                    ## User's instructions:
                    Here are custom instructions from the user. YOU MUST FOLLOW THEM to provide the best answer possible.
                    ### Personal
                    {$personalUserInstructions}
                    ### Behavior
                    {$behaviorUserInstructions}
                    EOT;
        }

        $client = self::client();

        $response = $client->chat()->create([
            'model' => $model->value,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt,
                ],
                ...$thread->history(),
            ],
        ]);

        $thread->messages()->create([
            'role' => 'assistant',
            'body' => Str::markdown($response['choices'][0]['message']['content']),
        ]);

        $thread->update([
            'token_count' => $response['usage']['total_tokens'],
        ]);
    }

    public static function title(Thread $thread, AIModels $model)
    {
        if (AIModels::Mixtral !== $model && AIModels::Mistral !== $model) {
            throw new \InvalidArgumentException('Model does not support json mode. Use Mixtral or Mistral instead.');
        }

        $currentTitle = $thread->title;
        $userLanguage = app()->getLocale();

        $messages = [
            ['role' => 'system', 'content' => <<<EOT
                # Conversation title assistant

                ## Instructions
                - You are a helpful assistant tasked to generate the best title for a conversation.
                - Analyze the conversation and generate a title that best describes the conversation.
                - The title should be written in the language provided in the Information section.
                - Keep the current title if you cannot generate a better one.
                - Use the reasoning field to explain why you generated the title.
                - Your answer should be in the answer field.

                ## Information
                Current title: {$currentTitle}
                User Language: {$userLanguage}

                ## Output format
                - a json object

                ## Example
                Here is an example of a valid response. The example is generic and should be adapted to the current conversation. To do so, you should replace the placeholder values (eg: [LANG], [SUBJECT], [ACTION], [REASON], [NEW TITLE]) with the correct values to help you reason step by step before generating the title.

                ```json
                {
                    "reasoning": [
                        "The provided language is [LANG].",
                        "The conversation is about [SUBJECT].",
                        "The assistant is trying to [ACTION].",
                        "But the assistant is not able to [ACTION].",
                        "The current title is not [REASON].",
                        "I should [ACTION] to generate a better title.",
                        "[NEW TITLE] will be a better match!"
                    ],
                    "answer": {
                        "language": "[LANG]",
                        "title": "[NEW TITLE]"
                    }
                }
                ```

                EOT,
            ],
            ...$thread->history(),
        ];

        $client = self::client();

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
