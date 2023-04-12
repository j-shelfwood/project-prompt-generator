<?php

namespace App\Scan\ProjectTypeHandlers;

class LaravelProjectTypeHandler implements ProjectTypeHandler
{
    public function getRelevantDirectories(): array
    {
        return [
            // Add relevant directories for Laravel projects
        ];
    }

    public function getSpecificFilepaths(): array
    {
        return ['/package.json', '/composer.json'];
    }

    public function getIgnoredFilepaths(): array
    {
        return [
            //
        ];
    }
}
