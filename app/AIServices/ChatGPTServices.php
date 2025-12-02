<?php
namespace App\Services;
use OpenAI;

class ChatGPTService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    public function askChatGPT(string $message)
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
        ]);

        return $response['choices'][0]['message']['content'];
    }
}

?>