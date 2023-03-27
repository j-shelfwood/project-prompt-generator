<?php

namespace App;

use App\Handlers\AbstractFileHandler;

class OpenAIDescriber
{
    public function describe(AbstractFileHandler $handler): string
    {
        return $handler->generateDescription();
    }
}
