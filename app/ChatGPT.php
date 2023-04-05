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

    public function send(string $message): self
    {
        $this->messages->push([
            'role' => 'user',
            'content' => $message,
        ]);

        // If the message itself is too large
        if (OpenAITokenizer::count($message) > config('openai.max_tokens')) {
            throw new \Exception('Message is too large');
        }

        $this->trimMessagesToFit();

        $message = $this->getChatResponse();

        $this->messages->push($message);

        return $this;
    }

    public function system(string $message): self
    {
        $this->messages->push([
            'role' => 'system',
            'content' => $message,
        ]);

        return $this;
    }

    public function reset(): self
    {
        $this->messages = collect();

        return $this;
    }

    private function trimMessagesToFit(): void
    {
        $totalTokens = OpenAITokenizer::count($this->messages->pluck('content')->implode(' '));

        while ($totalTokens > config('openai.max_tokens')) {
            echo '⚠️ Removing context from 1 message to abide by token limit ('.$this->messages->count().' left)'.PHP_EOL;
            // Remove the a message from the $this->messages collection and recalculate the total tokens
            $this->messages->shift();

            $totalTokens = OpenAITokenizer::count($this->messages);
        }
        echo PHP_EOL.'✅ Token limit is not exceeded with '.$this->messages->count().' messages left'.PHP_EOL.PHP_EOL;
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

    public function receive(): string
    {
        return $this->messages->last()['content'];
    }
}
