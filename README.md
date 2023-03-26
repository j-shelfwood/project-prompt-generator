This project is a Laravel-based AI-readable context prompt generator. The project consists of several components that work together to generate prompts that can be used to train AI models to describe Laravel projects' files.
## Components
### CheckAndOutputProjectDescriptionCommand

This command checks if all the files in the current project have been described and outputs their descriptions. If a file has not yet been described, an error message will be displayed.
### GeneratePromptCommand

This command generates AI-readable context prompts for the files in the current Laravel project. It uses the OpenAI GPT-3 API to describe the files and stores the descriptions in a database. The descriptions are then used to generate the context prompts.
### DescriptionStorage

This class provides methods to save and retrieve file descriptions from the database. It also provides methods to check if all the files in a project have been described.
### FileAnalyzer

This class provides methods to get a list of files in a Laravel project that should be described.
### OpenAIDescriber

This class provides a method to describe a file using the OpenAI GPT-3 API.
### Migrations

The migrations are used to create the projects and files tables in the database. These tables are used to store the project and file descriptions.
## Usage

To use this project, you need to have a Laravel project and an OpenAI API key. Once you have those, follow these steps:
1. Install the project dependencies by running `composer install`.
2. Create a `.env` file by copying the `.env.example` file and filling in the required values.
3. Run the migrations by running `php artisan migrate`.
4. Run the `generate` command by running `php artisan generate`. This will generate the context prompts for the files in the current Laravel project.
## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your changes.
