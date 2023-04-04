<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class GenerateProposalCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate:proposal';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Accepts a description of a new feature or request and generates a proposal for it based on the files in the current directory.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $projectDirectory = getcwd();

        $project = DB::table('projects')->where('path', $projectDirectory)->first();
        $projectId = $project ? $project->id : DB::table('projects')->insertGetId(['path' => $projectDirectory]);

        $fileAnalyzer = new FileAnalyzer($projectDirectory);
        $describer = new Describer();
        $descriptionStorage = new DescriptionStorage();

        $description = $this->ask('What is the description of the feature or request?');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
