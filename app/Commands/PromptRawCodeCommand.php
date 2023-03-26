<?php

namespace App\Commands;

use App\DescriptionStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromptRawCodeCommand extends Command
{
    protected $signature = 'generate:raw-code';

    protected $description = 'Concatenate the code from all files in the current project without newlines';

    public function handle()
    {
        $projectDirectory = getcwd();

        $project = DB::table('projects')->where('path', $projectDirectory)->first();

        if (! $project) {
            $this->error('The current directory is not recognized as a project.');

            return;
        }

        $rawCode = DescriptionStorage::getRawCode($project->id);

        $this->info('Raw code without newlines for the current project:');
        $this->info($rawCode);
    }
}
