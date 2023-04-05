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
            $this->warn('⚠️  You are running the command in development mode. This is not recommended.');
        }

        // Show current config('database.connections.sqlite.database') value
        $this->line('📄 Current database file configuration: '.config('database.connections.sqlite.database'));
        // Show DB_DATABASE value from .env file
        $this->line('📄 Current database file environment: '.env('DB_DATABASE'));

        $homeDir = getenv('HOME') ?: getenv('USERPROFILE');
        $appDir = $homeDir.DIRECTORY_SEPARATOR.'.project-prompt-generator';

        $this->line("📁 App directory: {$appDir}");
        // try {
            if ($this->checkForExistingFiles($appDir)) {
                return;
            }

            $this->createDatabase($appDir);
            $this->createEnvFile($appDir);
            $this->migrateDatabase($appDir);
        // } catch (\Exception $e) {
        //     $this->error('Something went wrong, try running the command as an administrator (we have to create 2 files in your app directory)');
        // }
    }

    protected function checkForExistingFiles(string $appDir): bool
    {
        $databaseFile = $appDir.DIRECTORY_SEPARATOR.'database.sqlite';
        $envFile = $appDir.DIRECTORY_SEPARATOR.'.env';

        if (File::exists($databaseFile) || File::exists($envFile)) {
            $choice = $this->choice('Some files already exist. Do you want to delete the files and start fresh or cancel the installation?', ['Delete and start fresh', 'Cancel'], 1);

            if ($choice === 'Delete and start fresh') {
                File::delete($databaseFile);
                File::delete($envFile);
                File::deleteDirectory($appDir);
            } else {
                $this->line('Installation canceled.');

                return true;
            }
        }

        // Create the app directory if it doesn't exist
        if (! File::exists($appDir)) {
            File::makeDirectory($appDir);
            File::chmod($appDir, 0775);
        }

        return false;
    }

    protected function createDatabase(string $appDir)
    {
        $this->task('Creating database.sqlite file', function () use ($appDir) {
            $databaseFile = $appDir.DIRECTORY_SEPARATOR.'database.sqlite';

            $this->line("📄 Database file: {$databaseFile}");

            if (File::exists($databaseFile)) {
                $this->line('ℹ️  database.sqlite file already exists.');

                return false;
            }

            File::put($databaseFile, '');
            File::chmod($databaseFile, 0755);

            return true;
        });
    }

    protected function createEnvFile(string $appDir)
    {
        $this->task('Creating .env file', function () use ($appDir) {
            $envFile = $appDir.DIRECTORY_SEPARATOR.'.env';

            $this->line("📄 .env file: {$envFile}");

            if (! File::exists($envFile)) {
                $openAiApiKey = $this->ask('Please provide your OpenAI API key:');
                $envContent = "OPENAI_API_KEY={$openAiApiKey}\nDB_DATABASE={$appDir}/database.sqlite";

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
            // Show current config('database.connections.sqlite.database') value
            $this->line('📄 Current database file configuration: '.config('database.connections.sqlite.database'));
            // Show DB_DATABASE value from .env file
            $this->line('📄 Current database file environment: '.env('DB_DATABASE'));
            // Load the new .env file
            $dotenv = \Dotenv\Dotenv::createUnsafeImmutable($appDir.DIRECTORY_SEPARATOR, '.env');
            $dotenv->load();
            // Update config('database.connections.sqlite.database') value
            // config(['database.connections.sqlite.database' => env('DB_DATABASE')]);
            // Show current config('database.connections.sqlite.database') value
            $this->line('📄 Current database file configuration: '.config('database.connections.sqlite.database'));
            // Show DB_DATABASE value from .env file
            $this->line('📄 Current database file environment: '.env('DB_DATABASE'));
            Artisan::call('migrate', ['--force' => true]);

            return true;
        });
    }
}
