<?php

namespace App\Handlers;

abstract class AbstractFileHandler implements FileHandlerInterface
{
    protected string $filepath;

    protected string $content;

    public function __construct(string $filepath, $content = null)
    {
        $this->filepath = $filepath;
        $this->content = $content ?? file_get_contents($filepath);
    }

    public function strippedContent(): string
    {
        // Remove newline characters and escape single quotes
        $content = preg_replace('/\s+/', '', $this->content);
        $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);
        $content = preg_replace('/^<\?php/', '', $content);
        // remove all dollar signs
        $content = str_replace('$', '', $content);
        // Replace all -> by .
        $content = str_replace('->', '.', $content);

        return $this->content;
    }
}
