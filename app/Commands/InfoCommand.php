<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Phar;

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
        $this->table(
            ['Name', 'Value'],
            [
                ['Environment', config('app.env')],
                ['Database', config('database.connections.sqlite.database')],
                ['Environment File', app()->environmentFilePath()],
                ['Getenv Path', getenv('HOME') ?: getenv('USERPROFILE')],
                ['OpenAI API Key', config('openai.api_key')],
                ['OpenAI API Key from env', env('OPENAI_API_KEY')],
                ['Phar::running', Phar::running(true)],
                ['Phar::running(false)', Phar::running(true)],
            ],
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
