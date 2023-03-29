<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

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
    protected $description = 'Prepare and migrate the SQLite database in the user\'s home folder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Create a project-prompt-generator directory in the user's home folder
        $this->task('Creating project-prompt-generator directory in the user\'s home folder', function () {
            $home = $_SERVER['HOME'];
            $path = $home.'/project-prompt-generator';
            if (! file_exists($path)) {
                mkdir($path, 0777, true);
            }

            return true;
        });

        $this->task('Creating .env file', function () {
            $apiKey = $this->askForApiKey();
            $home = $_SERVER['HOME'];
            $path = $home.'/project-prompt-generator/.env';

            if (! file_exists($path)) {
                touch($path);
            }

            // Save the API key to the .env file
            file_put_contents('.env', preg_replace(
                '/OPENAI_API_KEY=.*/',
                'OPENAI_API_KEY='.$apiKey,
                file_get_contents('.env')
            ));
        });

        // Touch the database file
        $this->task('Touching the database file', function () {
            $home = $_SERVER['HOME'];
            $path = $home.'/project-prompt-generator/database.sqlite';
            if (! file_exists($path)) {
                touch($path);
            }

            return true;
        });

        // Run the migrations
        $this->task('Running the migrations', function () {
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
