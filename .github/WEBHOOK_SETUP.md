# GitHub Webhook Configuration for Packagist

## Automatic Setup (Recommended)
If you want Packagist to automatically manage the webhook:

1. Log in to [Packagist](https://packagist.org) via GitHub
2. Make sure the Packagist application has access to your GitHub organizations
3. Check your package list for any warnings about automatic sync
4. If needed, trigger a manual account sync in your Packagist profile

## Automated Versioning and Release Workflow

The GitHub Actions workflow (`packagist-sync.yml`) automatically:

### 1. **Version Bumping Strategy**
Based on commit messages, it automatically determines the version bump:

- **Major version** (x.0.0): Commit messages containing `BREAKING CHANGE:` or `major:`
- **Minor version** (x.y.0): Commit messages containing `feat:`, `feature:`, or `minor:`
- **Patch version** (x.y.z): All other commits (default)

> **Note**: Commits with `[skip ci]` tag are excluded from version calculations

### 2. **Automated Actions**
When you push to main/master branch:
- Runs tests with coverage reporting
- Updates README badge with current code coverage percentage
- Runs PHPStan and code style checks
- Analyzes commit messages since the last tag
- Calculates the next semantic version
- Creates and pushes a new Git tag
- Creates a GitHub release with categorized changelog
- Notifies Packagist to update the package

### 3. **Commit Message Examples**
```bash
# Patch version bump (1.0.0 → 1.0.1)
git commit -m "fix: resolve issue with docker commands"
git commit -m "docs: update README"

# Minor version bump (1.0.0 → 1.1.0)
git commit -m "feat: add new currency conversion command"
git commit -m "feature: implement git stash restore functionality"

# Major version bump (1.0.0 → 2.0.0)
git commit -m "BREAKING CHANGE: refactor command interface"
git commit -m "major: rewrite core architecture"
```

## Manual Webhook Setup
If you prefer to configure the webhook manually in your GitHub repository:

### Webhook Settings
- **Payload URL**: `https://packagist.org/api/github?username=beecodersteam`
- **Content Type**: `application/json`
- **Secret**: Your Packagist API Token (found in your Packagist profile)
- **Events**: Select "Just the push event"

### Steps to Configure:
1. Go to your GitHub repository settings
2. Click on "Webhooks" in the left sidebar
3. Click "Add webhook"
4. Fill in the settings above
5. Click "Add webhook"

### Environment Variables for GitHub Actions
Add the following secret to your repository:

- `PACKAGIST_TOKEN`: Your Packagist API token

To add this secret:
1. Go to your repository on GitHub
2. Click Settings → Secrets and variables → Actions
3. Click "New repository secret"
4. Name: `PACKAGIST_TOKEN`
5. Value: Your Packagist API token

## Package Information
- **Package Name**: beecodersteam/mycommands
- **Repository**: https://github.com/beecodersteam/mycommands (assumed)
- **Type**: Composer library

## Verification
After setting up the workflow, you can verify it's working by:
1. Making a commit with a semantic message and pushing to your repository
2. Checking that a new tag was created automatically
3. Verifying a GitHub release was created
4. Checking the Packagist page for your package to see if it updates
5. Looking at the Actions tab to see the workflow execution

## Troubleshooting
- **No tag created**: Check the commit messages follow the semantic format
- **Packagist not updating**: Verify the `PACKAGIST_TOKEN` secret is set correctly
- **Workflow fails**: Check the Actions tab for detailed error logs
- **Coverage badge not updating**: Ensure Xdebug is enabled and tests are generating coverage data

## Features

### 🚀 Performance Optimizations
- **Dependency Caching**: Composer dependencies are cached between workflow runs
- **Selective Execution**: Workflow ignores documentation and README changes to prevent unnecessary runs

### 📊 Quality Checks
- **PHPStan Analysis**: Static analysis to catch potential issues
- **Code Style Verification**: Optional PHP CS Fixer check
- **Test Coverage**: Automated test coverage reporting

### 📝 Documentation
- **Categorized Changelog**: Commits are organized by type (features, fixes, breaking changes)
- **Status Badge**: GitHub Actions workflow status displayed in README
