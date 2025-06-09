# AGENTS Instructions

These guidelines define what an automated agent needs to do when opening a Pull Request or performing tasks in this repository.

## Code Requirements
1. Run `make check` to fix style and execute PHPStan.
2. **IMPORTANT**: Before running `make test`, install and enable a code coverage driver such as Xdebug or PCOV. Without a coverage driver, the tests will fail. If necessary, use `make xdebug.enable` to enable Xdebug.
3. Run `make test` to ensure all tests pass.
4. Make sure commits follow the Conventional Commits pattern.

## Workflow
1. Create branches **always in English** (e.g.: `feature/add-docker-support`).
2. After adjusting the code, run `composer install` (if you haven't already) and the verification commands above.
3. When opening a PR, describe what was done, how to test it, and any points of attention.

## Recommendations
- Use `php-cs-fixer` (already configured in the Makefile) to maintain the PSR-12 standard.
- See the README for details on installing and using the CLI tools.
