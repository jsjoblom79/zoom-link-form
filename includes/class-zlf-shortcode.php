<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZLF_Shortcode {
    public function __construct() {
        add_shortcode('zlf_form', [$this, 'render_form']);
        add_action('wp_head', [$this, 'add_custom_css']);
    }
    
    public function render_form($atts) {
        $atts = shortcode_atts([
            'class' => 'zlf-form'
        ], $atts);
        
        $site_key = get_option('zlf_recaptcha_site_key', '');
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <form id="zlf-form" method="post">
                <div class="zlf-form-group">
                    <label for="zlf-email">Email Address</label>
                    <input type="email" id="zlf-email" name="email" required>
                </div>
                <div class="zlf-form-group">
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                </div>
                <button type="submit">Get Zoom Link</button>
            </form>
            <div id="zlf-response" style="display: none;"></div>
        </div>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php
        return ob_get_clean();
    }
    
    public function add_custom_css() {
        if (get_option('zlf_use_custom_css')) {
            $custom_css = get_option('zlf_custom_css', '');
            if (!empty($custom_css)) {
                echo '<style type="text/css">' . wp_kses_post($custom_css) . '</style>';
            }
        }
    }
}
?>