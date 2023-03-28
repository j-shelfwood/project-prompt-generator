<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use OpenAI;
use OpenAI\Client;

class ChatGPT
{
    protected Client $client;

    protected Collection $messages;

    public function __construct()
    {
        $this->client = OpenAI::client(config('openai.api_key'));
        $this->messages = collect();
    }

    public function messages(): Collection
    {
        return $this->messages;
    }

    public function chat(string $message): self
    {
        $this->messages->push([
            'role' => 'user',
            'content' => $message,
        ]);

        $tokenCount = OpenAITokenizer::count($message);

        if ($tokenCount > 4000) {
            $message = OpenAITokenizer::truncate($message, 4000);
        }

        $message = $this->getChatResponse();

        $this->messages->push($message);

        return $this;
    }

    public function reset(): self
    {
        $this->messages = collect();

        return $this;
    }

    private function getChatResponse(): array
    {
        return Cache::rememberForever(md5($this->messages->implode('content')), function () {
            $response = $this->client->chat()->create([
                'model' => config('openai.model_used'),
                'messages' => [$this->messages->last()],
            ]);

            return [
                'role' => $response->choices[0]->message->role,
                'content' => $response->choices[0]->message->content,
            ];
        });
    }
}
