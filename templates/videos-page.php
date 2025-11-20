<?php
if (!defined('ABSPATH')) {
    exit;
}

$bunny_api = new Eightam_Bunny_Video_Library_API();
$videos = $bunny_api->get_videos();
?>

<div class="wrap">
    <h1>Bunny Videos</h1>
    <p>Click on any video thumbnail to copy the iframe embed code to clipboard.</p>
    
    <?php if (is_wp_error($videos)) : ?>
        <div class="notice notice-error">
            <p>Error: <?php echo esc_html($videos->get_error_message()); ?></p>
        </div>
    <?php elseif (empty($videos['items'])) : ?>
        <div class="notice notice-info">
            <p>No videos found in your Bunny.net library.</p>
        </div>
    <?php else : ?>
        <div class="eightam-bunny-videos-grid" data-library-id="<?php echo esc_attr($bunny_api->get_library_id()); ?>">
            <?php foreach ($videos['items'] as $video) : ?>
                <?php 
                $video_id = $video['guid'];
                $title = isset($video['title']) ? $video['title'] : 'Untitled';
                $thumbnail_url = $bunny_api->get_thumbnail_url($video_id, $video['thumbnailFileName'] ?? null);
                $webp_preview_url = $bunny_api->get_webp_preview_url($video_id, $video['previewFileName'] ?? null);
                $iframe_embed_code = $bunny_api->get_iframe_embed_code($video_id);
                $mp4_url = $bunny_api->get_mp4_url($video_id, '720p');
                ?>
                
                <div class="eightam-bunny-video-item" 
                     data-video-id="<?php echo esc_attr($video_id); ?>"
                     data-embed-code="<?php echo esc_attr($iframe_embed_code); ?>"
                     data-mp4-url="<?php echo esc_attr($mp4_url); ?>">
                    
                    <div class="eightam-bunny-video-thumbnail" 
                         data-preview-url="<?php echo esc_attr($webp_preview_url); ?>">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             alt="<?php echo esc_attr($title); ?>"
                             loading="lazy" />
                        <div class="eightam-bunny-video-overlay">
                            <span class="dashicons dashicons-admin-page"></span>
                            <span>Copy Embed Code</span>
                        </div>
                    </div>
                    
                    <div class="eightam-bunny-video-info">
                        <h3 class="eightam-bunny-video-title"><?php echo esc_html($title); ?></h3>
                        
                        <div class="eightam-bunny-video-options">
                            <label>
                                <input type="checkbox" class="bunny-option-autoplay" checked>
                                <span>Autoplay</span>
                            </label>
                            <label>
                                <input type="checkbox" class="bunny-option-loop" checked>
                                <span>Loop</span>
                            </label>
                            <label>
                                <input type="checkbox" class="bunny-option-muted" checked>
                                <span>Muted</span>
                            </label>
                            <label>
                                <input type="checkbox" class="bunny-option-show-player" checked>
                                <span>Show Player</span>
                            </label>
                        </div>
                        
                        <div class="eightam-bunny-video-url">
                            <textarea readonly rows="5"><?php echo esc_textarea($iframe_embed_code); ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
