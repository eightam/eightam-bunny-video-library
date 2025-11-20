(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl, ToggleControl } = wp.components;
    const { createElement: el } = wp.element;
    const { __ } = wp.i18n;
    
    registerBlockType('eightam-bunny/video', {
        title: __('Bunny Video', 'eightam-bunny-video-library'),
        icon: 'video-alt3',
        category: 'media',
        attributes: {
            videoId: {
                type: 'string',
                default: ''
            },
            autoplay: {
                type: 'boolean',
                default: false
            },
            loop: {
                type: 'boolean',
                default: false
            },
            muted: {
                type: 'boolean',
                default: false
            },
            aspectRatio: {
                type: 'string',
                default: '16:9'
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { videoId, autoplay, loop, muted, aspectRatio } = attributes;
            
            // Get video list from localized data
            const videos = window.bunnyVideoData ? window.bunnyVideoData.videos : [];
            const libraryId = window.bunnyVideoData ? window.bunnyVideoData.libraryId : '';
            const thumbnails = window.bunnyVideoData ? window.bunnyVideoData.thumbnails : {};
            
            // Add empty option at the beginning
            const videoOptions = [
                { value: '', label: __('Select a video...', 'eightam-bunny-video-library') },
                ...videos
            ];
            
            // Build preview with thumbnail
            let previewContent;
            if (videoId) {
                const thumbnailUrl = thumbnails[videoId] || '';
                const params = new URLSearchParams({
                    autoplay: autoplay ? 'true' : 'false',
                    loop: loop ? 'true' : 'false',
                    muted: muted ? 'true' : 'false',
                    preload: 'true',
                    responsive: 'true'
                });
                
                const iframeUrl = `https://iframe.mediadelivery.net/embed/${libraryId}/${videoId}?${params.toString()}`;
                
                previewContent = el('div', { 
                    className: 'bunny-player bunny-video-preview',
                    style: {
                        position: 'relative',
                        cursor: 'pointer'
                    }
                },
                    // Thumbnail image
                    el('img', {
                        src: thumbnailUrl,
                        alt: __('Video thumbnail', 'eightam-bunny-video-library'),
                        style: {
                            width: '100%',
                            height: '100%',
                            objectFit: 'cover',
                            display: 'block'
                        }
                    }),
                    // Play icon overlay
                    el('div', {
                        className: 'bunny-video-play-overlay',
                        style: {
                            position: 'absolute',
                            top: '50%',
                            left: '50%',
                            transform: 'translate(-50%, -50%)',
                            width: '80px',
                            height: '80px',
                            background: 'rgba(0, 0, 0, 0.7)',
                            borderRadius: '50%',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            pointerEvents: 'none'
                        }
                    },
                        el('span', {
                            className: 'dashicons dashicons-controls-play',
                            style: {
                                fontSize: '40px',
                                width: '40px',
                                height: '40px',
                                color: 'white'
                            }
                        })
                    ),
                    // Settings badge
                    el('div', {
                        className: 'bunny-video-settings-badge',
                        style: {
                            position: 'absolute',
                            bottom: '10px',
                            right: '10px',
                            background: 'rgba(0, 0, 0, 0.8)',
                            color: 'white',
                            padding: '5px 10px',
                            borderRadius: '4px',
                            fontSize: '11px',
                            display: 'flex',
                            gap: '8px'
                        }
                    },
                        autoplay && el('span', {}, '▶ Autoplay'),
                        loop && el('span', {}, '🔁 Loop'),
                        muted && el('span', {}, '🔇 Muted')
                    )
                );
            } else {
                previewContent = el('div', {
                    className: 'bunny-video-block-placeholder',
                    style: {
                        padding: '40px',
                        textAlign: 'center',
                        background: '#f0f0f0',
                        border: '2px dashed #ccc',
                        borderRadius: '4px'
                    }
                }, __('Please select a video from the block settings →', 'eightam-bunny-video-library'));
            }
            
            return el('div', {},
                // Inspector Controls (Sidebar)
                el(InspectorControls, {},
                    el(PanelBody, {
                        title: __('Video Settings', 'eightam-bunny-video-library'),
                        initialOpen: true
                    },
                        el(SelectControl, {
                            label: __('Select Video', 'eightam-bunny-video-library'),
                            value: videoId,
                            options: videoOptions,
                            onChange: function(value) {
                                setAttributes({ videoId: value });
                            }
                        }),
                        el(SelectControl, {
                            label: __('Aspect Ratio', 'eightam-bunny-video-library'),
                            value: aspectRatio,
                            options: [
                                { value: '16:9', label: '16:9 (Widescreen)' },
                                { value: '4:3', label: '4:3 (Standard)' },
                                { value: '1:1', label: '1:1 (Square)' },
                                { value: '21:9', label: '21:9 (Ultrawide)' },
                                { value: '9:16', label: '9:16 (Vertical)' }
                            ],
                            onChange: function(value) {
                                setAttributes({ aspectRatio: value });
                            },
                            help: __('Choose the aspect ratio that matches your video dimensions', 'eightam-bunny-video-library')
                        }),
                        el(ToggleControl, {
                            label: __('Autoplay', 'eightam-bunny-video-library'),
                            checked: autoplay,
                            onChange: function(value) {
                                setAttributes({ autoplay: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Loop', 'eightam-bunny-video-library'),
                            checked: loop,
                            onChange: function(value) {
                                setAttributes({ loop: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Muted', 'eightam-bunny-video-library'),
                            checked: muted,
                            onChange: function(value) {
                                setAttributes({ muted: value });
                            }
                        })
                    )
                ),
                // Block Preview
                previewContent
            );
        },
        
        save: function() {
            // Return null because we're using PHP render_callback
            return null;
        }
    });
})(window.wp);
