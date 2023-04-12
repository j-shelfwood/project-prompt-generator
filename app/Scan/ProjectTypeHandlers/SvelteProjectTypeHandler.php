<?php

namespace App\Scan\ProjectTypeHandlers;

class SvelteProjectTypeHandler implements ProjectTypeHandler
{
    public function getRelevantDirectories(): array
    {
        return [
            // Add relevant directories for Svelte projects
        ];
    }

    public function getSpecificFilepaths(): array
    {
        return ['/package.json'];
    }

    public function getIgnoredFilepaths(): array
    {
        return [
            //
        ];
    }
}
