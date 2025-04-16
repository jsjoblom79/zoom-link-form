<?php
/**
 * Plugin Name: Zoom Link Form
 * Description: A WordPress plugin to generate secure Zoom links via form submission with captcha
 * Version: 1.2.0
 * Author: xAI, Justin Sjoblom
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('ZLF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ZLF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ZLF_PLUGIN_DIR . 'includes/class-zlf-form.php';
require_once ZLF_PLUGIN_DIR . 'includes/class-zlf-settings.php';
require_once ZLF_PLUGIN_DIR . 'includes/class-zlf-shortcode.php';

// Initialize plugin
class ZLF_Plugin {
    public function __construct() {
        // Initialize classes
        new ZLF_Form();
        new ZLF_Settings();
        new ZLF_Shortcode();
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Activation hook to create database table
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        // Add rewrite rule and query var for redirect endpoint
        add_action('init', [$this, 'add_rewrite_rule']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_redirect']);
    }
    
    public function enqueue_assets() {
        wp_enqueue_style('zlf-styles', ZLF_PLUGIN_URL . 'assets/css/zlf-styles.css', [], '1.2.0');
        wp_enqueue_script('zlf-script', ZLF_PLUGIN_URL . 'assets/js/zlf-script.js', ['jquery'], '1.2.0', true);
        
        // Localize script for AJAX
        wp_localize_script('zlf-script', 'zlfAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zlf_nonce')
        ]);
    }
    
    public function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'zlf_submissions';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            ip_address varchar(45) NOT NULL,
            device text NOT NULL,
            token text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Ensure rewrite rules are flushed
        $this->add_rewrite_rule();
        flush_rewrite_rules();
    }
    
    public function add_rewrite_rule() {
        add_rewrite_rule(
            '^zlf-redirect/([0-9]+)/?$',
            'index.php?zlf_submission_id=$matches[1]',
            'top'
        );
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'zlf_submission_id';
        return $vars;
    }
    
    public function handle_redirect() {
        $submission_id = get_query_var('zlf_submission_id');
        if ($submission_id) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'zlf_submissions';
            $submission = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $submission_id));
            
            if ($submission) {
                $validity_minutes = get_option('zlf_token_validity', 10); // Default 10 minutes
                $created_at = strtotime($submission->created_at);
                $now = current_time('timestamp');
                
                if (($now - $created_at) <= ($validity_minutes * 60)) {
                    $zoom_url = get_option('zlf_zoom_link', '');
                    if (!empty($zoom_url)) {
                        wp_redirect($zoom_url);
                        exit;
                    }
                }
            }
            
            // If invalid or expired, show error
            wp_die('Invalid or expired Zoom link.', 'Zoom Link Form', ['response' => 403]);
        }
    }
}

new ZLF_Plugin();