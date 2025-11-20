<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GitHub Updater Class
 * Enables automatic updates from a GitHub repository
 */
class Eightam_Bunny_Video_Library_GitHub_Updater {
    
    private $plugin_file;
    private $plugin_slug;
    private $github_username;
    private $github_repo;
    private $github_token; // Optional: for private repos or higher rate limits
    private $plugin_data;
    
    /**
     * Constructor
     * 
     * @param string $plugin_file Full path to the main plugin file
     * @param string $github_username GitHub username or organization
     * @param string $github_repo GitHub repository name
     * @param string $github_token Optional GitHub personal access token
     */
    public function __construct($plugin_file, $github_username, $github_repo, $github_token = '') {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;
        $this->github_token = $github_token;
        
        // Get plugin data
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->plugin_data = get_plugin_data($plugin_file, false, false);
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        
        // Enable auto-updates support
        add_filter('auto_update_plugin', array($this, 'enable_auto_update'), 10, 2);
    }
    
    /**
     * Enable auto-updates for this plugin
     */
    public function enable_auto_update($update, $item) {
        // Check if this is our plugin
        if (isset($item->plugin) && $item->plugin === $this->plugin_slug) {
            // Allow auto-updates to be toggled by user
            return $update;
        }
        return $update;
    }
    
    /**
     * Check for updates from GitHub
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get latest release from GitHub
        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($this->plugin_data['Version'], $remote_version, '<')) {
            $plugin_info = $this->get_plugin_info();
            
            if ($plugin_info) {
                $transient->response[$this->plugin_slug] = (object) array(
                    'slug' => dirname($this->plugin_slug),
                    'plugin' => $this->plugin_slug,
                    'new_version' => $remote_version,
                    'url' => $this->plugin_data['PluginURI'],
                    'package' => $plugin_info->download_url,
                    'tested' => isset($plugin_info->tested) ? $plugin_info->tested : '',
                    'requires_php' => isset($plugin_info->requires_php) ? $plugin_info->requires_php : '',
                    'icons' => array(),
                );
            }
        } else {
            // No update available, but add to no_update to show auto-update toggle
            $transient->no_update[$this->plugin_slug] = (object) array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $this->plugin_data['Version'],
                'url' => $this->plugin_data['PluginURI'],
                'package' => '',
                'tested' => get_bloginfo('version'),
                'requires_php' => '7.4',
                'icons' => array(),
            );
        }
        
        return $transient;
    }
    
    /**
     * Get remote version from GitHub
     */
    private function get_remote_version() {
        $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        
        $response = $this->github_api_request($api_url);
        
        if (is_wp_error($response) || !isset($response->tag_name)) {
            return false;
        }
        
        // Remove 'v' prefix if present (e.g., v1.0.3 -> 1.0.3)
        return ltrim($response->tag_name, 'v');
    }
    
    /**
     * Get plugin info from GitHub
     */
    private function get_plugin_info() {
        $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        
        $response = $this->github_api_request($api_url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return (object) array(
            'name' => $this->plugin_data['Name'],
            'slug' => dirname($this->plugin_slug),
            'version' => ltrim($response->tag_name, 'v'),
            'author' => $this->plugin_data['Author'],
            'homepage' => $this->plugin_data['PluginURI'],
            'download_url' => $response->zipball_url,
            'sections' => array(
                'description' => $this->plugin_data['Description'],
                'changelog' => isset($response->body) ? $response->body : 'See GitHub releases for changelog.'
            ),
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.4',
        );
    }
    
    /**
     * Provide plugin information for the update screen
     */
    public function plugin_info($false, $action, $args) {
        if ($action !== 'plugin_information') {
            return $false;
        }
        
        if (!isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $false;
        }
        
        return $this->get_plugin_info();
    }
    
    /**
     * After installation, rename the folder to match the plugin slug
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        $plugin_folder = WP_PLUGIN_DIR . '/' . dirname($this->plugin_slug);
        $wp_filesystem->move($result['destination'], $plugin_folder);
        $result['destination'] = $plugin_folder;
        
        // Re-activate plugin if it was active
        if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === $this->plugin_slug) {
            activate_plugin($this->plugin_slug);
        }
        
        return $result;
    }
    
    /**
     * Make GitHub API request
     */
    private function github_api_request($url) {
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            )
        );
        
        // Add authorization header if token is provided
        if (!empty($this->github_token)) {
            $args['headers']['Authorization'] = 'token ' . $this->github_token;
        }
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('github_error', 'GitHub API error: ' . (isset($data->message) ? $data->message : 'Unknown error'));
        }
        
        return $data;
    }
}
