<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ClearCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'Clear';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Clears all the file descriptions in the database. This is useful if you want to start over with the generate command.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Clear the database but hide console output
        $this->callSilent('migrate:fresh');

        $this->info('✅ All file descriptions have been cleared.');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
