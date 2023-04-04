# Project Analyzer

This codebase is a command line tool for analyzing Laravel projects. The tool provides detailed information about the files in a project, including token counts, character counts, and the most repeated tokens.
## Features
- Analyze the current project and display:
- Number of tokens in each file
- Character count of each file
- Total number of tokens and characters in the project
- Most repeated tokens in the project
- Concatenate code from all files in the current project without newlines
- List all files used for context in the current working directory
- Generate AI-readable context prompts for a Laravel project
- Install the command line tool
- Describe files in a Laravel project
## Installation

To install the command line tool globally using Composer, run the following command:

```bash
composer global require shelfwood/project-prompt-generator
```



After installing the package, run the `install` command to set up the necessary files and configuration:

```bash
prompt install
```

The `install` command performs the following tasks:
1. Checks for existing files and prompts the user to either delete them and start fresh or cancel the installation.
2. Creates a `database.sqlite` file in the `database` directory of the globally installed package.
3. Creates an `.env` file and prompts the user to enter their OpenAI API key.
4. Migrates the database.

## Commands
- `install`: Install the command line tool
- `generate`: Generate AI-readable descriptions of each file for a Laravel project and saves them to `database.sqlite`
- `analyze`: Analyze the current project and display detailed information about token counts, character counts, and the most repeated tokens
- `copy:code`: Concatenate code from all files in the current project without newlines
- `copy:files`: List all files used for context in the current working directory

## Dependencies
- DescStorage
- FileAnalyzer
- OpenAITokenizer

## Usage
1. Install the command line tool using the `install` command.
2. Run the desired command to analyze the project, list files, or generate AI-readable context prompts.

## Example

```bash
cd /projects/my-laravel-project

prompt analyze
```



Output:

```mathematica

File               Raw token count    Description token Count    Character count    Most repeated tokens
config/app.php     120                45                          3500               ; (35x), -> (20x), [ (15x)
routes/web.php     90                 30                          1500               -> (10x), Route (8x), use (7x)
...
Total token count: 1000
Total description token count: 500
Total character count: 10000
```



For more information on how to use each command, refer to the command descriptions above.
