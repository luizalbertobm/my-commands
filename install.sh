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

# Install dependencies
echo "Installing dependencies..."
composer install

# Create a symbolic link
echo "Creating a symbolic link..."
sudo ln -sf $(pwd)/bin/console /usr/local/bin/my

# Verify installation
echo "Verifying installation..."
if mycommands list &> /dev/null
then
    echo "Installation successful! Use 'my list' to see available commands."
else
    echo "Installation failed. Please check for errors."
    exit 1
fi

# Enable autocomplete for Symfony Console commands
if [ -f "$(pwd)/symfony-autocomplete.bash" ]; then
    echo "Enabling autocomplete for My Commands..."
    echo "source $(pwd)/symfony-autocomplete.bash" >> ~/.bashrc
    source ~/.bashrc
    echo "Autocomplete enabled. Restart your terminal or run 'source ~/.bashrc' to apply changes."
else
    echo "Autocomplete script not found. Skipping autocomplete setup."
fi