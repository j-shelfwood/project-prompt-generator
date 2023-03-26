<?php

namespace App\Commands;

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

        $fileDescriptions = DB::table('files')->where('project_id', $project->id)->get()->map(function ($file) {
            return file_get_contents($file->path);
        })->toArray();

        $rawCode = '';

        foreach ($fileDescriptions as $fileDescription) {
            $rawCode .= $fileDescription;
        }

        // Remove newlines
        $rawCode = preg_replace('/\s+/', ' ', $rawCode);

        $this->info('Raw code without newlines for the current project:');
        $this->info($rawCode);
    }
}
