# Packagist Integration Guide

## Package Information

- **Package Name**: `beecodersteam/mycommands`
- **Package Type**: CLI Tool / PHP Library
- **Repository**: GitHub

## Automatic Synchronization

This package uses GitHub webhooks and GitHub Actions to automatically synchronize with Packagist whenever:

1. New commits are pushed to the main/master branch
2. New releases are published

## Validating the Packagist Integration

To verify that the integration is working correctly:

### Step 1: Check Package Visibility

1. Visit [https://packagist.org/packages/beecodersteam/mycommands](https://packagist.org/packages/beecodersteam/mycommands)
2. Confirm that the package details are correct:
   - Package name
   - Description
   - Repository URL
   - Latest version

### Step 2: Validate Webhook Configuration

1. Go to your GitHub repository settings
2. Navigate to "Webhooks"
3. Look for the Packagist webhook with URL: `https://packagist.org/api/github?username=beecodersteam`
4. Check that:
   - Webhook is active
   - Last delivery was successful (green checkmark)
   - Content type is set to `application/json`
   - "Just the push event" is selected

### Step 3: Verify GitHub Actions Integration

1. Go to the "Actions" tab in your GitHub repository
2. Look for the most recent "Version Tag and Packagist Sync" workflow run
3. Confirm that the workflow completed successfully
4. Check the "Notify Packagist" step to ensure it ran without errors

## Troubleshooting Packagist Integration

### Package Not Updating on Packagist

**Potential causes:**

1. **Webhook issues:**
   - Check the webhook delivery history in GitHub repository settings
   - Look for any failed webhook deliveries (marked with red X)
   - Review the response details for error messages

2. **API Token expired or revoked:**
   - Generate a new Packagist API token
   - Update the `PACKAGIST_TOKEN` secret in GitHub repository settings
   - Test by manually triggering the workflow

3. **Package name mismatch:**
   - Ensure the package name in `composer.json` matches the one on Packagist
   - Verify that the repository URL is correctly associated with your package

### Manually Updating Packagist

If automatic synchronization fails, you can manually update the package:

1. Log in to [Packagist](https://packagist.org/)
2. Navigate to your package page
3. Click the "Update" button

## Setting Up Packagist on a New Repository

If you're setting up Packagist integration on a new repository:

1. **Create a Packagist account** (if you don't have one)
2. **Submit your package:**
   - Go to [Packagist](https://packagist.org/packages/submit)
   - Enter your repository URL
   - Click "Check" and then "Submit"
3. **Configure the webhook:**
   - Use the automatic setup option when submitting the package
   - Or follow the manual webhook setup in [WEBHOOK_SETUP.md](./WEBHOOK_SETUP.md)
4. **Add required secrets:**
   - Add your Packagist API token as a repository secret named `PACKAGIST_TOKEN`
5. **Push the workflow files:**
   - Ensure both workflow files are present in `.github/workflows/`
   - Commit and push to the main/master branch

## Composer Installation after Publishing

Once published, users can install your package via Composer:

```bash
composer require beecodersteam/mycommands
```

## Related Documentation

- [Packagist Documentation](https://packagist.org/about)
- [Composer Documentation](https://getcomposer.org/doc/)
- [GitHub Webhook Documentation](https://docs.github.com/en/webhooks-and-events/webhooks/about-webhooks)
