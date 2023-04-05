<?php

namespace App\Commands;

use App\Describer;
use App\DescriptionStorage;
use App\FileAnalyzer;
use App\OpenAITokenizer;
use Illuminate\Support\Facades\DB;

class GeneratePromptCommand extends ProjectCommand
{
    protected $signature = 'generate {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    protected $description = 'Generate AI-readable context prompt for a Laravel project';

    public function handle()
    {
        $remote = $this->option('remote');
        $targetDir = $remote ? $this->getProjectDirectory() : getcwd();

        $project = DB::table('projects')->where('path', $targetDir)->first();
        $projectId = $project ? $project->id : DB::table('projects')->insertGetId(['path' => $targetDir]);

        $fileAnalyzer = new FileAnalyzer($targetDir);
        $describer = new Describer();
        $descriptionStorage = new DescriptionStorage();

        $this->renderPanel('🚀 Laravel Project Prompt Generator');
        $this->line('');

        $this->renderMessage('📁 Project directory: ', false);
        $this->renderMessage("{$targetDir}", true);
        $this->line('');

        $filesToDescribe = $fileAnalyzer->getFilesToDescribe();

        $totalDescriptionLength = 0;
        $totalDescriptionsRetrieved = 0;

        foreach ($filesToDescribe as $file) {
            $this->renderMessage('📄 Analyzing file: ', false);
            $this->renderMessage("{$file}", true);
            $this->line('');

            $currentContents = file_get_contents($file);
            $currentContentHash = md5($currentContents);

            $start_time = microtime(true);

            $description = $describer->describe($file);

            $end_time = microtime(true);
            $response_time = round($end_time - $start_time, 2);
            $descriptionLength = OpenAITokenizer::count($description);
            $totalDescriptionLength += $descriptionLength;
            $totalDescriptionsRetrieved++;

            $descriptionStorage->saveOrUpdateDescription($projectId, $file, $description, $currentContentHash);

            $this->renderMessage("⏱ Response time: {$response_time} seconds");
            $this->renderMessage("📏 Token count: {$descriptionLength} characters");
            $this->renderMessage("💬 GPT result: {$description}");
            $this->line('');

            $this->line(str_repeat('-', 80));
            $this->line('');
        }

        $this->renderPanel('🎉 Laravel project prompt generation completed!');
        $this->line('');
        $this->info("Total amount of characters in all descriptions: {$totalDescriptionLength}");
        $this->info("Total amount of descriptions retrieved: {$totalDescriptionsRetrieved}");
    }

    private function renderMessage(string $message, bool $highlight = false): void
    {
        if ($highlight) {
            $output = "\033[38;5;47m\033[48;5;235m {$message} \033[0m";
        } else {
            $output = $message;
        }
        $this->line($output);
    }

    private function renderPanel(string $message): void
    {
        $wrappedMessage = wordwrap($message, 76, "\n");
        $border = "\033[38;5;47m\033[48;5;235m".str_repeat(' ', 80)."\033[0m";
        $line = "\033[38;5;47m\033[48;5;235m ".str_repeat(' ', 2).$wrappedMessage.str_repeat(' ', 2)." \033[0m";

        $this->line($border);
        $this->line($line);
        $this->line($border);
    }
}
