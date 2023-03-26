<?php

namespace App;

use Illuminate\Support\Str;

class FileAnalyzer
{
    protected $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function getFilesToDescribe()
    {
        $files = [];

        // All the files in the app directory recursively (except for the exceptions)
        $files = array_merge($files, glob($this->directory.'/app/**/*.php', GLOB_BRACE));
        // And also the files in the app directory itself
        $files = array_merge($files, glob($this->directory.'/app/*.php'));

        // database/migrations
        $files = array_merge($files, glob($this->directory.'/database/migrations/*.php'));

        // resources/views
        $files = array_merge($files, glob($this->directory.'/resources/views/**/*.php', GLOB_BRACE));

        // routes/web.php && routes/api.php
        // Only if this project has them
        if (file_exists($this->directory.'/routes/web.php')) {
            $files[] = $this->directory.'/routes/web.php';
        }
        if (file_exists($this->directory.'/routes/api.php')) {
            $files[] = $this->directory.'/routes/api.php';
        }

        // Remove files ending in .blade.php
        $filteredFiles = collect($files)->filter(function ($file) {
            return ! Str::of($file)->contains('.blade.php');
        })->toArray();

        return $filteredFiles;
    }
}
