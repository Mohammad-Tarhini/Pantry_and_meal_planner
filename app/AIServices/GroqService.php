<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
    }

    public function ask($message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ]
        ]);

        return $response->json();
    }
}
