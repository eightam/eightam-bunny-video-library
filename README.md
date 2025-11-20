# 8am Bunny Video Library

WordPress plugin for integrating Bunny.net video library with automatic updates from GitHub.

## Features

- Browse and manage Bunny.net videos from WordPress admin
- Copy video embed codes with customizable options
- Gutenberg block for embedding videos
- Automatic updates from GitHub releases
- Video options: autoplay, loop, muted, show/hide player

## Installation

1. Download the plugin from the [latest release](https://github.com/eightam/eightam-bunny-video-library/releases)
2. Upload to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your Bunny.net credentials in Settings → Bunny Video Library

## Configuration

### Bunny.net Setup

1. Go to **Settings → Bunny Video Library**
2. Enter your Bunny.net Library ID
3. Enter your Bunny.net API Key
4. Save settings

## Usage

### Admin Video Library

Navigate to **Media → Bunny Videos** to:
- View all videos from your Bunny.net library
- Copy embed codes with customizable options
- Toggle autoplay, loop, muted, and player visibility

### Gutenberg Block

1. Add a new block in the editor
2. Search for "Bunny Video"
3. Select a video from the dropdown
4. Configure playback options in the sidebar
5. Publish your post/page

## Development

### Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- Bunny.net account with Video Library

### File Structure

```
eightam-bunny-video-library/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── blocks/
│   └── bunny-video/
│       ├── block.js
│       ├── editor.css
│       └── style.css
├── includes/
│   ├── class-admin-pages.php
│   ├── class-blocks.php
│   ├── class-bunny-api.php
│   ├── class-github-updater.php
│   └── class-settings.php
├── templates/
│   └── videos-page.php
├── LICENSE
├── README.md
└── eightam-bunny-video-library.php
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

Developed by [8am GmbH](https://8am.ch)

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/eightam/eightam-bunny-video-library/issues).
