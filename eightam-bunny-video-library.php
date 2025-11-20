<?php
/**
 * Plugin Name: 8am Bunny Video Library
 * Plugin URI: https://github.com/eightam/eightam-bunny-video-library
 * Description: Integration with Bunny.net video library for WordPress
 * Version: 1.1.1
 * Author: 8am GmbH
 * Author URI: https://8am.ch
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: eightam-bunny-video-library
 * GitHub Plugin URI: https://github.com/eightam/eightam-bunny-video-library
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION', '1.1.1');
define('EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-bunny-api.php';
require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-admin-pages.php';
require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-settings.php';
require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-blocks.php';
require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-github-updater.php';

// Initialize the plugin
add_action('plugins_loaded', 'eightam_bunny_video_library_init');

function eightam_bunny_video_library_init() {
    new Eightam_Bunny_Video_Library();
}

// Initialize GitHub updater for automatic updates
if (is_admin()) {
    new Eightam_Bunny_Video_Library_GitHub_Updater(
        __FILE__,
        'eightam',
        'eightam-bunny-video-library',
        '' // Optional: Add GitHub personal access token for private repos or higher rate limits
    );
}

class Eightam_Bunny_Video_Library {
    
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->load_classes();
        $this->init_hooks();
    }
    
    private function define_constants() {
        // Define constants here
    }
    
    private function includes() {
        require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-bunny-api.php';
        require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-admin-pages.php';
        require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-settings.php';
        require_once EIGHTAM_BUNNY_VIDEO_LIBRARY_PLUGIN_DIR . 'includes/class-blocks.php';
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_bunny_video_upload', array($this, 'handle_video_upload'));
        add_action('wp_ajax_nopriv_bunny_video_upload', array($this, 'handle_video_upload'));
    }
    
    private function load_classes() {
        new Eightam_Bunny_Video_Library_Settings();
        new Eightam_Bunny_Video_Library_Admin_Pages();
        
        // Initialize blocks on init hook to avoid translation loading issues
        add_action('init', array($this, 'init_blocks'));
    }
    
    public function init_blocks() {
        new Eightam_Bunny_Video_Library_Blocks();
    }
    
    public function add_admin_menu() {
        // Let the Settings class handle the main menu
        // Admin pages are handled by Eightam_Bunny_Video_Library_Admin_Pages
    }
    
    public function enqueue_admin_scripts($hook) {
        $allowed_hooks = array('media_page_eightam-bunny-videos', 'media_page_eightam-bunny-upload');
        
        if (!in_array($hook, $allowed_hooks)) {
            return;
        }
        
        wp_enqueue_style(
            'eightam-bunny-admin',
            plugins_url('assets/css/admin.css', __FILE__),
            array(),
            EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION
        );
        
        wp_enqueue_script(
            'eightam-bunny-admin',
            plugins_url('assets/js/admin.js', __FILE__),
            array('jquery'),
            EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION,
            true
        );
        
        wp_localize_script('eightam-bunny-admin', 'bunnyAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'copyNonce' => wp_create_nonce('eightam_copy_to_clipboard')
        ));
    }
    
    public function handle_video_upload() {
        check_ajax_referer('bunny_video_upload', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => 'No file uploaded'));
            return;
        }
        
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'File upload error: ' . $file['error']));
            return;
        }
        
        // Check if it's a video file
        $allowed_types = array('video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm', 'video/mkv');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => 'Invalid file type. Please upload a video file.'));
            return;
        }
        
        // Check file size (500MB limit)
        $max_size = 500 * 1024 * 1024; // 500MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(array('message' => 'File too large. Maximum size is 500MB.'));
            return;
        }
        
        $bunny_api = new Eightam_Bunny_Video_Library_API();
        $result = $bunny_api->upload_video($file['tmp_name'], $file['name']);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success($result);
        }
    }
}
