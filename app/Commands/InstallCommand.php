<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Phar;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Prepare and migrate the SQLite database in the globally installed executable folder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $executablePath = Phar::running() ? dirname(Phar::running(false)) : realpath(__DIR__.'/../../../');

        $this->task('Creating .env file...', function () use ($executablePath) {
            $apiKey = $this->askForApiKey();
            $env = $executablePath.'/.env';
            $database = $executablePath.'/database.sqlite';
            touch($env);
            // Setup the keys
            file_put_contents($env, 'OPENAI_API_KEY='.$apiKey.PHP_EOL);
            file_put_contents($env, 'DB_DATABASE=.'.$database.PHP_EOL, FILE_APPEND);
        });

        // Touch the database file
        $this->task('Touching the database file...', function () use ($executablePath) {
            $database = $executablePath.'/database.sqlite';

            touch($database);

            return true;
        });

        // Run the migrations
        $this->task('Running the database migrations...', function () {
            $this->call('migrate:fresh');
        });

        $this->info(PHP_EOL.'✅ Installation complete!');
        $this->info('Run `php prompt generate` to get started.'.PHP_EOL);
        $this->info('Run `php prompt generate:raw-code` to get a blob of all the crucial raw code in your project.'.PHP_EOL);
    }

    protected function askForApiKey()
    {
        return $this->ask('Please enter your API key:');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
