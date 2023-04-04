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
       $handlers = [
           // ['pattern' => '/routes', 'handler' => RouteFileHandler::class],
           // ['pattern' => '/package.json', 'handler' => PackageFileHandler::class],
           // ['pattern' => '/composer.json', 'handler' => PackageFileHandler::class],
           // ['pattern' => '/config', 'handler' => ConfigFileHandler::class],
           // ['pattern' => '/database/migrations', 'handler' => MigrationFileHandler::class],
       ];

       foreach ($handlers as $handler) {
           if (strpos($file, $handler['pattern']) !== false) {
               return new $handler['handler']($file);
           }
       }

       return new PHPFileHandler($file);
   }
}
