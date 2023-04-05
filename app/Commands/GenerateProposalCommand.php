<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class GenerateProposalCommand extends ProjectCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate:proposal {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

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
        $remote = $this->option('remote');
        $targetDir = $remote ? $this->getProjectDirectory() : getcwd();

        $project = DB::table('projects')->where('path', $targetDir)->first();
        $projectId = $project ? $project->id : DB::table('projects')->insertGetId(['path' => $targetDir]);

        $fileAnalyzer = new FileAnalyzer($targetDir);
        $describer = new Describer();
        $descriptionStorage = new DescriptionStorage();

        $description = $this->ask('What is the description of the feature or request?');
    }
}
