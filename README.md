# Laravel CLI Tool

This is a command line tool written in Laravel Zero that provides various functions for analyzing and generating descriptions of files in a Laravel project. The tool uses generative AI to create prompts for use with ChatGPT and can also generate a README.md file for your project.

## Installation

Install the tool globally using Composer:

```
composer global require shelfwood/project-prompt-generator
```

Then run the install command:
```
prompt install
```

The `install` command creates a database file named `database.sqlite` in the `database` directory and a `.env` file. During the `.env` creation, you will be prompted to input your OpenAI API key. The installation process also migrates the database.

## Usage

The tool contains several commands that can be used to analyze and generate descriptions of files in a Laravel project. Here are the available commands:

- `analyze`: Analyzes the current project and shows how many tokens each file contains, the character count of each file, and the total number of tokens and characters in the project. This command uses a progress bar during the analysis and displays the results in a table.

- `clear`: Clears all file descriptions in the database, which is useful when starting over with the generate command.

- `copy:code`: Concatenates the code of all files in the current project, without newlines.

- `copy:compressed`: Concatenates all the compressed file descriptions for the current project.

- `copy:files`: Prints a list of files from the current working directory as a concatenated string.

- `generate`: Analyzes files in a Laravel project and generates a description of each file. The descriptions are saved to a database.

- `generate:proposal`: Accepts a description of a new feature or request and generates a proposal for it based on the files in the current directory.

- `readme`: Generates a README.md file for the current project by scanning files for context using `FileAnalyzer`, `PHPFileHandler`, and `ChatGPT`. The generated README file has Introduction, Installation, Usage, Contributing, License, and Credits sections.

## Contributing

Contributions are welcome! If you have any issues or feature requests, please open an issue on GitHub or submit a pull request. Please make sure to follow the code style of the project when making any contributions.

## License

This tool is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Credits

This tool was created by the Laravel CLI Tool team and uses several open-source libraries, including:
- Laravel Zero
- OpenAI api
- Composer