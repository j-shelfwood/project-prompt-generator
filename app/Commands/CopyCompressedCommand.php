<?php

namespace App\Commands;

use App\DescriptionStorage;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class CopyCompressedCommand extends ProjectCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'copy:compressed {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Concatenates all the compressed file descriptions for the current project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $remote = $this->option('remote');
        $targetDir = $remote ? $this->getProjectDirectory() : getcwd();

        $project = DB::table('projects')->where('path', $targetDir)->first();

        if (!$project) {
            $this->error('The current directory is not recognized as a project.');

            return;
        }

        $files = (new DescriptionStorage)->getFileDescriptions($targetDir);

        $compressedDescriptions = collect($files)->map(function ($file) {
            return $file->description;
        })->implode('|');

        $this->info($compressedDescriptions);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
