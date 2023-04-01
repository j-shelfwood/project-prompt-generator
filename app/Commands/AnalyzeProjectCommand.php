<?php

namespace App\Commands;

use App\DescriptionStorage;
use App\FileAnalyzer;
use App\OpenAITokenizer;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class AnalyzeProjectCommand extends Command
{
    protected $signature = 'analyze';

    protected $description = 'Analyze the current project and show how many tokens each file contains; the character count of each file; and the total number of tokens and characters in the project. It also shows the most repeated tokens in the project.';

    protected $descriptionStorage;

    public function __construct(DescriptionStorage $descriptionStorage)
    {
        parent::__construct();

        $this->descriptionStorage = $descriptionStorage;
    }

    public function handle()
    {
        $files = (new FileAnalyzer(getcwd()))
            ->getFilesToDescribe();

        $tokenCounts = [];
        $fileInfo = [];
        $bar = $this->output->createProgressBar(count($files));

        $fileDescriptions = $this->descriptionStorage->getFileDescriptions(getcwd());

        foreach ($files as $file) {
            $result = $this->processFile($file, $tokenCounts, $fileDescriptions);
            $fileInfo[] = $result;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $files = collect($fileInfo);
        $totalTokenCount = $files->sum('token_count');
        $totalCharacterCount = $files->sum('character_count');
        $totalDescriptionTokenCount = $files->sum('description_token_count');

        $this->table(
            ['File', 'Raw token count', 'Description token Count', 'Character count', 'Most repeated tokens'],
            $files->sortByDesc('token_count')->map(function ($file) {
                $shortPath = implode('/', array_slice(explode('/', $file['path']), -2));

                return [
                    $shortPath,
                    $file['token_count'],
                    $file['description_token_count'],
                    $file['character_count'],
                    $file['most_repeated_tokens'],
                ];
            }));

        $this->info("Total token count: {$totalTokenCount}");
        $this->info("Total description token count: {$totalDescriptionTokenCount}");
        $this->info("Total character count: {$totalCharacterCount}");
    }

    protected function processFile(string $file, array &$tokenCounts, array $fileDescriptions): array
    {
        $content = file_get_contents($file);
        $content = preg_replace('/\s+/', '', $content);
        $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);
        $content = preg_replace('/^<\?php/', '', $content);

        $encodedTokens = OpenAITokenizer::encode($content);

        foreach ($encodedTokens as $token => $id) {
            $tokenCounts[$id] = ($tokenCounts[$id] ?? 0) + 1;
        }

        $mostRepeatedTokens = collect($tokenCounts)
            ->map(function ($count, $id) use ($encodedTokens) {
                return [
                    'id' => $id,
                    'count' => $count,
                    'token' => array_search($id, $encodedTokens),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->map(function ($item) {
                return "{$item['token']} ({$item['count']}x)";
            })
            ->implode(', ');

        $descriptionTokenCount = $this->getDescriptionTokenCount($file, $fileDescriptions);

        return [
            'path' => $file,
            'content' => $content,
            'token_count' => count($encodedTokens),
            'description_token_count' => $descriptionTokenCount,
            'character_count' => strlen($content),
            'most_repeated_tokens' => $mostRepeatedTokens,
        ];
    }

    protected function getDescriptionTokenCount(string $file, array $fileDescriptions): int
    {
        $descriptionTokenCount = 0;

        foreach ($fileDescriptions as $description) {
            if ($description->path == $file) {
                $encodedDescriptionTokens = OpenAITokenizer::encode($description->description);
                $descriptionTokenCount = count($encodedDescriptionTokens);
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
