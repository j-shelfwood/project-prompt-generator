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

        $this->trimMessagesToFit();

        $message = $this->getChatResponse();

        $this->messages->push($message);

        return $this;
    }

    public function reset(): self
    {
        $this->messages = collect();

        return $this;
    }

    private function trimMessagesToFit(): void
    {
        $maxTokens = config('openai.max_tokens');
        $totalTokens = OpenAITokenizer::count($this->messages);

        while ($totalTokens > $maxTokens) {
            $this->messages->shift();
            $totalTokens = OpenAITokenizer::count($this->messages);
        }
    }

    private function getChatResponse(): array
    {
        return Cache::rememberForever(md5($this->messages->implode('content')), function () {
            $response = $this->client->chat()->create([
                'model' => config('openai.model_used'),
                'messages' => $this->messages->toArray(),
            ]);

            return [
                'role' => $response->choices[0]->message->role,
                'content' => $response->choices[0]->message->content,
            ];
        });
    }
}
