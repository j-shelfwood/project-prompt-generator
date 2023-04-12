<?php

namespace App\Scan;

use RecursiveFilterIterator;

class CustomFilterIterator extends RecursiveFilterIterator
{
    public function accept(): bool
    {
        $filename = $this->current()->getFilename();
        if ($this->hasChildren()) {
            return !in_array($filename, [
                'vendor',
                'node_modules',
                'site-packages',
                'bower_components',
                'storage',
                'bootstrap/cache',
                'tests',
                'config',
                '.svelte-kit',
                'static',
            ]);
        }

        return true;
    }
}
