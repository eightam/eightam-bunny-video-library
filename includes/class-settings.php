<?php
if (!defined('ABSPATH')) {
    exit;
}

class Eightam_Bunny_Video_Library_Settings {
    
    private $options;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }
    
    public function add_plugin_page() {
        add_options_page(
            'Bunny Video Library Settings',
            'Bunny Video Library',
            'manage_options',
            'eightam-bunny-video-settings',
            array($this, 'create_admin_page')
        );
    }
    
    public function create_admin_page() {
        $this->options = get_option('eightam_bunny_video_options');
        ?>
        <div class="wrap">
            <h1>Bunny Video Library Settings</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields('eightam_bunny_video_option_group');
                do_settings_sections('eightam-bunny-video-settings');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }
    
    public function page_init() {
        register_setting(
            'eightam_bunny_video_option_group',
            'eightam_bunny_video_options',
            array($this, 'sanitize')
        );
        
        add_settings_section(
            'setting_section_id',
            'Bunny.net Configuration',
            array($this, 'print_section_info'),
            'eightam-bunny-video-settings'
        );
        
        add_settings_field(
            'bunny_library_id',
            'Bunny.net Library ID',
            array($this, 'bunny_library_id_callback'),
            'eightam-bunny-video-settings',
            'setting_section_id'
        );
        
        add_settings_field(
            'bunny_api_key',
            'Bunny.net API Key',
            array($this, 'bunny_api_key_callback'),
            'eightam-bunny-video-settings',
            'setting_section_id'
        );
        
        add_settings_field(
            'bunny_cdn_domain',
            'Bunny.net CDN Domain',
            array($this, 'bunny_cdn_domain_callback'),
            'eightam-bunny-video-settings',
            'setting_section_id'
        );
    }
    
    public function sanitize($input) {
        $new_input = array();
        
        if (isset($input['bunny_library_id'])) {
            $new_input['bunny_library_id'] = sanitize_text_field($input['bunny_library_id']);
        }
        
        if (isset($input['bunny_api_key'])) {
            $new_input['bunny_api_key'] = sanitize_text_field($input['bunny_api_key']);
        }
        
        if (isset($input['bunny_cdn_domain'])) {
            $new_input['bunny_cdn_domain'] = sanitize_text_field($input['bunny_cdn_domain']);
        }
        
        return $new_input;
    }
    
    public function print_section_info() {
        print 'Enter your Bunny.net credentials below:';
    }
    
    public function bunny_library_id_callback() {
        printf(
            '<input type="text" id="bunny_library_id" name="eightam_bunny_video_options[bunny_library_id]" value="%s" />',
            isset($this->options['bunny_library_id']) ? esc_attr($this->options['bunny_library_id']) : ''
        );
    }
    
    public function bunny_api_key_callback() {
        printf(
            '<input type="password" id="bunny_api_key" name="eightam_bunny_video_options[bunny_api_key]" value="%s" />',
            isset($this->options['bunny_api_key']) ? esc_attr($this->options['bunny_api_key']) : ''
        );
    }
    
    public function bunny_cdn_domain_callback() {
        printf(
            '<input type="text" id="bunny_cdn_domain" name="eightam_bunny_video_options[bunny_cdn_domain]" value="%s" placeholder="e.g. vz-f9bc59e8-c71.b-cdn.net" style="width: 400px;" /><p class="description">Your Bunny.net CDN pull zone domain (found in your video library settings)</p>',
            isset($this->options['bunny_cdn_domain']) ? esc_attr($this->options['bunny_cdn_domain']) : ''
        );
    }
    
    public static function get_option($key) {
        $options = get_option('eightam_bunny_video_options');
        return isset($options[$key]) ? $options[$key] : '';
    }
}
