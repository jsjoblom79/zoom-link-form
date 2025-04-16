<?php
if (!defined('ABSPATH')) {
    exit;
}

class ZLF_Form {
    public function __construct() {
        add_action('wp_ajax_zlf_submit_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_zlf_submit_form', [$this, 'handle_form_submission']);
    }
    
    public function handle_form_submission() {
        check_ajax_referer('zlf_nonce', 'nonce');
        
        // Sanitize input
        $email = sanitize_email($_POST['email']);
        $captcha_response = sanitize_text_field($_POST['captcha_response']);
        
        // Validate input
        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Invalid email address']);
        }
        
        // Verify captcha
        $captcha_type = get_option('zlf_captcha_type', 'recaptcha');
        if (!$this->verify_captcha($captcha_response, $captcha_type)) {
            wp_send_json_error(['message' => 'Captcha verification failed']);
        }
        
        // Get client data
        $ip_address = $this->get_client_ip();
        $device = $this->get_client_device();
        $token = $this->generate_token();
        
        // Save submission data
        $this->save_submission($email, $ip_address, $device, $token);
        
        // Get Zoom link from settings
        $zoom_base_url = get_option('zlf_zoom_link', '');
        if (empty($zoom_base_url)) {
            wp_send_json_error(['message' => 'Zoom link not configured']);
        }
        
        // Generate secure Zoom link
        $zoom_link = add_query_arg(['token' => $token], $zoom_base_url);
        
        wp_send_json_success([
            'zoom_link' => esc_url($zoom_link),
            'message' => 'Zoom link generated successfully'
        ]);
    }
    
    private function verify_captcha($response, $type) {
        if ($type === 'recaptcha') {
            $secret_key = get_option('zlf_recaptcha_secret_key', '');
            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
            
            $response = wp_remote_post($verify_url, [
                'body' => [
                    'secret' => $secret_key,
                    'response' => $response,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ]
            ]);
            
            if (is_wp_error($response)) {
                return false;
            }
            
            $result = json_decode(wp_remote_retrieve_body($response));
            return isset($result->success) && $result->success;
        }
        
        return false;
    }
    
    private function generate_token() {
        $time = time();
        $secret = wp_generate_password(32, false);
        return hash_hmac('sha256', $time, $secret);
    }
    
    private function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return sanitize_text_field($ip);
    }
    
    private function get_client_device() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
        return sanitize_text_field($user_agent);
    }
    
    private function save_submission($email, $ip_address, $device, $token) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zlf_submissions';
        
        $wpdb->insert(
            $table_name,
            [
                'email' => $email,
                'ip_address' => $ip_address,
                'device' => $device,
                'token' => $token,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
    }
}
?>