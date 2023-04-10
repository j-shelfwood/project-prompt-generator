<?php

namespace App;

use FilesystemIterator;
use Illuminate\Support\Collection;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;

class FileAnalyzer
{
    protected $directory;

    protected $type;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function getFilesToDescribe(): Collection
    {
        return $this->getCode();
    }

    protected function getCode(): Collection
    {
        $extension = $this->pickMostUsedFileExtension();

        $directoryIterator = new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS);
        $filterIterator = new CustomFilterIterator($directoryIterator);

        $iterator = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);

        $files = collect();
        foreach ($iterator as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === $extension) {
                $contents = file_get_contents($file->getPathname());
                if (strpos($contents, '@ignore') === false) {
                    $files->push($file->getPathname());
                }
            }
        }

        return $files;
    }

    protected function pickMostUsedFileExtension(): string
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        $extensions = collect();
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extensions->push(pathinfo($file, PATHINFO_EXTENSION));
            }
        }

        return $extensions->filter(function ($extension) {
            return $extension !== '';
        })->groupBy(function ($extension) {
            return $extension;
        })->map(function ($group) {
            return $group->count();
        })->sortDesc()->keys()->first();
    }
}

class CustomFilterIterator extends RecursiveFilterIterator
{
    public function accept(): bool
    {
        $filename = $this->current()->getFilename();
        if ($this->hasChildren()) {
            return ! in_array($filename, [
                'vendor',
                'node_modules',
                'site-packages',
                'bower_components',
                'storage',
                'bootstrap/cache',
            ]);
        }

        return true;
    }
}
