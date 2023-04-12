<?php

namespace App\Scan\ProjectTypeHandlers;

interface ProjectTypeHandler
{
    public function getRelevantDirectories(): array;

    public function getSpecificFilepaths(): array;

    public function getIgnoredFilepaths(): array;
}
