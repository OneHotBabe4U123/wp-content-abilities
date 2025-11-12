# WP Content Abilities

A WordPress plugin that registers content management abilities for use with Model Context Protocol (MCP).

## Description

WP Content Abilities extends WordPress functionality by registering abilities that can be accessed via MCP adapters. This enables programmatic content management and site administration through standardized interfaces.

## Features

### Content Management
- **List Posts** - Retrieve posts with filtering by status and limit
- **List Pages** - Get all pages on your site
- **Create Post** - Create new blog posts with title, content, status, and excerpt
- **Create Page** - Create new WordPress pages
- **Update Post** - Modify existing posts
- **Delete Post** - Remove posts (with trash or permanent delete options)

### Site Information
- **Get Site Info** - Retrieve site details including:
  - Site title and tagline
  - WordPress version
  - Post/page/user counts
  - Active theme and plugins
  
- **List Plugins** - View all installed plugins with status filtering

## Installation

### Via Git Clone (Recommended)
```bash
cd /var/www/html/wp-content/plugins
sudo git clone https://github.com/YOUR_USERNAME/wp-content-abilities.git
sudo chown -R www-data:www-data wp-content-abilities
```

Then activate via WordPress admin or WP-CLI:
```bash
wp plugin activate wp-content-abilities
```

### Manual Installation

1. Download this repository
2. Upload the `wp-content-abilities` folder to `/wp-content/plugins/`
3. Activate through WordPress admin → Plugins menu

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- User with appropriate permissions for each ability
- MCP adapter plugin (for MCP functionality)

## Permissions

Each ability has permission requirements:

- **List Posts/Pages/Site Info**: Requires logged-in user
- **Create Post**: Requires `publish_posts` capability
- **Create Page**: Requires `publish_pages` capability
- **Update Post**: Requires `edit_posts` capability
- **Delete Post**: Requires `delete_posts` capability
- **List Plugins**: Requires `activate_plugins` capability

## Usage with MCP

This plugin is designed to work with MCP adapters. Once activated, abilities are automatically registered and available through the MCP interface.

### Example: Create a Post
```json
{
  "ability": "content/create-post",
  "parameters": {
    "title": "My New Post",
    "content": "<p>This is the post content.</p>",
    "status": "draft",
    "excerpt": "A brief summary"
  }
}
```

### Example: List Posts
```json
{
  "ability": "content/list-posts",
  "parameters": {
    "status": "publish",
    "limit": 20
  }
}
```

## Registered Abilities

| Ability Name | Description | Required Params |
|--------------|-------------|-----------------|
| `content/list-posts` | Get posts list | None (optional: status, limit) |
| `content/list-pages` | Get pages list | None (optional: limit) |
| `content/create-post` | Create new post | title, content |
| `content/create-page` | Create new page | title, content |
| `content/update-post` | Update existing post | post_id |
| `content/delete-post` | Delete a post | post_id |
| `site/get-info` | Get site information | None |
| `site/list-plugins` | List installed plugins | None (optional: status) |

## Security

- All input is sanitized using WordPress functions
- Content uses `wp_kses_post()` for safe HTML
- Permission callbacks enforce capability checks
- User authentication required for all operations

## Development

This plugin was developed for Icons Event Hall, Sioux Falls, SD.

### File Structure
```
wp-content-abilities/
├── wp-content-abilities.php  (Main plugin file)
└── README.md                 (This file)
```

## Changelog

### 1.0.0 (2025-11-11)
- Initial release
- 8 content management abilities
- Site information retrieval
- Plugin listing functionality

## Support

For issues or questions:
- Create an issue on GitHub
- Contact: 
- Website: 
## License

GPL v2 or later

## Credits

**Author**: Icons Event Hall  
**Plugin URI**: https://iconsiouxfalls.com  
**Version**: 1.0.0
