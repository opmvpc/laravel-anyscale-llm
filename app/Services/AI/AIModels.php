<?php

namespace App\Services\AI;

/**
 * AI Models.
 *
 * @see https://docs.endpoints.anyscale.com/category/guides
 * @see https://docs.endpoints.anyscale.com/category/supported-models
 */
enum AIModels: string
{
    case NeuralHermes = 'mlabonne/NeuralHermes-2.5-Mistral-7B';
    case OpenOrca = 'Open-Orca/Mistral-7B-OpenOrca';
    case Zephyr = 'HuggingFaceH4/zephyr-7b-beta';
    case Mistral = 'mistralai/Mistral-7B-Instruct-v0.1';
    case Mixtral = 'mistralai/Mixtral-8x7B-Instruct-v0.1';
    case Llama7B = 'meta-llama/Llama-2-7b-chat-hf';
    case Llama13B = 'meta-llama/Llama-2-13b-chat-hf';
    case Llama70B = 'meta-llama/Llama-2-70b-chat-hf';
    case CodeLlama34B = 'codellama/CodeLlama-34b-Instruct-hf';
    case CodeLlama70B = 'codellama/CodeLlama-70b-Instruct-hf';
    case GroqLlama70B = 'llama2-70b-4096';
    case GroqMixtral = 'mixtral-8x7b-32768';
    case GroqGemma = 'gemma-7b-it';

    public static function default(): self
    {
        return self::GroqMixtral;
    }

    public static function toArray(): array
    {
        return [
            self::NeuralHermes->value => [
                'name' => 'NeuralHermes 7B 16k',
                'value' => self::NeuralHermes,
                'maxTokens' => 16384,
                'provider' => 'Anyscale',
            ],
            self::OpenOrca->value => [
                'name' => 'OpenOrca 7B 8k',
                'value' => self::OpenOrca,
                'maxTokens' => 8192,
                'provider' => 'Anyscale',
            ],
            self::Zephyr->value => [
                'name' => 'Zephyr 7B 16k',
                'value' => self::Zephyr,
                'maxTokens' => 16384,
                'provider' => 'Anyscale',
            ],
            self::Mistral->value => [
                'name' => 'Mistral 7B 16k',
                'value' => self::Mistral,
                'maxTokens' => 16384,
                'provider' => 'Anyscale',
            ],
            self::Mixtral->value => [
                'name' => 'Mixtral 8x7B 32k',
                'value' => self::Mixtral,
                'maxTokens' => 32768,
                'provider' => 'Anyscale',
            ],
            self::Llama7B->value => [
                'name' => 'Llama2 7B 4k',
                'value' => self::Llama7B,
                'maxTokens' => 4096,
                'provider' => 'Anyscale',
            ],
            self::Llama13B->value => [
                'name' => 'Llama2 13B 4k',
                'value' => self::Llama13B,
                'maxTokens' => 4096,
                'provider' => 'Anyscale',
            ],
            self::Llama70B->value => [
                'name' => 'Llama2 70B 4k',
                'value' => self::Llama70B,
                'maxTokens' => 4096,
                'provider' => 'Anyscale',
            ],
            self::CodeLlama34B->value => [
                'name' => 'CodeLlama 34B 16k',
                'value' => self::CodeLlama34B,
                'maxTokens' => 16384,
                'provider' => 'Anyscale',
            ],
            self::CodeLlama70B->value => [
                'name' => 'CodeLlama 70B 4k',
                'value' => self::CodeLlama70B,
                'maxTokens' => 4096,
                'provider' => 'Anyscale',
            ],
            self::GroqLlama70B->value => [
                'name' => 'Groq Llama2 70B 4k',
                'value' => self::GroqLlama70B,
                'maxTokens' => 4096,
                'provider' => 'Groq',
            ],
            self::GroqMixtral->value => [
                'name' => 'Groq Mixtral 8x7B 32k',
                'value' => self::GroqMixtral,
                'maxTokens' => 32768,
                'provider' => 'Groq',
            ],
            self::GroqGemma->value => [
                'name' => 'Groq Gemma 7B 8k',
                'value' => self::GroqGemma,
                'maxTokens' => 8192,
                'provider' => 'Groq',
            ],
        ];
    }
}
