<?php

namespace App\Commands;

use App\DescriptionStorage;
use App\FileAnalyzer;
use App\Describer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Termwind\render;
use function Termwind\style;

class GeneratePromptCommand extends Command
{
    protected $signature = 'generate';

    protected $description = 'Generate AI-readable context prompt for a Laravel project';

    public function handle()
    {
        $projectDirectory = getcwd();
        $project = DB::table('projects')->where('path', $projectDirectory)->first();

        if (! $project) {
            $projectId = DB::table('projects')->insertGetId(['path' => $projectDirectory]);
        } else {
            $projectId = $project->id;
        }

        $fileAnalyzer = new FileAnalyzer($projectDirectory);
        $openAIDescriber = new Describer(config('openai.api_key'));
        $descriptionStorage = new DescriptionStorage();

        style('panel')->apply('py-0.5');
        render('<div class="panel"><b>🚀 Laravel Project Prompt Generator</b></div>');

        $filesToDescribe = $fileAnalyzer->getFilesToDescribe();

        $totalDescriptionLength = 0;
        $totalDescriptionsRetrieved = 0;

        foreach ($filesToDescribe as $file) {
            $currentContents = file_get_contents($file);
            $currentContentHash = md5($currentContents);
            $fileContentHash = $descriptionStorage->getFileContentHash($file);

            if ($fileContentHash === $currentContentHash) {
                continue;
            }

            $start_time = microtime(true);

            $description = $openAIDescriber->describeFile($file, $currentContents);

            $end_time = microtime(true);
            $response_time = round($end_time - $start_time, 2);
            $descriptionLength = strlen($description);
            $totalDescriptionLength += $descriptionLength;
            $totalDescriptionsRetrieved++;

            $descriptionStorage->saveOrUpdateDescription($projectId, $file, $description, $currentContentHash);

            render("
                <div class=\"panel\">
                    <b>📄 {$file}</b>
                    <br>
                    <div>⏱ Response time: </div><br>
                    <i> {$response_time} seconds</i>
                    <br>
                    <div>📏 Description length: </div><br>
                    <i>{$descriptionLength} characters</i>
                    <br>
                    <div>💬 GPT result</div><br>
                    <i>{$description}</i>
                </div>
            ");
            $this->line('');
        }

        $this->info('🎉 Laravel project prompt generation completed!');
        $this->info("Total amount of characters in all descriptions: {$totalDescriptionLength}");
        $this->info("Total amount of descriptions retrieved: {$totalDescriptionsRetrieved}");
    }
}
