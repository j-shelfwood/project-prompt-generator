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
        $dbConnection = config('database.connections.sqlite.database');
        $envFilePath = app()->environmentFilePath();
        $appEnv = config('app.env');
        $this->info("Current app_env value: {$appEnv}");
        $this->info("SQLite database file: {$dbConnection}");
        $this->info(".env file path: {$envFilePath}");
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
