<?php

namespace App\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    protected $signature = 'install';

    protected $description = 'Install the command line tool';

    public function handle()
    {
        // If env = development show warning
        if (env('APP_ENV') === 'development') {
            $this->warn(PHP_EOL.'⚠️  You are running the command in development mode. This is not recommended.'.PHP_EOL);
        }

        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
        $appDir = $homeDir.DIRECTORY_SEPARATOR.'.project-prompt-generator';

        $this->line("📁 App directory: {$appDir}");

        if ($this->checkForExistingFiles($appDir)) {
            return;
        }

        $this->createDatabase($appDir);
        $this->createEnvFile($appDir);
        $this->migrateDatabase($appDir);
    }

    protected function checkForExistingFiles(string $appDir): bool
    {
        $databaseFile = $appDir.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'database.sqlite';
        $envFile = $appDir.DIRECTORY_SEPARATOR.'.env';

        if (File::exists($databaseFile) || File::exists($envFile)) {
            $choice = $this->choice('Some files already exist. Do you want to delete the files and start fresh or cancel the installation?', ['Delete and start fresh', 'Cancel'], 1);

            if ($choice === 'Delete and start fresh') {
                File::delete($databaseFile);
                File::delete($envFile);
                File::deleteDirectory($appDir);
            } else {
                $this->line(PHP_EOL.'Installation canceled.');

                return true;
            }
        }

        $this->createAppDirectory($appDir);
        $this->createDatabaseDirectory($appDir);
        $this->createCacheDirectory($appDir);

        return false;
    }

    protected function createAppDirectory(string $appDir)
    {
        if (! File::exists($appDir)) {
            File::makeDirectory($appDir, 0755);
        }
    }

    protected function createDatabaseDirectory(string $appDir)
    {
        if (! File::exists($appDir.DIRECTORY_SEPARATOR.'database')) {
            File::makeDirectory($appDir.DIRECTORY_SEPARATOR.'database', 0755);
        }
    }

    protected function createCacheDirectory(string $appDir)
    {
        if (! File::exists($appDir.DIRECTORY_SEPARATOR.'cache')) {
            File::makeDirectory($appDir.DIRECTORY_SEPARATOR.'cache', 0755);
        }
    }

    protected function createDatabase(string $appDir)
    {
        $this->task('Creating database.sqlite file', function () use ($appDir) {
            $databaseFile = $appDir.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'database.sqlite';

            $this->line(PHP_EOL."📄 Database file: {$databaseFile}");

            File::put($databaseFile, '');
            File::chmod($databaseFile, 0755);

            return true;
        });
    }

    protected function createEnvFile(string $appDir)
    {
        $this->task('Creating .env file', function () use ($appDir) {
            $envFile = $appDir.DIRECTORY_SEPARATOR.'.env';

            $this->line(PHP_EOL."📄 .env file: {$envFile}");

                        if (! File::exists($envFile)) {
                $openAiApiKey = $this->ask('Please provide your OpenAI API key:');
                $projectDir = $this->ask('Do you want to add a PROJECT_DIRECTORY filepath for use later? (Default: $HOME/projects)');
                $projectDir = $projectDir ?: getenv('HOME').DIRECTORY_SEPARATOR.'projects';

                $envContent = "CACHE_FOLDER=$appDir/cache\nOPENAI_API_KEY={$openAiApiKey}\nPROJECT_DIRECTORY={$projectDir}";

                File::put($envFile, $envContent);
                File::chmod($envFile, 0755);

                return true;
            }

            $this->line('ℹ️  .env file already exists.');

            return false;
        });
    }

    protected function migrateDatabase(string $appDir)
    {
        $this->task('Migrating the database', function () use ($appDir) {
            // Load the new .env file
            $dotenv = \Dotenv\Dotenv::createUnsafeImmutable($appDir.DIRECTORY_SEPARATOR, '.env');
            $dotenv->load();
            // Show current config('database.connections.sqlite.database') value
            $this->line(PHP_EOL.'📄 Current database file configuration: '.config('database.connections.sqlite.database').PHP_EOL);
            Artisan::call('migrate', ['--force' => true]);

            return true;
        });
    }
}
