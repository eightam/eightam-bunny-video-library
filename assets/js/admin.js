jQuery(document).ready(function($) {
    'use strict';
    
    var EightamBunnyAdmin = {
        init: function() {
            this.bindEvents();
            this.setupHoverPreviews();
        },
        
        bindEvents: function() {
            $('.eightam-bunny-video-thumbnail').on('click', this.handleThumbnailClick.bind(this));
            $('.eightam-bunny-video-url textarea').on('click', this.handleUrlClick.bind(this));
            $('.eightam-bunny-video-options input[type="checkbox"]').on('change', this.handleOptionChange.bind(this));
        },
        
        setupHoverPreviews: function() {
            $('.eightam-bunny-video-thumbnail').each(function() {
                var $thumbnail = $(this);
                var previewUrl = $thumbnail.data('preview-url');
                var originalImg = $thumbnail.find('img').attr('src');
                
                if (previewUrl) {
                    // Preload the preview image
                    var previewImg = new Image();
                    previewImg.src = previewUrl;
                    
                    // Store references
                    $thumbnail.data('original-src', originalImg);
                    $thumbnail.data('preview-img', previewImg);
                    
                    $thumbnail.hover(
                        function() {
                            // Mouse enter - show preview
                            var $img = $(this).find('img');
                            $img.attr('src', previewUrl);
                        },
                        function() {
                            // Mouse leave - show original
                            var $img = $(this).find('img');
                            $img.attr('src', $(this).data('original-src'));
                        }
                    );
                }
            });
        },
        
        handleThumbnailClick: function(e) {
            e.preventDefault();
            
            var $item = $(e.currentTarget).closest('.eightam-bunny-video-item');
            var embedCode = $item.data('embed-code');
            
            this.copyToClipboard(embedCode);
        },
        
        handleUrlClick: function(e) {
            e.preventDefault();
            
            var $textarea = $(e.currentTarget);
            var embedCode = $textarea.val();
            
            $textarea.select();
            this.copyToClipboard(embedCode);
        },
        
        handleOptionChange: function(e) {
            var $checkbox = $(e.currentTarget);
            var $item = $checkbox.closest('.eightam-bunny-video-item');
            var $textarea = $item.find('.eightam-bunny-video-url textarea');
            
            // Get video ID from the item
            var videoId = $item.data('video-id');
            
            // Get current option states
            var autoplay = $item.find('.bunny-option-autoplay').is(':checked');
            var loop = $item.find('.bunny-option-loop').is(':checked');
            var muted = $item.find('.bunny-option-muted').is(':checked');
            var showPlayer = $item.find('.bunny-option-show-player').is(':checked');
            
            // Generate new embed code
            var embedCode = this.generateEmbedCode(videoId, autoplay, loop, muted, showPlayer);
            
            // Update textarea
            $textarea.val(embedCode);
            
            // Update data attribute for thumbnail click
            $item.data('embed-code', embedCode);
        },
        
        generateEmbedCode: function(videoId, autoplay, loop, muted, showPlayer) {
            // Extract library ID from the page (we'll need to add this as a data attribute)
            var libraryId = $('.eightam-bunny-videos-grid').data('library-id');
            
            if (!libraryId) {
                // Fallback: try to extract from existing embed code
                var $firstItem = $('.eightam-bunny-video-item').first();
                var existingCode = $firstItem.find('textarea').val();
                var match = existingCode.match(/embed\/(\d+)\//);
                if (match) {
                    libraryId = match[1];
                }
            }
            
            if (!showPlayer) {
                // Return direct MP4 URL for background video usage
                // Get the MP4 URL from the data attribute
                var $item = $('.eightam-bunny-video-item[data-video-id="' + videoId + '"]');
                var mp4Url = $item.data('mp4-url');
                
                if (mp4Url) {
                    return mp4Url;
                }
                
                // Fallback: construct the URL (though this may not work without pull zone info)
                return 'https://iframe.mediadelivery.net/play/' + libraryId + '/' + videoId;
            }
            
            // Build query parameters
            var params = [];
            params.push('autoplay=' + (autoplay ? 'true' : 'false'));
            params.push('loop=' + (loop ? 'true' : 'false'));
            params.push('muted=' + (muted ? 'true' : 'false'));
            params.push('preload=true');
            params.push('responsive=true');
            
            var iframeUrl = 'https://iframe.mediadelivery.net/embed/' + libraryId + '/' + videoId + '?' + params.join('&');
            
            return '<div class="bunny-player"><iframe \n' +
                   '    src="' + iframeUrl + '"\n' +
                   '    loading="lazy"\n' +
                   '    allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture"\n' +
                   '    allowfullscreen\n' +
                   '  ></iframe></div>';
        },
        
        copyToClipboard: function(text) {
            // Modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    EightamBunnyAdmin.showCopyNotification();
                }).catch(function(err) {
                    console.error('Could not copy text: ', err);
                    EightamBunnyAdmin.fallbackCopyToClipboard(text);
                });
            } else {
                // Fallback for older browsers
                EightamBunnyAdmin.fallbackCopyToClipboard(text);
            }
        },
        
        fallbackCopyToClipboard: function(text) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    EightamBunnyAdmin.showCopyNotification();
                } else {
                    EightamBunnyAdmin.showCopyError();
                }
            } catch (err) {
                console.error('Fallback copy failed: ', err);
                EightamBunnyAdmin.showCopyError();
            }
            
            document.body.removeChild(textArea);
        },
        
        showCopyNotification: function() {
            var notification = $('<div class="eightam-bunny-copy-notification">Embed code copied to clipboard!</div>');
            $('body').append(notification);
            
            setTimeout(function() {
                notification.addClass('show');
            }, 100);
            
            setTimeout(function() {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        },
        
        showCopyError: function() {
            var notification = $('<div class="eightam-bunny-copy-notification" style="background: #d63638;">Failed to copy embed code. Please select and copy manually.</div>');
            $('body').append(notification);
            
            setTimeout(function() {
                notification.addClass('show');
            }, 100);
            
            setTimeout(function() {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 5000);
        }
    };
    
    // Initialize when document is ready
    EightamBunnyAdmin.init();
    
    // Handle AJAX copy to clipboard (if needed for server-side logging)
    $(document).on('click', '.eightam-bunny-copy-trigger', function(e) {
        e.preventDefault();
        
        var url = $(this).data('url');
        
        $.ajax({
            url: bunnyAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'eightam_copy_to_clipboard',
                url: url,
                nonce: bunnyAdmin.copyNonce
            },
            success: function(response) {
                if (response.success) {
                    EightamBunnyAdmin.showCopyNotification();
                }
            },
            error: function() {
                EightamBunnyAdmin.showCopyError();
            }
        });
    });
});
