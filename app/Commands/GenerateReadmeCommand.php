<?php

namespace App\Commands;

use App\ChatGPT;
use App\Scan\FileAnalyzer;
use App\Handlers\PHPFileHandler;
use App\Helpers\OpenAITokenizer;
use Illuminate\Console\Scheduling\Schedule;

class GenerateReadmeCommand extends ProjectCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'readme {--remote : Use the directories in the PROJECT_DIRECTORY instead of the current working directory}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generates a README.md file for the current project.';

    protected $files;

    protected ChatGPT $chat;

    protected $context;

    protected $instructions = 'You are an expert at writing README.md files for projects.
     You are allowed to view each file of the project;
     one at a time.
     You will ONLY RESPOND with any crucial information someone else (A generative AI) would need to write a good README.md for the project. You extract information relevant to features, installation & usage.

     [EXAMPLE OUTPUT:package.json or similar]
     composer.json: the project is a laravel project called shelfwood/prj-blog. It is using X and Y packages other than the defaults that come with laravel.
     [EXAMPLE OUTPUT:php file]
     routes/web.php: the project has routes for showing a list of posts, showing a single post, and creating a new post. You can also delete a post.
     ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->task('Determining which files to scan for context', function () {
            // Get all the filepaths from FileAnalyzer
            $this->files = (new FileAnalyzer($this->option('remote') ? $this->getProjectDirectory() : getcwd()))
                ->scan();

            return true;
        });

        $this->task('Preparing & instructing ChatGPT to scan files for context', function () {
            $this->chat = (new ChatGPT())
                ->system($this->instructions . 'NONE YET');

            return true;
        });

        $this->task('Collecting crucial information from every file to be used for context while writing the README.md file', function () {
            $this->context = $this->files->map(function ($file) {
                $content = (new PHPFileHandler($file))
                    ->strippedContent();

                $response = $this->chat->send('Extract info from ' . $file . ':' . $content)->receive();

                $this->chat->reset();

                $this->chat->system($this->instructions . $response);
                $this->comment("Extracted information from: {$file}");

                return [
                    'path' => $file,
                    'response' => $response,
                    'token_count' => OpenAITokenizer::count($response),
                ];
            });

            $tokenCount = OpenAITokenizer::count($this->context->pluck('response')->implode(' '));

            $this->info('Total token count: ' . $tokenCount);

            return true;
        });

        $this->task('Writing README.md file', function () {
            $this->chat->reset();
            $this->chat->system('
            You are an expert at writing README.md files for projects.
            Every file in the project has been scanned for crucial information provided as [CONTEXT].
            You are now going to write a readme file for the project. You will write the following sections: Introduction, Installation, Usage, Contributing, License, and Credits.
            ');

            $specialInstructions = $this->ask('Do you have any special instructions for the AI?');
            $context = $this->context->pluck('response')->implode(' ');

            $this->chat->send("
            [CONTEXT] $context
            [ADDITIONAL INSTRUCTIONS] $specialInstructions

            Write out the readme.md for the provided project context in markdown format; ONLY RESPOND WITH THE README.MD CONTENT.:
            ");

            $readme = $this->chat->receive();

            file_put_contents($this->option('remote') ? $this->getProjectDirectory() . '/README.md' : getcwd() . '/README.md', $readme);

            return true;
        });

        $this->info('✅ README.md file has been generated.');
        $this->info('👉 You can find it in the root of the project.');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        //
    }
}
