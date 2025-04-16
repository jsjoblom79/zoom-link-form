# Zoom Link Form Plugin Documentation

## Overview

The Zoom Link Form plugin allows WordPress users to create a form that collects an email address, verifies it with a captcha, and generates a secure Zoom link with a time-based token. The plugin stores submission data (email, IP address, device, token, and timestamp), supports token validation with a customizable validity period, and provides options to export data as CSV and delete stored data.

## Installation

1. Download the plugin zip file.
2. In your WordPress admin panel, navigate to **Plugins > Add New > Upload Plugin**.
3. Upload the zip file and click **Install Now**.
4. Activate the plugin from the **Plugins** menu.
5. Configure the plugin settings under **Settings > Zoom Link Form**.

## Configuration

1. **Zoom Link**: Enter the base Zoom meeting URL (e.g., `https://us02web.zoom.us/j/12345678901`).
2. **Captcha Type**: Select the captcha type (currently supports Google reCAPTCHA).
3. **reCAPTCHA Keys**: Obtain a site key and secret key from [Google reCAPTCHA](https://www.google.com/recaptcha) and enter them in the settings.
4. **Token Validity (Minutes)**: Set the duration (in minutes) for which the Zoom link is valid (default: 60 minutes).
5. **Custom CSS**: Enable custom CSS and add your styles to override the default form appearance.

## Usage

1. Add the shortcode `[zlf_form]` to any page or post to display the form.
2. Optionally, customize the form's appearance by adding a custom class: `[zlf_form class="your-custom-class"]`.
3. Users submit their email and complete the captcha to receive a Zoom link (e.g., `yourdomain.com/zlf-redirect/123`).
4. Clicking the link redirects to the Zoom meeting if the token is valid; otherwise, an error is displayed.

## Data Management

- **Storage**: Each form submission stores the email, IP address, device (user agent), token, and timestamp in the `wp_zlf_submissions` table.
- **Token Validation**: Links are valid for the duration set in "Token Validity (Minutes)". Expired or invalid links display an error.
- **Export**: In **Settings > Zoom Link Form**, click "Export Submissions as CSV" to download a CSV file containing all submission data.
- **Delete**: Click "Delete All Submission Data" to clear the table (requires confirmation).

## Styling

- The plugin includes default styles in `assets/css/zlf-styles.css`.
- Enable custom CSS in the settings to add your own styles, which will be applied site-wide.
- Alternatively, use the shortcode's `class` attribute to apply page-specific styles.

## Troubleshooting

- **Form not displaying**: Ensure the shortcode is correctly placed and the plugin is activated.
- **Captcha errors**: Verify that the reCAPTCHA keys are correct and the site is accessible to Google's servers.
- **Zoom link errors**: Ensure a valid Zoom URL is entered in the settings. If "unable to find site" occurs, verify the Zoom URL format.
- **Expired link**: Check the "Token Validity (Minutes)" setting and ensure the link is accessed within the validity period.
- **Export/Delete issues**: Ensure you have admin permissions and check the PHP error log for issues.

## Support

For issues or feature requests, contact the plugin developer through the WordPress plugin repository or the xAI support page.