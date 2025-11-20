<?php
if (!defined('ABSPATH')) {
    exit;
}

class Eightam_Bunny_Video_Library_Blocks {
    
    public function __construct() {
        // Register blocks immediately since we're already on init hook
        $this->register_blocks();
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function register_blocks() {
        // Register the Bunny Video block
        register_block_type('eightam-bunny/video', array(
            'render_callback' => array($this, 'render_video_block'),
            'attributes' => array(
                'videoId' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'autoplay' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'loop' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'muted' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'aspectRatio' => array(
                    'type' => 'string',
                    'default' => '16:9'
                )
            )
        ));
    }
    
    public function render_video_block($attributes) {
        $video_id = isset($attributes['videoId']) ? sanitize_text_field($attributes['videoId']) : '';
        
        if (empty($video_id)) {
            return '<div class="bunny-video-block-placeholder">Please select a video from the block settings.</div>';
        }
        
        $autoplay = isset($attributes['autoplay']) ? (bool) $attributes['autoplay'] : false;
        $loop = isset($attributes['loop']) ? (bool) $attributes['loop'] : false;
        $muted = isset($attributes['muted']) ? (bool) $attributes['muted'] : false;
        $aspect_ratio = isset($attributes['aspectRatio']) ? sanitize_text_field($attributes['aspectRatio']) : '16:9';
        
        $bunny_api = new Eightam_Bunny_Video_Library_API();
        
        $options = array(
            'autoplay' => $autoplay,
            'loop' => $loop,
            'muted' => $muted,
            'show_player' => true
        );
        
        $embed_code = $bunny_api->get_iframe_embed_code($video_id, $options);
        
        // Apply aspect ratio styling
        $aspect_ratio_style = $this->get_aspect_ratio_style($aspect_ratio);
        
        return '<div class="bunny-video-block" style="' . esc_attr($aspect_ratio_style) . '">' . $embed_code . '</div>';
    }
    
    private function get_aspect_ratio_style($aspect_ratio) {
        // Convert aspect ratio to padding-bottom percentage
        $ratios = array(
            '16:9' => '56.25%',   // 9/16 * 100
            '4:3' => '75%',       // 3/4 * 100
            '1:1' => '100%',      // 1/1 * 100
            '21:9' => '42.857%',  // 9/21 * 100
            '9:16' => '177.778%'  // 16/9 * 100
        );
        
        $padding = isset($ratios[$aspect_ratio]) ? $ratios[$aspect_ratio] : $ratios['16:9'];
        
        return 'position: relative; padding-bottom: ' . $padding . '; height: 0; overflow: hidden;';
    }
    
    public function enqueue_block_editor_assets() {
        // Enqueue block editor script
        wp_enqueue_script(
            'eightam-bunny-video-block',
            plugins_url('blocks/bunny-video/block.js', dirname(__FILE__)),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch'),
            EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION,
            true
        );
        
        // Set script translations
        wp_set_script_translations(
            'eightam-bunny-video-block',
            'eightam-bunny-video-library'
        );
        
        // Pass video list to JavaScript
        $bunny_api = new Eightam_Bunny_Video_Library_API();
        $videos = $bunny_api->get_videos(1, 100);
        
        $video_list = array();
        $video_thumbnails = array();
        if (!is_wp_error($videos) && !empty($videos['items'])) {
            foreach ($videos['items'] as $video) {
                $video_id = sanitize_text_field($video['guid']);
                $video_list[] = array(
                    'value' => $video_id,
                    'label' => isset($video['title']) ? sanitize_text_field($video['title']) : 'Untitled'
                );
                // Store thumbnail URLs (sanitized)
                $thumbnail_url = $bunny_api->get_thumbnail_url($video_id, $video['thumbnailFileName'] ?? null);
                $video_thumbnails[$video_id] = esc_url_raw($thumbnail_url);
            }
        }
        
        wp_localize_script('eightam-bunny-video-block', 'bunnyVideoData', array(
            'videos' => $video_list,
            'libraryId' => $bunny_api->get_library_id(),
            'thumbnails' => $video_thumbnails
        ));
        
        // Enqueue block editor styles
        wp_enqueue_style(
            'eightam-bunny-video-block-editor',
            plugins_url('blocks/bunny-video/editor.css', dirname(__FILE__)),
            array('wp-edit-blocks'),
            EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION
        );
    }
    
    public function enqueue_frontend_assets() {
        // Enqueue frontend styles
        wp_enqueue_style(
            'eightam-bunny-video-block',
            plugins_url('blocks/bunny-video/style.css', dirname(__FILE__)),
            array(),
            EIGHTAM_BUNNY_VIDEO_LIBRARY_VERSION
        );
    }
}
