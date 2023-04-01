<?php

namespace App\Commands;

use App\FileAnalyzer;
use App\OpenAITokenizer;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class AnalyzeProjectCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'analyze';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Analyze the current project and show how many tokens each file contains; the character count of each file; and the total number of tokens and characters in the project. It also shows the most repeated tokens in the project.';

    public function handle()
    {
        // Get all the files in the current project
        $files = (new FileAnalyzer(getcwd()))
            ->getFilesToDescribe();

        $tokenCounts = [];

        $files = collect($files)->map(function ($file) use (&$tokenCounts) {
            $content = file_get_contents($file);
            // Remove all newlines
            $content = preg_replace('/\s+/', '', $content);
            $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);

            // Remove the opening php tag
            $content = preg_replace('/^<\?php/', '', $content);

            // Example of the output of the OpenAITokenizer::encode() method:
            $encodedTokens = OpenAITokenizer::encode($content);

            // Count the occurrences of each token
            foreach ($encodedTokens as $token => $count) {
                if (isset($tokenCounts[$token])) {
                    $tokenCounts[$token] += $count;
                } else {
                    $tokenCounts[$token] = $count;
                }
            }

            $mostRepeatedTokens = collect($encodedTokens)
                ->map(function ($count, $token) {
                    return [
                        'token' => $token,
                        'count' => $count,
                    ];
                });

            return [
                'path' => $file,
                'content' => $content,
                'token_count' => OpenAITokenizer::count($content),
                'character_count' => strlen($content),
                'most_repeated_tokens' => $mostRepeatedTokens->sortBy('count')->take(5)->pluck('token')->implode(', '),
            ];
        });

        // Sort token counts in descending order
        arsort($tokenCounts);

        $totalTokenCount = $files->sum('token_count');
        $totalCharacterCount = $files->sum('character_count');

        // Show a table descending by token count
        $this->table(
            ['File', 'Token count', 'Character count', 'Most repeated tokens'],
            $files->sortByDesc('token_count')->map(function ($file) {
                return [
                    $file['path'],
                    $file['token_count'],
                    $file['character_count'],
                    $file['most_repeated_tokens'],
                ];
            }));
        $this->info("Total token count: {$totalTokenCount}");
        $this->info("Total character count: {$totalCharacterCount}");
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        //
    }
}
