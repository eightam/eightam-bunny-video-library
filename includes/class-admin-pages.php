<?php
if (!defined('ABSPATH')) {
    exit;
}

class Eightam_Bunny_Video_Library_Admin_Pages {
    
    private $api;
    
    public function __construct() {
        $this->api = new Eightam_Bunny_Video_Library_API();
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_eightam_copy_to_clipboard', array($this, 'handle_copy_to_clipboard'));
    }
    
    public function add_admin_menu() {
        add_media_page(
            'Bunny Videos',
            'Bunny Videos',
            'manage_options',
            'eightam-bunny-videos',
            array($this, 'render_videos_page')
        );
        
        add_media_page(
            'Upload to Bunny',
            'Upload to Bunny',
            'manage_options',
            'eightam-bunny-upload',
            array($this, 'render_upload_page')
        );
    }
    
    public function render_videos_page() {
        $bunny_api = new Eightam_Bunny_Video_Library_API();
        $videos = $bunny_api->get_videos();
        
        if (is_wp_error($videos)) {
            echo '<div class="wrap"><h1>Bunny Videos</h1><p>Error fetching videos: ' . esc_html($videos->get_error_message()) . '</p></div>';
            return;
        }
        
        if (!$this->api->is_configured()) {
            $this->render_configuration_notice();
            return;
        }
        
        include plugin_dir_path(__FILE__) . '../templates/videos-page.php';
    }
    
    public function render_upload_page() {
        $bunny_api = new Eightam_Bunny_Video_Library_API();
        $settings = new Eightam_Bunny_Video_Library_Settings();
        
        if (!$bunny_api->is_configured()) {
            echo '<div class="wrap"><h1>Upload to Bunny</h1><p>Please configure your Bunny.net credentials in the <a href="' . admin_url('options-general.php?page=eightam-bunny-settings') . '">settings page</a> first.</p></div>';
            return;
        }
        
        include plugin_dir_path(__FILE__) . '../templates/upload-page.php';
    }
    
    private function get_videos() {
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        return $this->api->get_videos($page, 50);
    }
    
    private function render_video_item($video) {
        $video_id = $video['guid'];
        $title = isset($video['title']) ? $video['title'] : 'Untitled';
        $thumbnail_url = $this->api->get_thumbnail_url($video_id, $video['thumbnailFileName'] ?? null);
        $webp_preview_url = $this->api->get_webp_preview_url($video_id, $video['previewFileName'] ?? null);
        $direct_play_url = $this->api->get_direct_play_url($video_id);
        ?>
        <div class="eightam-bunny-video-item" 
             data-video-id="<?php echo esc_attr($video_id); ?>"
             data-direct-url="<?php echo esc_attr($direct_play_url); ?>">
            
            <div class="eightam-bunny-video-thumbnail" 
                 data-preview-url="<?php echo esc_attr($webp_preview_url); ?>">
                <img src="<?php echo esc_url($thumbnail_url); ?>" 
                     alt="<?php echo esc_attr($title); ?>"
                     loading="lazy" />
                <div class="eightam-bunny-video-overlay">
                    <span class="dashicons dashicons-admin-page"></span>
                    <span>Copy URL</span>
                </div>
            </div>
            
            <div class="eightam-bunny-video-info">
                <h3><?php echo esc_html($title); ?></h3>
                <div class="eightam-bunny-video-url">
                    <input type="text" 
                           value="<?php echo esc_attr($direct_play_url); ?>" 
                           readonly 
                           onclick="this.select()" />
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_configuration_notice() {
        ?>
        <div class="wrap">
            <h1>Bunny Videos</h1>
            <div class="notice notice-warning">
                <p>
                    Bunny.net credentials are not configured. 
                    <a href="<?php echo admin_url('options-general.php?page=eightam-bunny-video-settings'); ?>">
                        Please configure your Bunny.net settings
                    </a>
                    to view your videos.
                </p>
            </div>
        </div>
        <?php
    }
    
    public function handle_copy_to_clipboard() {
        check_ajax_referer('eightam_copy_to_clipboard', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        if (empty($url)) {
            wp_send_json_error('Invalid URL');
        }
        
        wp_send_json_success(array('url' => $url));
    }
}
