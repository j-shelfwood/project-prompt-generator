<?php

namespace App\Commands;

use App\Helpers\OpenAITokenizer;
use Illuminate\Support\Facades\File;

class CopyCommand extends ProjectCommand
{
    protected $signature = 'copy {--output= : Output file path} {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    protected $description = 'Copy specified files, trim whitespace, and implode them into a single text blob';

    public function __construct()
    {
        parent::__construct();

        $this->projectDirectory = $this->getProjectDirectory();
    }

    public function handle()
    {
        $outputPath = $this->option('output') ?: 'output.txt';

        $this->info('Enter file paths or directory paths to include: ("bootstrap/", "phpunit.xml", etc.)"');
        $includePaths = $this->askForPaths();

        $this->info('Enter file paths or directory paths to exclude: ("database/database.sqlite", "package.lock", etc.)"');
        $excludePaths = $this->askForPaths();

        $includeFiles = $this->getFiles($includePaths);
        $excludeFiles = $this->getFiles($excludePaths);
        $files = array_diff($includeFiles, $excludeFiles);

        $content = $this->implodeFiles($files);

        // Display the table
        $this->displayFileTable($files);

        // Save the output
        File::put($outputPath, $content);
        $this->info('Files imploded and saved to ' . $outputPath);
    }

    private function askForPaths()
    {
        $paths = [];

        while (true) {
            $path = $this->ask('Enter a file or directory path (leave empty to finish):');

            if (!$path) {
                break;
            }

            $paths[] = $path;
        }

        return $paths;
    }

    private function getFiles($paths)
    {
        $files = [];

        foreach ($paths as $path) {
            $fullPath = $this->projectDirectory . '/' . $path;

            if (is_dir($fullPath)) {
                $directoryFiles = File::allFiles($fullPath);
                $files = array_merge($files, $directoryFiles);
            } elseif (is_file($fullPath)) {
                $files[] = $fullPath;
            } else {
                $this->warn("Path not found: $path");
            }
        }

        return $files;
    }


    private function implodeFiles($files)
    {
        $content = '';

        foreach ($files as $file) {
            $fileContent = File::get($file);
            $trimmedContent = preg_replace('/\s+/', ' ', $fileContent);
            $content .= $trimmedContent;
        }

        return $content;
    }

    private function displayFileTable($files)
    {
        $headers = ['File Path', 'Token Count', 'Character Count'];
        $rows = [];

        foreach ($files as $file) {
            $fileContent = File::get($file);
            $rows[] = [
                'path' => str_replace($this->projectDirectory . '/', '', $file),
                'token_count' => OpenAITokenizer::count($fileContent),
                'count' => strlen($fileContent),
            ];
        }

        $totalTokenCount = array_sum(array_column($rows, 'token_count'));

        $this->table($headers, $rows);
        $this->info("Total token count: $totalTokenCount");
    }
}
