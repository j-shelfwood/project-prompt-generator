<?php

namespace App;

use App\Handlers\AbstractFileHandler;
use App\Handlers\PHPFileHandler;

class Describer
{
    protected ChatGPT $chatGPT;

    public function __construct()
    {
        $this->chatGPT = new ChatGPT();
    }

    public function describe(string $file): string
    {
        $prompt = $this->determineHandler($file)
            ->buildPrompt();

        return $this->chatGPT->chat($prompt)
            ->messages()
            ->last()['content'];
    }

    private function determineHandler($file): AbstractFileHandler
    {
        // Determine the appropriate handler based on the file type or other conditions
        // If it is a ./package.json or ./composer.json file, return a new PackageFileHandler()
        // If it is a ./config directory, return a new ConfigFileHandler()
        // If it is a ./database/migrations directory, return a new MigrationFileHandler()
        // If it is any other php file return the PHPFileHandler()

        // if (strpos($file, '/routes') !== false) {
        //     return new RouteFileHandler();
        // }
        // if (strpos($file, '/package.json') !== false || strpos($file, '/composer.json') !== false) {
        //     return new PackageFileHandler();
        // }
        // if (strpos($file, '/config') !== false) {
        //     return new ConfigFileHandler();
        // }
        // if (strpos($file, '/database/migrations') !== false) {
        //     return new MigrationFileHandler();
        // }

        return new PHPFileHandler($file);
    }
}
