=== Zoom Link Form ===
Contributors: xAI
Tags: zoom, form, captcha, secure link
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to generate secure Zoom links via form submission with captcha verification.

== Description ==
This plugin allows users to submit an email address through a form with captcha verification to receive a secure, time-based Zoom link. The plugin stores submission data (email, IP, device, token) with a customizable token validity period, supports exporting to CSV, and allows deleting stored data.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/zoom-link-form` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure the plugin settings under Settings > Zoom Link Form.
4. Use the [zlf_form] shortcode to display the form on any page or post.

== Changelog ==
= 1.2.0 =
* Added token validation with customizable validity period (in minutes) via settings.
* Fixed "unable to find site" issue by using a redirect endpoint instead of appending token to Zoom URL.
* Updated documentation for token validation and link handling.

= 1.1.0 =
* Added database table to store email, IP address, device, and token.
* Added CSV export functionality for submission data.
* Added option to delete all submission data.
* Updated documentation to include new features.

= 1.0.0 =
* Initial release