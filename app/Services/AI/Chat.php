<?php

namespace App\Services\AI;

use App\Models\Conversation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use OpenAI\Client as OpenAIClient;

class Chat
{
    public static function create(Conversation $conversation, AIModels $model)
    {
        $client = self::client($model);

        $response = $client->chat()->create([
            'model' => $model->value,
            'temperature' => 0.8,
            'messages' => [
                ['role' => 'system', 'content' => self::createSystemPrompt($conversation),
                ],
                ...$conversation->history(),
            ],
        ]);

        $answer = $response['choices'][0]['message']['content'];

        $conversation->messages()->create([
            'role' => 'assistant',
            'body' => Str::markdown($answer),
        ]);

        $conversation->update([
            'token_count' => $response['usage']['total_tokens'],
        ]);
    }

    public static function stream(Conversation $conversation, AIModels $model)
    {
        $client = self::client($model);

        return $client->chat()->createStreamed([
            'model' => $model->value,
            'temperature' => 0.8,
            'stream' => true,
            'messages' => [
                ['role' => 'system', 'content' => self::createSystemPrompt($conversation),
                ],
                ...$conversation->history(),
            ],
        ]);
    }

    public static function systemPrompTokenCount(Conversation $conversation): int
    {
        $systemPrompt = self::createSystemPrompt($conversation);

        return self::tokenCount($systemPrompt);
    }

    public static function title(Conversation $conversation, AIModels $model)
    {
        if (0 === $conversation->messages()->count()) {
            throw new \InvalidArgumentException('Cannot generate a title for an empty conversation.');
        }

        if (AIModels::Mixtral !== $model && AIModels::Mistral !== $model) {
            throw new \InvalidArgumentException('Model does not support json mode. Use Mixtral or Mistral instead.');
        }

        $currentTitle = $conversation->title;
        $userLanguage = app()->getLocale();

        $messages = [
            ['role' => 'system', 'content' => <<<EOT
                # Conversation title assistant

                ## Instructions
                - You are a helpful assistant tasked to generate the best title for a conversation.
                - Keep the current title if you cannot generate a better one.
                - Use the reasoning field to explain your thought process.
                - The reasoning field should contain 3 to 5 sentences.
                - Take the whole conversation into account when generating the title.
                - Conversation can have multiple subjects, but the title should be about the main subject.
                - Title should not contain the word "Conversation".
                - The title should be descriptive and summarize the conversation. It should be in the user's language. It should be brief and to the point.

                ## Information
                Current title: {$currentTitle}
                User Language: {$userLanguage}

                ## Output format
                - you MUST provide VALID JSON output.
                - Your final answer MUST be in the `title` field of the `answer` object.
                - NEVER use emojis or special characters.
                - The title SHOULD BE MAX 5 words long.

                ## Example
                - Here is an example of a valid response.
                - The example is generic and should be adapted to the current conversation history.
                - [LANG], [SUBJECT], [ACTION], [REASON], [TITLE] tags are placeholders.
                - Explain your reasoning by writing a few sentences. Replace the tags with the actual information. (e.g. French, a conversation about cats, summarize the conversation, the current title is not descriptive, generate a better title)
                - [TITLE] and should NEVER be used in the final answer.

                ```json
                {
                    "reasoning": [
                        "The provided language is [LANG]. The conversation is about [SUBJECT]",
                        "The assistant is trying to [ACTION] and to [ACTION].",
                        "The current title is not [REASON]. I should [ACTION] to generate a better title.",
                    ],
                    "answer": {
                        "title": "[TITLE]"
                    }
                }
                ```

                end of example
                EOT,
            ],
            ...$conversation->history(),
        ];

        $client = self::client($model);

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

        $tries = 0;
        $data = [];
        do {
            try {
                $json = $response['choices'][0]['message']['content'];
                $data = json_decode($json, true);
            } catch (\Throwable $th) {
                throw new \RuntimeException('Failed to parse the response from the AI model.');
            }
            ++$tries;
        } while ($tries < 5 && empty($data['answer']['title']));

        if (empty($data['answer']['title'])) {
            return $currentTitle;
        }

        return $data['answer']['title'];
    }

    public static function tokenCount(string $text): int
    {
        return \ceil(\mb_strlen($text) / 4);
    }

    private static function createSystemPrompt(Conversation $conversation): string
    {
        $date = Carbon::now('Europe/Brussels')->format('l, jS \of F Y. H:i:s');

        $userName = $conversation->user->name;
        $userLanguage = app()->getLocale();

        $systemPrompt = <<<'EOT'
                    # Helpful Assistant

                    ## Instructions
                    - You are a helpful assistant that is trying to help a user with a problem.
                    - Respond to the user's messages in a helpful and informative way.
                    - Answer in the user's language.
                    - Add emojis to express emotions and friendly behavior.
                    - Fullfill the user's requests and provide the best answer possible.
                    - Don't present yourself if the user didn't say hello first.
                    - Don't talk about yourself unless the user asks you to.
                    - Don't list your commands unless the user asks you to.


                    EOT;

        $userInstructions = $conversation->user->instruction;

        if ($userInstructions) {
            $personalUserInstructions = $userInstructions?->personal ?? 'No personal instructions provided.';
            $behaviorUserInstructions = $userInstructions?->behavior ?? 'No behavior instructions provided.';
            $commandsUserInstructions = $userInstructions?->commands ?? 'No commands provided.';

            $systemPrompt .= <<<EOT
                    # User's custom instructions
                    Here are custom instructions from the user. you must follow them to provide the best answer possible.
                    Commands are user-defined shortcuts. User will use these commands to instruct the assistant to perform specific tasks.
                    Sometimes, custom instructions or commands will go against the default instructions. In this case, you should follow the custom instructions. User is always right.

                    ## Personal information and preferences
                    {$personalUserInstructions}

                    ## Behavior and communication preferences
                    {$behaviorUserInstructions}

                    ## Commands
                    {$commandsUserInstructions}
                    EOT;
        }

        $systemPrompt .= <<<EOT
                    ## Output format
                    - Use Github flavored Markdown to format your messages. (Titles, Lists, Emphasis, Links, Tables, CodeBlocks ...)

                    ## Information
                    Today's date is : {$date}
                    User's name is : {$userName}
                    User Language : {$userLanguage}
                    EOT;

        return $systemPrompt;
    }

    private static function client(AIModels $model): OpenAIClient
    {
        if ('Anyscale' === AIModels::toArray()[$model->value]['provider']) {
            $yourApiKey = config('openai.api_key');
            $yourOrganization = config('openai.organization');
            $apiEndpoint = config('openai.api_endpoint');
        } if ('Groq' === AIModels::toArray()[$model->value]['provider']) {
            $yourApiKey = config('openai.groq_api_key');
            $yourOrganization = config('openai.groq_organization');
            $apiEndpoint = config('openai.groq_api_endpoint');
        }

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
