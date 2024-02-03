<?php

namespace App\Services\AI;

use App\Models\Thread;
use GuzzleHttp\Client;
use OpenAI;

class Chat
{
    public static function create(Thread $thread, AIModels $model)
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

        $date = date('Y-m-d');

        $response = $client->chat()->create([
            'model' => $model->value,
            'messages' => [
                ['role' => 'system', 'content' => <<<EOT
You are a helpful assistant that is trying to help a user with a problem.
Today's date is : {$date}
EOT
                ],
                ...$thread->history(),
            ],
        ]);

        $thread->messages()->create([
            'role' => 'assistant',
            'body' => $response['choices'][0]['message']['content'],
        ]);
    }
}
