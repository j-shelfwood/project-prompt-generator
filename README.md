# Project CLI Tool

This is a Laravel Zero CLI tool that uses GPT with OpenAI API to generate prompts and analyze project files. You need an OpenAI API key to use this tool.

## Installation

To install the CLI tool globally via composer, run the following command:

```
composer global require shelfwood/project-prompt-generator
```

After installation, create a new project directory, and navigate to it in your terminal. Then, run the following command:

```
prompt install
```

This will install the necessary dependencies, create a database, and prompt you for a default projects to allow you to choose projects from a default directory. Handy for when you have multiple projects in a single directory that you want to analyze individually.

## Usage

### Analyze Project

To analyze the current working directory's files and display counts for tokens and descriptions, use the following command:

```
prompt analyze [--remote]
```

The `--remote` option can be used to specify a remote directory instead of using the current working directory.

### Clear Command

To clear all file descriptions from the database, use the following command:

```
project-cli clear
```

### Copy Code Command

To concatenate the code from all files in the current project without newlines and count the number of tokens, use the following command:

```
project-cli copy:files [--remote]
```

The `--remote` option can be used to specify a remote directory instead of using the current working directory.

### Copy Compressed Command

To concatenate all the compressed file descriptions for the current project and count the number of tokens, use the following command:

```
project-cli copy:compressed [--remote]
```

The `--remote` option can be used to specify a remote directory instead of using the current working directory.

### Generate Command

To generate AI-readable context prompts for a Laravel project and store them in a database, use the following command:

```
project-cli generate [--remote]
```

The `--remote` option can be used to specify a remote directory instead of using the current working directory.

### Generate Proposal Command

To generate a proposal based on the files in the current directory, use the following command:

```
project-cli generate:proposal [--remote]
```

The command retrieves the project directory and the project ID from the database and creates instances of `FileAnalyzer`, `Describer`, and `DescriptionStorage` to process the files in the directory and store the generated proposal. Finally, the command prompts the user to provide a description of the feature or request.

### Readme Command

To generate a README.md file for the current project, use the following command:

```
project-cli readme [--remote]
```

The `--remote` option can be used to specify a remote directory instead of using the current working directory. The command uses Laravel's `FileAnalyzer` class to determine which files to scan for context information. It also uses an instance of `ChatGPT` to gather crucial information from each file to be used as context while writing the README.md file. Finally, the command prompts the user to provide any special instructions and then writes out the complete README.md in markdown format.

## Contributing

If you would like to contribute to this project, please submit a pull request on GitHub or contact the project owner directly.

## License

This project is licensed under the MIT license.

## Credits

This project was created by Joris Schelfhout. Special thanks to Bit Academy for providing the resources to create this project.