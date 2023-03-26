<?php

namespace App\Commands;

use App\DescriptionStorage;
use Illuminate\Console\Command;

class CheckAndOutputProjectDescriptionCommand extends Command
{
    protected $signature = 'check:output-descriptions';

    protected $description = 'Check if files have been described and output descriptions';

    public function handle()
    {
        $projectPath = getcwd();
        $descriptionStorage = new DescriptionStorage();

        if (! $descriptionStorage->isProjectDescribed($projectPath)) {
            $this->error('Not all files have been described yet.');

            return;
        }

        $fileDescriptions = $descriptionStorage->getFileDescriptions($projectPath);
        $descriptions = implode(' ', array_column($fileDescriptions, 'description'));

        $this->info($descriptions);
    }
}
