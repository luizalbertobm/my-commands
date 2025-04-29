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

1. Clone the repository:
   ```bash
   git clone https://github.com/luizalbertobm/my-commands.git
   ```

2. Navigate to the project directory:
   ```bash
   cd my-commands
   ```

3. Install dependencies using Composer:
   ```bash
   composer install
   ```

4. Create a symbolic link to make the commands globally accessible:
   ```bash
   sudo ln -s $(pwd)/bin/console /usr/local/bin/my
   ```

5. Verify the installation:
   ```bash
   mycommands list
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

1. Generate the completion file:

```bash
my completion > my-autocomplete.sh
```

2. Reload your shell configuration:
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
