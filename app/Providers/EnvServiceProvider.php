<?php

namespace App\Providers;

use Dotenv\Dotenv;
use Illuminate\Support\ServiceProvider;

class EnvServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        if ($this->app->environment('production')) {
            $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
            $appDir = $homeDir.DIRECTORY_SEPARATOR.'.project-prompt-generator';
            $envPath = $appDir.DIRECTORY_SEPARATOR.'.env';

            if (file_exists($envPath)) {
                $dotenv = Dotenv::createImmutable($appDir, '.env');
                $dotenv->load();
            }
        }
    }
}
