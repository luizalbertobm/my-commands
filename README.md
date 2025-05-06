# My Commands

A collection of custom terminal commands designed to automate repetitive tasks and improve productivity.

This project includes commands for generating semantic Git commit messages using OpenAI, sending custom prompts to OpenAI, and more.

## Demo
![Command Demo](.docs/screen.gif)

## Prerequisites

Before installing and running the project, ensure you have the following:

- **PHP 8.1 or higher**: Required to run the commands.
- **Composer**: For managing dependencies.
- **Git**: For version control and generating commit messages.
- **OpenAI API Key**: Needed to interact with the OpenAI API. You can obtain one from [OpenAI's website](https://platform.openai.com/api-keys).

## Features

- Generate semantic Git commit messages using OpenAI.
- Send custom prompts to OpenAI and receive responses.
- Automate repetitive tasks with custom commands.
- Easily extendable architecture for adding new commands.
- Code quality tools for maintaining code standards.

## Installation

You can install My Commands in two ways:

### Option 1: Using the Installation Script (Recommended)

Run the installation script that will automatically handle all the setup for you:
```bash
./install.sh
```

After running the script, you have to reload your shell configuration file to apply the changes.
```bash
source ~/.bashrc # or source ~/.zshrc
```

The script will:
- Check if PHP and Composer are installed
- Install dependencies
- Create a symbolic link to make the command globally accessible
- Configure shell autocompletion

### Option 2: Manual Installation

If you prefer to install step by step:


1. Install dependencies using Composer:
   ```bash
   composer install
   ```

2. Create a symbolic link to make the commands globally accessible:
   ```bash
   sudo ln -s $(pwd)/bin/console /usr/local/bin/my
   ```

3. Add shell autocompletion to your configuration file:
   ```bash
   # For Bash
   echo "source $(pwd)/my-autocomplete.sh" >> ~/.bashrc
   # OR for Zsh
   echo "source $(pwd)/my-autocomplete.sh" >> ~/.zshrc
   ```

4. Reload your shell configuration:
   ```bash
   source ~/.bashrc # or source ~/.zshrc
   ```

5. Verify the installation:
   ```bash
   my list
   ```

## Usage

Run the following command to see the available commands:
```bash
my list
```

### Example: Generate a Semantic Commit Message
```bash
my ai:commit
```

### Example: Send a Custom Prompt to OpenAI
```bash
my ai:ask "Write a poem about technology."
```


## Shell Completion

The shell completion is automatically activated when you run the `./install.sh` script.
The `my-autocomplete.sh` contains the necessary functions to enable auto-completion for the commands.

### Updating the Completion File

If you create new commands or modify existing ones, you can regenerate the completion file by running:

1. Regenerate the completion file:

```bash
my completion > my-autocomplete.sh
```

Reload your shell configuration file to apply the changes:
```bash
source ~/.bashrc # or source ~/.zshrc
```

Now, you should have auto-completion enabled. Simply type `my` followed by a tab to see the available commands.


## Development

### Code Quality Tools
- Run PHPStan for static analysis:
  ```bash
  make phpstan
  ```
- Fix code style issues:
  ```bash
  make fixer
  ```

### Adding New Commands
1. Create a new command class in the `src/Command` directory.
2. Register the command in `bin/console`.


## Contributing

Contributions are welcome! Feel free to open issues or submit pull requests.

## License

This project is licensed under the [MIT License](LICENSE).
