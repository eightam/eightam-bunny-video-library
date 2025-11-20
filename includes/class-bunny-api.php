<?php
if (!defined('ABSPATH')) {
    exit;
}

class Eightam_Bunny_Video_Library_API {
    
    private $library_id;
    private $api_key;
    private $cdn_domain;
    private $api_base = 'https://video.bunnycdn.com';
    
    public function __construct() {
        $this->library_id = Eightam_Bunny_Video_Library_Settings::get_option('bunny_library_id');
        $this->api_key = Eightam_Bunny_Video_Library_Settings::get_option('bunny_api_key');
        $this->cdn_domain = Eightam_Bunny_Video_Library_Settings::get_option('bunny_cdn_domain');
        
        // Fallback to default if not set
        if (empty($this->cdn_domain)) {
            $this->cdn_domain = 'vz-f9bc59e8-c71.b-cdn.net';
        }
    }
    
    public function is_configured() {
        return !empty($this->library_id) && !empty($this->api_key);
    }
    
    public function get_library_id() {
        return $this->library_id;
    }
    
    public function get_videos($page = 1, $per_page = 50) {
        if (!$this->is_configured()) {
            return new WP_Error('not_configured', 'Bunny.net credentials not configured');
        }
        
        $url = $this->api_base . '/library/' . $this->library_id . '/videos';
        $params = array(
            'page' => $page,
            'itemsPerPage' => $per_page,
            'orderBy' => 'date'
        );
        
        return $this->make_request($url, 'GET', $params);
    }
    
    public function get_video_details($video_id) {
        if (!$this->is_configured()) {
            return new WP_Error('not_configured', 'Bunny.net credentials not configured');
        }
        
        $url = $this->api_base . '/library/' . $this->library_id . '/videos/' . $video_id;
        
        return $this->make_request($url, 'GET');
    }
    
    public function get_direct_play_url($video_id) {
        if (!$this->is_configured()) {
            return '';
        }
        
        return 'https://iframe.mediadelivery.net/play/' . $this->library_id . '/' . $video_id;
    }
    
    public function get_iframe_embed_code($video_id, $options = array()) {
        if (!$this->is_configured()) {
            return '';
        }
        
        // Default options
        $defaults = array(
            'autoplay' => true,
            'loop' => true,
            'muted' => true,
            'show_player' => true
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Build query parameters
        $params = array(
            'autoplay' => $options['autoplay'] ? 'true' : 'false',
            'loop' => $options['loop'] ? 'true' : 'false',
            'muted' => $options['muted'] ? 'true' : 'false',
            'preload' => 'true',
            'responsive' => 'true'
        );
        
        $iframe_url = 'https://iframe.mediadelivery.net/embed/' . $this->library_id . '/' . $video_id . '?' . http_build_query($params);
        
        // If show_player is false, return the direct MP4 URL
        if (!$options['show_player']) {
            // Return direct MP4 URL for background video usage
            return $this->get_mp4_url($video_id, '720p');
        }
        
        return '<div class="bunny-player"><iframe 
    src="' . esc_url($iframe_url) . '"
    loading="lazy"
    allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"
    allowfullscreen
  ></iframe></div>';
    }
    
    public function get_thumbnail_url($video_id, $thumbnail_file_name = null) {
        if (!$this->is_configured()) {
            return '';
        }
        
        // Get video details to extract actual URLs from API response
        $video_details = $this->get_video_details($video_id);
        
        if (is_wp_error($video_details) || empty($video_details)) {
            return '';
        }
        
        // Use the actual thumbnail URL from API response
        if (isset($video_details['thumbnailUrl'])) {
            return $video_details['thumbnailUrl'];
        }
        
        // Fallback to constructing from pull zone URL if available
        return $this->construct_thumbnail_url($video_id, $video_details, $thumbnail_file_name);
    }
    
    public function get_webp_preview_url($video_id, $preview_file_name = null) {
        if (!$this->is_configured()) {
            return '';
        }
        
        // Get video details to extract actual URLs from API response
        $video_details = $this->get_video_details($video_id);
        
        if (is_wp_error($video_details) || empty($video_details)) {
            return '';
        }
        
        // Use the actual preview URL from API response
        if (isset($video_details['previewUrl'])) {
            return $video_details['previewUrl'];
        }
        
        // Fallback to constructing from pull zone URL if available
        return $this->construct_preview_url($video_id, $video_details, $preview_file_name);
    }
    
    public function get_mp4_url($video_id, $quality = '720p') {
        if (!$this->is_configured()) {
            return '';
        }
        
        // Get video details to extract pull zone URL
        $video_details = $this->get_video_details($video_id);
        
        if (is_wp_error($video_details) || empty($video_details)) {
            return '';
        }
        
        $pull_zone_url = $this->get_pull_zone_url_from_response($video_details);
        
        if (empty($pull_zone_url)) {
            return '';
        }
        
        return 'https://' . $pull_zone_url . '/' . $video_id . '/play_' . $quality . '.mp4';
    }
    
    private function construct_thumbnail_url($video_id, $video_details, $thumbnail_file_name = null) {
        // Construct thumbnail URL from pull zone URL if available
        $pull_zone_url = $this->get_pull_zone_url_from_response($video_details);
        
        if (empty($pull_zone_url)) {
            return '';
        }
        
        if ($thumbnail_file_name) {
            return 'https://' . $pull_zone_url . '/' . $video_id . '/' . $thumbnail_file_name;
        }
        
        return 'https://' . $pull_zone_url . '/' . $video_id . '/thumbnail.jpg';
    }
    
    private function construct_preview_url($video_id, $video_details, $preview_file_name = null) {
        // Construct preview URL from pull zone URL if available
        $pull_zone_url = $this->get_pull_zone_url_from_response($video_details);
        
        if (empty($pull_zone_url)) {
            return '';
        }
        
        if ($preview_file_name) {
            return 'https://' . $pull_zone_url . '/' . $video_id . '/' . $preview_file_name;
        }
        
        return 'https://' . $pull_zone_url . '/' . $video_id . '/preview.webp';
    }
    
    private function get_pull_zone_url_from_response($video_details) {
        // Extract the pull zone URL from the video details response
        if (isset($video_details['pullZoneUrl'])) {
            return $video_details['pullZoneUrl'];
        }
        
        // Fallback to extracting from available URLs
        if (isset($video_details['thumbnailUrl'])) {
            $thumbnail_url = $video_details['thumbnailUrl'];
            $parts = parse_url($thumbnail_url);
            if (isset($parts['host'])) {
                return $parts['host'];
            }
        }
        
        if (isset($video_details['previewUrl'])) {
            $preview_url = $video_details['previewUrl'];
            $parts = parse_url($preview_url);
            if (isset($parts['host'])) {
                return $parts['host'];
            }
        }
        
        return $this->cdn_domain; // Fallback to configured CDN domain
    }
    
    private function make_request($url, $method = 'GET', $params = array()) {
        $args = array(
            'method' => $method,
            'headers' => array(
                'AccessKey' => $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );
        
        if ($method === 'GET' && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif (!empty($params)) {
            $args['body'] = wp_json_encode($params);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error(
                'bunny_api_error',
                isset($data['message']) ? $data['message'] : 'API request failed'
            );
        }
        
        return $data;
    }
    
    public function upload_video($file_path, $file_name) {
        if (!$this->is_configured()) {
            return new WP_Error('not_configured', 'Bunny.net credentials not configured');
        }
        
        // First, create a video object
        $create_url = $this->api_base . '/library/' . $this->library_id . '/videos';
        
        $create_data = array(
            'title' => pathinfo($file_name, PATHINFO_FILENAME),
            'collectionId' => '' // Optional: can be set if you want to organize videos
        );
        
        $create_response = $this->make_request($create_url, 'POST', $create_data);
        
        if (is_wp_error($create_response)) {
            return $create_response;
        }
        
        if (!isset($create_response['guid'])) {
            return new WP_Error('api_error', 'Failed to create video object');
        }
        
        $video_id = $create_response['guid'];
        
        // Get upload URL
        $upload_url = $this->api_base . '/library/' . $this->library_id . '/videos/' . $video_id;
        
        // Read file content
        $file_content = file_get_contents($file_path);
        if ($file_content === false) {
            return new WP_Error('file_error', 'Failed to read file');
        }
        
        // Upload video file
        $upload_args = array(
            'method' => 'PUT',
            'headers' => array(
                'AccessKey' => $this->api_key,
                'Content-Type' => 'application/octet-stream'
            ),
            'body' => $file_content,
            'timeout' => 300 // 5 minutes for large files
        );
        
        $upload_response = wp_remote_request($upload_url, $upload_args);
        
        if (is_wp_error($upload_response)) {
            return $upload_response;
        }
        
        $response_code = wp_remote_retrieve_response_code($upload_response);
        
        if ($response_code === 200 || $response_code === 201) {
            return array(
                'success' => true,
                'videoId' => $video_id,
                'url' => $this->get_direct_play_url($video_id),
                'message' => 'Video uploaded successfully'
            );
        } else {
            return new WP_Error('upload_error', 'Failed to upload video');
        }
    }
}
