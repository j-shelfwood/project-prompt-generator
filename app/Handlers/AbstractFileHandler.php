<?php

namespace App\Handlers;

abstract class AbstractFileHandler implements FileHandlerInterface
{
    protected string $filepath;

    protected string $content;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->content = file_get_contents($filepath);
    }
}
