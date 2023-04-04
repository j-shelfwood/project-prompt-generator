<?php

namespace App\Commands;

use App\DescriptionStorage;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class GenerateReadmeCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'readme';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generates a README.md file for the current project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projectDirectory = getcwd();
        $descriptionStorage = new DescriptionStorage();

        if (! $descriptionStorage->isProjectDescribed($projectDirectory)) {
            $this->error('Not all files have been described yet.');

            return;
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        //
    }
}
