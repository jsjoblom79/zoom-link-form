<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZLF_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_settings_page() {
        add_options_page(
            'Zoom Link Form Settings',
            'Zoom Link Form',
            'manage_options',
            'zlf-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        register_setting('zlf_settings_group', 'zlf_zoom_link', ['sanitize_callback' => 'esc_url_raw']);
        register_setting('zlf_settings_group', 'zlf_captcha_type', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('zlf_settings_group', 'zlf_recaptcha_site_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('zlf_settings_group', 'zlf_recaptcha_secret_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('zlf_settings_group', 'zlf_use_custom_css', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('zlf_settings_group', 'zlf_custom_css', ['sanitize_callback' => 'wp_kses_post']);
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Zoom Link Form Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('zlf_settings_group');
                do_settings_sections('zlf_settings_group');
                ?>
                <table class="form-table">
                    <tr>
                        <th>Zoom Link</th>
                        <td>
                            <input type="url" name="zlf_zoom_link" value="<?php echo esc_attr(get_option('zlf_zoom_link')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th>Captcha Type</th>
                        <td>
                            <select name="zlf_captcha_type">
                                <option value="recaptcha" <?php selected(get_option('zlf_captcha_type'), 'recaptcha'); ?>>Google reCAPTCHA</option>
                                <!-- Add more captcha options here -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>reCAPTCHA Site Key</th>
                        <td>
                            <input type="text" name="zlf_recaptcha_site_key" value="<?php echo esc_attr(get_option('zlf_recaptcha_site_key')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th>reCAPTCHA Secret Key</th>
                        <td>
                            <input type="text" name="zlf_recaptcha_secret_key" value="<?php echo esc_attr(get_option('zlf_recaptcha_secret_key')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th>Use Custom CSS</th>
                        <td>
                            <input type="checkbox" name="zlf_use_custom_css" value="1" <?php checked(get_option('zlf_use_custom_css'), 1); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th>Custom CSS</th>
                        <td>
                            <textarea name="zlf_custom_css" rows="10" class="large-text code"><?php echo esc_textarea(get_option('zlf_custom_css')); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
?>