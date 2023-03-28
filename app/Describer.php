<?php

namespace App;

use App\Handlers\AbstractFileHandler;

class OpenAIDescriber
{
    public function describe(AbstractFileHandler $handler): string
    {
        return $handler->generateDescription();
    }

    public function describeFile($file, $contents): string
    {
        $handler = $this->determineHandler($file);

        return $handler->generateDescription($contents);
    }

    private function determineHandler($file): AbstractFileHandler
    {
        // Determine the appropriate handler based on the file type or other conditions
        // return new PHPFileHandler();
    }
}
