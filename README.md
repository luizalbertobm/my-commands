# My Commands

A collection of custom terminal commands designed to automate repetitive tasks and improve productivity.

## Prerequisites

Before installing and running the project, ensure you have the following:

- **PHP 8.1 or higher**: Required to run the commands.
- **Composer**: For managing dependencies.
- **Git**: For version control and generating commit messages.
- **OpenAI API Key**: Needed to interact with the OpenAI API. You can obtain one from [OpenAI's website](https://platform.openai.com/api-keys).

## Features

- Generate semantic Git commit messages using OpenAI.
- Easily extendable with additional commands.
- Built with Symfony Console for a robust CLI experience.

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
   ln -s $(pwd)/bin/console /usr/local/bin/mycommands
   ```

5. Verify the installation:
   ```bash
   mycommands list
   ```

6. Set up your OpenAI API Key:
   ```bash
   mycommands openai --setup
   ```

## Usage

Run the following command to see the available commands:
```bash
mycommands list
```

### Example: Generate a Semantic Commit Message
```bash
mycommands openai:commit
```

### Example: Send a Custom Prompt to OpenAI
```bash
mycommands openai:ask "Write a poem about technology."
```

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
