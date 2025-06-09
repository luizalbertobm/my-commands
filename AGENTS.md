# AGENTS Instructions

These guidelines define what an automated agent needs to do when opening a Pull Request or performing tasks in this repository.

## Preparing the Environment
Follow these steps to set up your environment before running the verification commands:

1. Run `composer install` to install all dependencies.
2. Verify your PHP version matches the one defined in `composer.json`.
3. Install a coverage driver as described below.

## Configuring Coverage Drivers
Tests with coverage require **Xdebug** or **PCOV**.

* **Xdebug**
  ```bash
  sudo apt-get install php-xdebug
  phpenmod xdebug
  ```
* **PCOV**
  ```bash
  sudo pecl install pcov
  echo "extension=pcov.so" | sudo tee /etc/php/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')/mods-available/pcov.ini
  phpenmod pcov
  ```

After enabling one of these extensions you can run `make test-coverage` safely.

## Code Requirements
1. Run `make check` to fix style and execute PHPStan.
2. Run `make test` to ensure all tests pass.
3. Run `make test-coverage` only when a coverage driver is active.
4. Make sure commits follow the Conventional Commits pattern. Examples:
   - `feat: add docker support`
   - `fix: correct path handling`

## Workflow
1. Create branches **always in English**. Suggested patterns:
   - `feature/<short-description>`
   - `fix/<bug-description>`
2. After adjusting the code, run `composer install` (if needed) and execute all
   verification commands.
3. When opening a PR, include a description using the template below.

### Pull Request Template

```markdown
### Objective

Describe the goal of this PR.

### How to test

Step-by-step commands to validate the changes.

### Points of attention

Mention anything reviewers should be aware of.
```

## Recommendations
- Use `php-cs-fixer` (already configured in the Makefile) to maintain the PSR-12 standard.
- See the README for installation and CLI usage instructions.
- Refer to the documentation in the `.docs/` directory for further guidance.
