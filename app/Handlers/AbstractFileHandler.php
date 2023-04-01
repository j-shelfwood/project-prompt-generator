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

    public function strippedContent(): string
    {
        // Remove newline characters and escape single quotes
        $content = preg_replace('/\s+/', '', $this->content);
        $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);

        // Remove opening <?php tag
        $content = preg_replace('/^<\?php/', '', $content);

        return $this->content;
    }
}
