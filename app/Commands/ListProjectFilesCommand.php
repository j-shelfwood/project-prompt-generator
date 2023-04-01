<?php

namespace App\Commands;

use App\FileAnalyzer;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListProjectFilesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'list:files';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Lists all the files that would be used for context in the current working directory.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = (new FileAnalyzer(getcwd()))
            ->getFilesToDescribe();

        // Print the files as a concatenated string
        $this->info(implode(PHP_EOL, $files));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
