<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

abstract class ProjectCommand extends Command
{
    protected string $projectDirectory;

    protected function getProjectDirectory(): string
    {
        // If $this->projectDirectory is set, return it
        if (isset($this->projectDirectory)) {
            return $this->projectDirectory;
        }

        // If the env REMOTE is set, use it
        if (env('REMOTE')) {
            return env('REMOTE');
        }

        $projectDirectory = env('PROJECT_DIRECTORY');
        if (!$projectDirectory) {
            $this->error('PROJECT_DIRECTORY is not set in the .env file.');
            exit;
        }

        $directories = glob($projectDirectory . '/*', GLOB_ONLYDIR);
        if (empty($directories)) {
            $this->error('No directories found in the PROJECT_DIRECTORY.');
            exit;
        }

        $selectedDirectory = $this->menu('Select a directory from the PROJECT_DIRECTORY:', $directories)->open();
        if ($selectedDirectory === null) {
            $this->error('No directory selected.');
            exit;
        }

        $this->projectDirectory = $directories[$selectedDirectory];

        return $directories[$selectedDirectory];
    }
}
