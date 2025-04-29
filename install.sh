#!/bin/bash

# Check if PHP is installed
if ! command -v php &> /dev/null
then
    echo "PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null
then
    echo "Composer is not installed. Please install Composer."
    exit 1
fi

echo "Installing dependencies..."
composer install

echo "Creating a symbolic link..."
sudo ln -sf $(pwd)/bin/console /usr/local/bin/my

# Discover the current shell's configuration file
shell_config=""
case "$SHELL" in
    */zsh)
        shell_config="$HOME/.zshrc"
        ;;
    */bash)
        shell_config="$HOME/.bashrc"
        ;;
    *)
        echo "Unsupported shell. Please add 'source $(pwd)/my-autocomplete.sh' manually to your shell configuration file."
        exit 1
        ;;
esac

# Add my-autocomplete.sh to the shell configuration file
if ! grep -q "source $(pwd)/my-autocomplete.sh" "$shell_config"; then
    echo "source $(pwd)/my-autocomplete.sh" >> "$shell_config"
    echo "Added 'source $(pwd)/my-autocomplete.sh' to $shell_config."
else
    echo "Autocomplete is already configured in $shell_config."
fi


echo "Verifying installation..."
if mycommands list &> /dev/null
then
    echo "Installation successful! Use 'my list' to see available commands."
else
    echo "Installation failed. Please check for errors."
    exit 1
fi