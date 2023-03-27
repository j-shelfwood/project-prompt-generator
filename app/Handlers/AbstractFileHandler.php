<?php

namespace App\Handlers;

abstract class AbstractFileHandler implements FileHandlerInterface
{
    protected string $filepath;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }
}
