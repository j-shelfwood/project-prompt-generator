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
        $baseDirectory = $this->laravel->basePath();

        $this->info("📁 Base directory: {$baseDirectory}");

        if ($this->checkForExistingFiles($baseDirectory)) {
            return;
        }

        $this->createDatabase($baseDirectory);
        $this->createEnvFile($baseDirectory);
        $this->migrateDatabase();
    }

    protected function checkForExistingFiles(string $baseDirectory): bool
    {
        $databaseFile = $baseDirectory.'/database/database.sqlite';
        $envFile = $baseDirectory.'/.env';

        if (File::exists($databaseFile) || File::exists($envFile)) {
            $choice = $this->choice('Some files already exist. Do you want to delete the files and start fresh or cancel the installation?', ['Delete and start fresh', 'Cancel'], 1);

            if ($choice === 'Delete and start fresh') {
                File::delete($databaseFile);
                File::delete($envFile);
            } else {
                $this->info('Installation canceled.');

                return true;
            }
        }

        return false;
    }

    protected function createDatabase(string $baseDirectory)
    {
        $this->task('Creating database.sqlite file', function () use ($baseDirectory) {
            $databaseDirectory = $baseDirectory.'/database';
            $databaseFile = $databaseDirectory.'/database.sqlite';

            $this->info("📁 Database directory: {$databaseDirectory}");
            $this->info("📄 Database file: {$databaseFile}");

            if (! File::isDirectory($databaseDirectory)) {
                File::makeDirectory($databaseDirectory, 0755, true);
            }

            if (! File::exists($databaseFile)) {
                File::put($databaseFile, '');

                return true;
            }

            $this->info('ℹ️  database.sqlite file already exists.');

            return false;
        });
    }

    protected function createEnvFile(string $baseDirectory)
    {
        $this->task('Creating .env file', function () use ($baseDirectory) {
            $envFile = $baseDirectory.'/.env';

            $this->info("📄 .env file: {$envFile}");

            if (! File::exists($envFile)) {
                $openAiApiKey = $this->ask('Please provide your OpenAI API key:');
                $envContent = "OPENAI_API_KEY={$openAiApiKey}\n";

                File::put($envFile, $envContent);

                return true;
            }

            $this->info('ℹ️  .env file already exists.');

            return false;
        });
    }

    protected function migrateDatabase()
    {
        $this->task('Migrating the database', function () {
            Artisan::call('migrate', ['--force' => true]);
            $this->info(Artisan::output());

            return true;
        });
    }
}
