<?php

namespace App\Scan\ProjectTypeHandlers;

class LaravelZeroProjectTypeHandler implements ProjectTypeHandler
{
    public function getRelevantDirectories(): array
    {
        return [
            '/app',
            '/config',
            '/database'
        ];
    }

    public function getSpecificFilepaths(): array
    {
        return ['/composer.json'];
    }

    public function getIgnoredFilepaths(): array
    {
        return [
            '/database/database.sqlite'
        ];
    }
}
