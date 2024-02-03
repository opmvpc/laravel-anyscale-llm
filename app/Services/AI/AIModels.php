<?php

namespace App\Services\AI;

enum AIModels: string
{
    case NeuralHermes = 'mlabonne/NeuralHermes-2.5-Mistral-7B';
    case Mistral = 'mistralai/Mistral-7B-Instruct-v0.1';
    case Mixtral = 'mistralai/Mixtral-8x7B-Instruct-v0.1';
    case Llama = 'meta-llama/Llama-2-70b-chat-hf';
}
