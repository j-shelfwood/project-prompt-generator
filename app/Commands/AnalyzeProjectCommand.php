<?php

namespace App\Commands;

use App\DescriptionStorage;
use App\Scan\FileAnalyzer;
use App\Helpers\OpenAITokenizer;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class AnalyzeProjectCommand extends ProjectCommand
{
    protected $signature = 'analyze {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    protected $description = 'Analyze the current project and show how many tokens each file contains; the character count of each file; and the total number of tokens and characters in the project.';

    protected $descriptionStorage;

    public function __construct(DescriptionStorage $descriptionStorage)
    {
        parent::__construct();

        $this->descriptionStorage = $descriptionStorage;
    }

    public function handle()
    {
        $remote = $this->option('remote');
        $targetDir = $remote ? $this->getProjectDirectory() : getcwd();

        $files = (new FileAnalyzer($targetDir))
            ->scan();

        $fileInfo = [];
        $bar = $this->output->createProgressBar(count($files));

        $fileDescriptions = $this->descriptionStorage->getFileDescriptions($targetDir);

        $concatenatedContent = '';

        foreach ($files as $file) {
            $result = $this->processFile($file, $fileDescriptions);
            $concatenatedContent .= $result['content'];
            $fileInfo[] = $result;
            $bar->advance();
        }

        $totalTokenCount = OpenAITokenizer::count($concatenatedContent);
        // Get the total token count for descriptions
        $totalDescriptionTokenCount = 0;
        foreach ($fileDescriptions as $description) {
            $totalDescriptionTokenCount += OpenAITokenizer::count($description->description);
        }

        $bar->finish();
        $this->newLine();

        $files = collect($fileInfo);

        $this->table(
            ['File', 'Raw token count', 'Description token Count', 'Character count'],
            $files->sortByDesc('token_count')->map(function ($file) {
                $shortPath = str_replace($this->getProjectDirectory(), '', $file['path']);

                return [
                    $shortPath,
                    $file['token_count'],
                    $file['description_token_count'],
                    $file['character_count'],
                ];
            })
        );

        $this->info("Total token count: {$totalTokenCount}");
        $this->info("Total description token count: {$totalDescriptionTokenCount}");
    }

    protected function processFile(string $file, array $fileDescriptions): array
    {
        $content = file_get_contents($file);
        $content = preg_replace('/\s+/', '', $content);
        $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);
        $content = preg_replace('/^<\?php/', '', $content);

        $descriptionTokenCount = $this->getDescriptionTokenCount($file, $fileDescriptions);

        return [
            'path' => $file,
            'content' => $content,
            'description_token_count' => $descriptionTokenCount,
            'character_count' => strlen($content),
            'token_count' => OpenAITokenizer::count($content),
        ];
    }

    protected function getDescriptionTokenCount(string $file, array $fileDescriptions): int
    {
        $descriptionTokenCount = 0;

        foreach ($fileDescriptions as $description) {
            if ($description->path == $file) {
                $descriptionTokenCount = OpenAITokenizer::count($description->description);
                break;
            }
        }

        return $descriptionTokenCount;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        //
    }
}
