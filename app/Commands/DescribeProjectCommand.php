<?php

namespace App\Commands;

use App\DescriptionStorage;
use App\OpenAIDescriber;
use Illuminate\Console\Command;

class DescribeProjectCommand extends Command
{
    protected $signature = 'describe:project';

    protected $description = 'Generate a project description based on file descriptions';

    public function handle()
    {
        $projectPath = getcwd();
        $descriptionStorage = new DescriptionStorage();

        $fileDescriptions = $descriptionStorage->getFileDescriptions($projectPath);

        $descriptions = implode(' ', array_column($fileDescriptions, 'description'));

        $openAIDescriber = new OpenAIDescriber(config('openai.api_key'));

        $prompt = 'Describe this Laravel project based on the file descriptions: '.$descriptions;

        try {
            $response = $openAIDescriber->getProjectDescription($prompt);
        } catch (\Exception $e) {
            $this->error('Failed to generate project description: '.$e->getMessage());

            return;
        }

        $projectDescription = $response->choices[0]->message->content;

        $this->info('Project Description:');
        $this->info($projectDescription);
    }
}
