<?php

namespace App\Scan;

use FilesystemIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Scan\CustomFilterIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use App\Scan\ProjectTypeHandlers\ProjectTypeHandler;
use App\Scan\ProjectTypeHandlers\SvelteProjectTypeHandler;
use App\Scan\ProjectTypeHandlers\LaravelProjectTypeHandler;
use App\Scan\ProjectTypeHandlers\LaravelZeroProjectTypeHandler;

class FileAnalyzer
{
    protected string $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function scan(): Collection
    {
        $projectTypeHandler = $this->getProjectTypeHandler();
        $directories = $projectTypeHandler->getRelevantDirectories();
        $specificFiles = $projectTypeHandler->getSpecificFilepaths();

        return $this->getFilesFromDirectories($directories, $projectTypeHandler)
            ->merge($this->getFilesFromSpecificFilepaths($specificFiles));
    }

    protected function getProjectTypeHandler(): ProjectTypeHandler
    {
        $projectType = $this->getProjectType();

        if ($projectType === 'laravel') {
            return new LaravelProjectTypeHandler();
        }

        if ($projectType === 'svelte') {
            return new SvelteProjectTypeHandler();
        }

        if ($projectType === 'laravel-zero') {
            return new LaravelZeroProjectTypeHandler();
        }

        return new DefaultProjectTypeHandler();
    }

    protected function getFilesFromDirectories(array $directories, ProjectTypeHandler $projectTypeHandler): Collection
    {
        return collect($directories)->flatMap(function ($directory) use ($projectTypeHandler) {
            $directoryIterator = new RecursiveDirectoryIterator($this->directory . $directory, FilesystemIterator::SKIP_DOTS);
            $filterIterator = new CustomFilterIterator($directoryIterator);

            $iterator = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);

            return collect($iterator)
                ->filter(function ($file) use ($projectTypeHandler) {
                    return $this->containsIgnoreSignature($file) && !$this->isIgnoredFile($file, $projectTypeHandler);
                })
                ->map(function ($file) {
                    return $file->getPathname();
                });
        });
    }


    protected function isIgnoredFile($file, ProjectTypeHandler $handler): bool
    {
        $relativePath = str_replace($this->directory, '', $file->getPathname());
        $ignoredFilepaths = $handler->getIgnoredFilepaths();

        // Check if the file is larger than 1000 lines or has more than 5000 characters
        if ($file->isFile() && $file->getSize() > 5000) {
            return true;
        }

        foreach ($ignoredFilepaths as $ignoredFilepath) {
            if ($relativePath === $ignoredFilepath) {
                return true;
            }
        }

        // IGNORE_PATHS=/app/Helpers/characters.json,/app/Helpers/characters.php
        if (getenv('IGNORE_PATHS')) {
            $ignorePaths = explode(',', getenv('IGNORE_PATHS'));
            foreach ($ignorePaths as $ignorePath) {
                if ($relativePath === $ignorePath) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getFilesFromSpecificFilepaths(array $filepaths): Collection
    {
        return collect($filepaths)->map(function ($filepath) {
            return $this->directory . $filepath;
        })->filter(function ($filepath) {
            return file_exists($filepath);
        });
    }

    protected function containsIgnoreSignature($file): bool
    {
        if ($file->isFile()) {
            $contents = File::get($file->getPathname());
            return strpos($contents, '@ignore') === false;
        }

        return false;
    }

    protected function getProjectType(): ?string
    {
        if (file_exists($this->directory . '/composer.json')) {
            $composerContent = file_get_contents($this->directory . '/composer.json');
            if (strpos($composerContent, 'laravel-zero') !== false) {
                return 'laravel-zero';
            }
        }

        if (file_exists($this->directory . '/composer.json')) {
            $composerContent = file_get_contents($this->directory . '/composer.json');
            if (strpos($composerContent, 'laravel') !== false) {
                return 'laravel';
            }
        }

        if (file_exists($this->directory . '/package.json')) {
            $packageContent = file_get_contents($this->directory . '/package.json');
            if (strpos($packageContent, 'svelte') !== false) {
                return 'svelte';
            }
        }

        return null;
    }
}
