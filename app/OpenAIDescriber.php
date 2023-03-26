<?php

namespace App;

use OpenAI;

class OpenAIDescriber
{
    protected $client;

    public function __construct($apiKey)
    {
        $this->client = OpenAI::client($apiKey);
    }

    public function describeFile($file, $fileContents)
    {
        // Prepare the prompt for the chat
        $prompt = '[INSTRUCTIONS]';
        $prompt .= "Create a concise description of a Laravel project's file, focusing on the names and types of relationships, design patterns used, unique or custom methods relevant to the class's functionality, details about the class's inheritance or implemented interfaces, and any dependencies or external packages that the class relies on. The response should be brief and strictly adhere to the provided format: file_path|namespace|classname|extends:parent_class|uses:trait1,trait2,external_class1|property1:type1|property2:type2|method1:returntype1(arg1:type1,arg2:type2)|method2:returntype2(arg1:type1)|notes:[note1;note2;...]|...";
        $prompt .= '[CONTEXT]';
        $prompt .= $file."\n";
        $prompt .= $fileContents;

        // Describe the file using OpenAI
        try {
            $response = $this->client->chat()->create([
                'model' => config('openai.model_used'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to describe file: {$file}, {$e->getMessage()}");
        }

        $description = $response->choices[0]->message->content;

        return $description;
    }
}
