<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class InfoCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'info';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Shows information about the currently configured project. Such as the name, version, and environment.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appEnv = config('app.env');
        $dbConfig = config('database.connections.sqlite.database');
        $envFilePath = app()->environmentFilePath();
        $getenvPath = getenv('HOME') ?: getenv('USERPROFILE');
        $openAiApiKey = config('openai.api_key');
        $openaiApiKeyEnv = env('OPENAI_API_KEY', 'NOT FOUND');

        $this->table(
            ['Name', 'Value'],
            [
                ['Environment', $appEnv],
                ['Database', $dbConfig],
                ['Environment File', $envFilePath],
                ['Getenv Path', $getenvPath],
                ['OpenAI API Key', $openAiApiKey],
                ['OpenAI API Key from env', $openaiApiKeyEnv],
            ]
        );
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
