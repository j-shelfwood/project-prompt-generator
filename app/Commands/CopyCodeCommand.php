<?php

namespace App\Commands;

use App\DescriptionStorage;
use App\Helpers\OpenAITokenizer;
use Illuminate\Support\Facades\DB;

class CopyCodeCommand extends ProjectCommand
{
    protected $signature = 'copy:code {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    protected $description = 'Concatenate the code from all files in the current project without newlines';

    public function handle()
    {
        $project = DB::table('projects')->where('path', $this->option('remote') ? $this->getProjectDirectory() : getcwd())->first();

        if (!$project) {
            $this->error('The current directory is not recognized as a project.');

            return;
        }

        $rawCode = DescriptionStorage::getRawCode($project->id);

        // Count the number of tokens with the OpenAITokenizer
        $count = OpenAITokenizer::count($rawCode);
        $this->info($rawCode);
    }
}
