<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZLF_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_zlf_export_data', [$this, 'export_data']);
        add_action('wp_ajax_zlf_delete_data', [$this, 'delete_data']);
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
        register_setting('zlf_settings_group', 'zlf_token_validity', ['sanitize_callback' => 'absint']);
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
                            <p class="description">Enter the base Zoom meeting URL (e.g., https://us02web.zoom.us/j/12345678901).</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Captcha Type</th>
                        <td>
                            <select name="zlf_captcha_type">
                                <option value="recaptcha" <?php selected(get_option('zlf_captcha_type'), 'recaptcha'); ?>>Google reCAPTCHA</option>
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
                        <th>Token Validity (Minutes)</th>
                        <td>
                            <input type="number" name="zlf_token_validity" value="<?php echo esc_attr(get_option('zlf_token_validity', 60)); ?>" min="1" class="regular-text" />
                            <p class="description">Set the duration (in minutes) for which the Zoom link is valid.</p>
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
            <h2>Data Management</h2>
            <p>
                <button id="zlf-export-data" class="button button-secondary">Export Submissions as CSV</button>
                <button id="zlf-delete-data" class="button button-secondary">Delete All Submission Data</button>
            </p>
            <script>
                jQuery(document).ready(function($) {
                    $('#zlf-export-data').on('click', function(e) {
                        e.preventDefault();
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'zlf_export_data',
                                nonce: '<?php echo wp_create_nonce('zlf_export_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    window.location.href = response.data.url;
                                } else {
                                    alert('Export failed: ' + response.data.message);
                                }
                            }
                        });
                    });
                    
                    $('#zlf-delete-data').on('click', function(e) {
                        e.preventDefault();
                        if (confirm('Are you sure you want to delete all submission data? This cannot be undone.')) {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'zlf_delete_data',
                                    nonce: '<?php echo wp_create_nonce('zlf_delete_nonce'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        alert('Submission data deleted successfully.');
                                    } else {
                                        alert('Deletion failed: ' + response.data.message);
                                    }
                                }
                            });
                        }
                    });
                });
            </script>
        </div>
        <?php
    }
    
    public function export_data() {
        check_ajax_referer('zlf_export_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zlf_submissions';
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        
        if (empty($results)) {
            wp_send_json_error(['message' => 'No data to export']);
        }
        
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/zlf-submissions-' . date('Y-m-d-H-i-s') . '.csv';
        $file_url = $upload_dir['baseurl'] . '/zlf-submissions-' . date('Y-m-d-H-i-s') . '.csv';
        
        $file = fopen($file_path, 'w');
        fputcsv($file, ['ID', 'Email', 'IP Address', 'Device', 'Token', 'Created At']);
        
        foreach ($results as $row) {
            fputcsv($file, [
                $row['id'],
                $row['email'],
                $row['ip_address'],
                $row['device'],
                $row['token'],
                $row['created_at']
            ]);
        }
        
        fclose($file);
        
        wp_send_json_success(['url' => $file_url]);
    }
    
    public function delete_data() {
        check_ajax_referer('zlf_delete_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'zlf_submissions';
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        if ($result !== false) {
            wp_send_json_success(['message' => 'Data deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete data']);
        }
    }
}
?>