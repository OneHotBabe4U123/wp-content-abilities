<?php
/**
 * Plugin Name: WP Content Abilities
 * Plugin URI: https://iconsiouxfalls.com
 * Description: Registers WordPress abilities for content management via MCP
 * Version: 1.0.0
 * Author: Icons Event Hall
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register content management abilities
 */
add_action('wp_abilities_api_init', function() {
    
    // 1. List all posts
    wp_register_ability('content/list-posts', [
        'label' => 'List Posts',
        'description' => 'Retrieve all posts with filtering options',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'description' => 'Post status filter',
                    'enum' => ['publish', 'draft', 'private', 'any'],
                    'default' => 'any'
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Number of posts to retrieve',
                    'default' => 10,
                    'minimum' => 1,
                    'maximum' => 100
                ]
            ]
        ],
        'output_schema' => [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'ID' => ['type' => 'integer'],
                    'post_title' => ['type' => 'string'],
                    'post_status' => ['type' => 'string'],
                    'post_date' => ['type' => 'string'],
                    'post_excerpt' => ['type' => 'string']
                ]
            ]
        ],
        'execute_callback' => function($input) {
            $args = [
                'post_type' => 'post',
                'post_status' => $input['status'] ?? 'any',
                'numberposts' => $input['limit'] ?? 10
            ];
            
            $posts = get_posts($args);
            $result = [];
            
            foreach ($posts as $post) {
                $result[] = [
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_status' => $post->post_status,
                    'post_date' => $post->post_date,
                    'post_excerpt' => $post->post_excerpt
                ];
            }
            
            return $result;
        },
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 2. List all pages
    wp_register_ability('content/list-pages', [
        'label' => 'List Pages',
        'description' => 'Retrieve all pages',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Number of pages to retrieve',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100
                ]
            ]
        ],
        'output_schema' => [
            'type' => 'array'
        ],
        'execute_callback' => function($input) {
            $pages = get_pages([
                'number' => $input['limit'] ?? 20
            ]);
            
            $result = [];
            foreach ($pages as $page) {
                $result[] = [
                    'ID' => $page->ID,
                    'post_title' => $page->post_title,
                    'post_status' => $page->post_status,
                    'post_date' => $page->post_date,
                    'guid' => $page->guid
                ];
            }
            
            return $result;
        },
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 3. Create a post
    wp_register_ability('content/create-post', [
        'label' => 'Create Post',
        'description' => 'Create a new blog post',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Post title',
                    'required' => true
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Post content (HTML supported)',
                    'required' => true
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Post status',
                    'enum' => ['publish', 'draft', 'private'],
                    'default' => 'draft'
                ],
                'excerpt' => [
                    'type' => 'string',
                    'description' => 'Post excerpt'
                ]
            ],
            'required' => ['title', 'content']
        ],
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'success' => ['type' => 'boolean'],
                'post_id' => ['type' => 'integer'],
                'message' => ['type' => 'string'],
                'edit_link' => ['type' => 'string']
            ]
        ],
        'execute_callback' => function($input) {
            $post_data = [
                'post_title' => sanitize_text_field($input['title']),
                'post_content' => wp_kses_post($input['content']),
                'post_status' => $input['status'] ?? 'draft',
                'post_excerpt' => sanitize_text_field($input['excerpt'] ?? ''),
                'post_type' => 'post'
            ];
            
            $post_id = wp_insert_post($post_data);
            
            if (is_wp_error($post_id)) {
                return [
                    'success' => false,
                    'message' => $post_id->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'post_id' => $post_id,
                'message' => 'Post created successfully',
                'edit_link' => admin_url('post.php?post=' . $post_id . '&action=edit')
            ];
        },
        'permission_callback' => function() {
            return current_user_can('publish_posts');
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 4. Create a page
    wp_register_ability('content/create-page', [
        'label' => 'Create Page',
        'description' => 'Create a new page',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Page title',
                    'required' => true
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'Page content (HTML supported)',
                    'required' => true
                ],
                'status' => [
                    'type' => 'string',
                    'description' => 'Page status',
                    'enum' => ['publish', 'draft', 'private'],
                    'default' => 'draft'
                ]
            ],
            'required' => ['title', 'content']
        ],
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'success' => ['type' => 'boolean'],
                'page_id' => ['type' => 'integer'],
                'message' => ['type' => 'string'],
                'edit_link' => ['type' => 'string']
            ]
        ],
        'execute_callback' => function($input) {
            $page_data = [
                'post_title' => sanitize_text_field($input['title']),
                'post_content' => wp_kses_post($input['content']),
                'post_status' => $input['status'] ?? 'draft',
                'post_type' => 'page'
            ];
            
            $page_id = wp_insert_post($page_data);
            
            if (is_wp_error($page_id)) {
                return [
                    'success' => false,
                    'message' => $page_id->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'page_id' => $page_id,
                'message' => 'Page created successfully',
                'edit_link' => admin_url('post.php?post=' . $page_id . '&action=edit')
            ];
        },
        'permission_callback' => function() {
            return current_user_can('publish_pages');
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 5. Get site information
    wp_register_ability('site/get-info', [
        'label' => 'Get Site Info',
        'description' => 'Retrieve site information and settings',
        'input_schema' => [
            'type' => 'object',
            'properties' => []
        ],
        'output_schema' => [
            'type' => 'object'
        ],
        'execute_callback' => function($input) {
            return [
                'site_title' => get_bloginfo('name'),
                'tagline' => get_bloginfo('description'),
                'url' => get_bloginfo('url'),
                'admin_email' => get_bloginfo('admin_email'),
                'language' => get_bloginfo('language'),
                'wordpress_version' => get_bloginfo('version'),
                'template' => get_bloginfo('template'),
                'post_count' => wp_count_posts()->publish,
                'page_count' => wp_count_posts('page')->publish,
                'user_count' => count_users()['total_users'],
                'plugin_count' => count(get_plugins()),
                'active_theme' => wp_get_theme()->get('Name')
            ];
        },
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 6. List plugins
    wp_register_ability('site/list-plugins', [
        'label' => 'List Plugins',
        'description' => 'Get list of all installed plugins',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['all', 'active', 'inactive'],
                    'default' => 'all'
                ]
            ]
        ],
        'output_schema' => [
            'type' => 'array'
        ],
        'execute_callback' => function($input) {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            $all_plugins = get_plugins();
            $active_plugins = get_option('active_plugins', []);
            $result = [];
            
            foreach ($all_plugins as $plugin_file => $plugin_data) {
                $is_active = in_array($plugin_file, $active_plugins);
                
                if ($input['status'] === 'active' && !$is_active) continue;
                if ($input['status'] === 'inactive' && $is_active) continue;
                
                $result[] = [
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'author' => $plugin_data['Author'],
                    'description' => $plugin_data['Description'],
                    'active' => $is_active
                ];
            }
            
            return $result;
        },
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 7. Update post
    wp_register_ability('content/update-post', [
        'label' => 'Update Post',
        'description' => 'Update an existing post',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'post_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the post to update',
                    'required' => true
                ],
                'title' => [
                    'type' => 'string',
                    'description' => 'New title'
                ],
                'content' => [
                    'type' => 'string',
                    'description' => 'New content'
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['publish', 'draft', 'private']
                ]
            ],
            'required' => ['post_id']
        ],
        'output_schema' => [
            'type' => 'object'
        ],
        'execute_callback' => function($input) {
            $post_data = ['ID' => $input['post_id']];
            
            if (isset($input['title'])) {
                $post_data['post_title'] = sanitize_text_field($input['title']);
            }
            if (isset($input['content'])) {
                $post_data['post_content'] = wp_kses_post($input['content']);
            }
            if (isset($input['status'])) {
                $post_data['post_status'] = $input['status'];
            }
            
            $result = wp_update_post($post_data);
            
            if (is_wp_error($result)) {
                return [
                    'success' => false,
                    'message' => $result->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Post updated successfully',
                'post_id' => $result
            ];
        },
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
    
    // 8. Delete post
    wp_register_ability('content/delete-post', [
        'label' => 'Delete Post',
        'description' => 'Delete a post or page',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'post_id' => [
                    'type' => 'integer',
                    'description' => 'ID of the post to delete',
                    'required' => true
                ],
                'force' => [
                    'type' => 'boolean',
                    'description' => 'Skip trash and permanently delete',
                    'default' => false
                ]
            ],
            'required' => ['post_id']
        ],
        'output_schema' => [
            'type' => 'object'
        ],
        'execute_callback' => function($input) {
            $result = wp_delete_post($input['post_id'], $input['force'] ?? false);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Failed to delete post'
                ];
            }
            
            return [
                'success' => true,
                'message' => $input['force'] ? 'Post permanently deleted' : 'Post moved to trash'
            ];
        },
        'permission_callback' => function() {
            return current_user_can('delete_posts');
        },
        'meta' => [
            'mcp' => ['public' => true]
        ]
    ]);
});

// Add admin notice when plugin is activated
add_action('admin_notices', function() {
    if (get_transient('wp-content-abilities-activated')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>WP Content Abilities activated!</strong> MCP abilities for content management are now available.</p>
        </div>
        <?php
        delete_transient('wp-content-abilities-activated');
    }
});

// Set transient on activation
register_activation_hook(__FILE__, function() {
    set_transient('wp-content-abilities-activated', true, 5);
});
