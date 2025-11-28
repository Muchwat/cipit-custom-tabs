<?php
/**
 * Plugin Name: CIPIT Custom Tabs
 * Plugin URI: https://github.com/Muchwat/cipit-custom-tabs
 * Description: Implements a custom [custom_tabs] shortcode system with deep linking, dynamic content, layouts, search, and more.
 * Version: 1.0.0
 * Author: Kevin Muchwat
 * Author URI: https://github.com/Muchwat
 * Text Domain: cipit-custom-tabs
 */

global $custom_tab_data;
$custom_tab_data = array();

function ctdl_register_tab_cpt()
{
    $labels = array(
        'name' => _x('Tab Items', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Tab Item', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Custom Tabs', 'textdomain'),
        'name_admin_bar' => __('Tab Item', 'textdomain'),
        'add_new_item' => __('Add New Tab Item', 'textdomain'),
        'new_item' => __('New Tab Item', 'textdomain'),
        'edit_item' => __('Edit Tab Item', 'textdomain'),
        'view_item' => __('View Tab Item', 'textdomain'),
        'all_items' => __('All Tab Items', 'textdomain'),
    );
    $args = array(
        'label' => __('Tab Items', 'textdomain'),
        'description' => __('Content for custom tab sections.', 'textdomain'),
        'labels' => $labels,
        'supports' => array('title', 'editor'),
        'hierarchical' => false,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-editor-table',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => false,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'capability_type' => 'post',
    );
    register_post_type('tab_item', $args);
}
add_action('init', 'ctdl_register_tab_cpt', 0);

function ctdl_register_tab_taxonomy()
{
    $labels = array(
        'name' => _x('Tab Groups', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Tab Group', 'taxonomy singular name', 'textdomain'),
        'menu_name' => __('Tab Groups', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'tab-group'),
    );
    register_taxonomy('tab_group', array('tab_item'), $args);
}
add_action('init', 'ctdl_register_tab_taxonomy', 0);

function ctdl_add_custom_id_meta_box()
{
    add_meta_box(
        'ctdl_tab_id_box',
        __('Custom Tab ID', 'textdomain'),
        'ctdl_display_custom_id_meta_box',
        'tab_item',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'ctdl_add_custom_id_meta_box');

function ctdl_display_custom_id_meta_box($post)
{
    wp_nonce_field('ctdl_save_custom_id_data', 'ctdl_custom_id_nonce');

    $custom_id = get_post_meta($post->ID, '_ctdl_custom_tab_id', true);
    $default_id = $post->post_name;

    echo '<label for="ctdl_custom_tab_id">' . esc_html__('Unique ID (for deep linking):', 'textdomain') . '</label>';
    echo '<input type="text" id="ctdl_custom_tab_id" name="ctdl_custom_tab_id" value="' . esc_attr($custom_id) . '" size="25" style="width: 100%;" />';
    echo '<p class="description">' . esc_html__('This ID is used for deep linking (e.g., #your-id). If left blank, the ID will default to the post slug:', 'textdomain') . ' <code>' . esc_html($default_id) . '</code>.</p>';
}

function ctdl_save_custom_id_data($post_id)
{
    if (!isset($_POST['ctdl_custom_id_nonce']) || !wp_verify_nonce($_POST['ctdl_custom_id_nonce'], 'ctdl_save_custom_id_data')) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    $input_id = isset($_POST['ctdl_custom_tab_id']) ? trim($_POST['ctdl_custom_tab_id']) : '';

    if (!empty($input_id)) {
        // Sanitize the input to allow only letters, numbers, hyphens, and underscores
        $new_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $input_id);

        if (!empty($new_id)) {
            update_post_meta($post_id, '_ctdl_custom_tab_id', $new_id);
        } else {
            delete_post_meta($post_id, '_ctdl_custom_tab_id');
        }

    } else {
        delete_post_meta($post_id, '_ctdl_custom_tab_id');
    }
}
add_action('save_post', 'ctdl_save_custom_id_data');

function custom_tab_shortcode($atts, $content = null)
{
    global $custom_tab_data;

    $atts = shortcode_atts(array(
        'title' => 'Tab',
        'id' => sanitize_title($atts['title'] ?? 'tab-' . count($custom_tab_data)),
    ), $atts, 'custom_tab');

    $custom_tab_data[] = array(
        'title' => $atts['title'],
        'id' => $atts['id'],
        'content' => do_shortcode($content),
    );

    return '';
}
add_shortcode('custom_tab', 'custom_tab_shortcode');

function custom_tabs_shortcode($atts, $content = null)
{
    global $custom_tab_data;

    $atts = shortcode_atts(array(
        'layout' => 'top',
        'group' => '',
        'title' => '',
        'description' => '',
        'show-header' => 'true', // New attribute to control header visibility
        'content-mode' => 'false', // NEW: Content display mode
    ), $atts, 'custom_tabs');

    $layout_class = 'layout-' . sanitize_html_class($atts['layout']);
    $tabs_group_id = 'custom-tabs-' . uniqid();
    $group_slug = sanitize_title($atts['group']);

    // NEW: Determine if content-mode is active
    $content_mode_active = filter_var($atts['content-mode'], FILTER_VALIDATE_BOOLEAN);
    $content_mode_class = $content_mode_active ? 'content-mode-active' : '';

    $custom_tab_data = array();

    $default_title = '';
    $default_description = '';

    // --- Start: Modification to get Group Term Data ---
    if (!empty($group_slug)) {
        // Retrieve the term object by slug from the 'tab_group' taxonomy
        $term = get_term_by('slug', $group_slug, 'tab_group');

        if ($term && !is_wp_error($term)) {
            $default_title = $term->name;
            $default_description = $term->description;
        }
    }

    // Set title and description, prioritizing shortcode attributes over term data
    $title = !empty($atts['title']) ? $atts['title'] : $default_title;
    $description = !empty($atts['description']) ? $atts['description'] : $default_description;
    $show_header = filter_var($atts['show-header'], FILTER_VALIDATE_BOOLEAN);
    // --- End: Modification to get Group Term Data ---


    if (!empty($group_slug)) {
        $query_args = array(
            'post_type' => 'tab_item',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'tab_group',
                    'field' => 'slug',
                    'terms' => $group_slug,
                ),
            ),
        );

        $tabs_query = new WP_Query($query_args);

        if ($tabs_query->have_posts()) {
            while ($tabs_query->have_posts()) {
                $tabs_query->the_post();

                $custom_id = get_post_meta(get_the_ID(), '_ctdl_custom_tab_id', true);
                $tab_id = !empty($custom_id) ? $custom_id : get_post_field('post_name', get_the_ID());

                $custom_tab_data[] = array(
                    'title' => get_the_title(),
                    'id' => $tab_id,
                    'raw_content' => get_the_content(),
                    'content' => apply_filters('the_content', get_the_content()),
                );
            }
            wp_reset_postdata();
        }

    } else {
        do_shortcode($content);
    }

    if (empty($custom_tab_data)) {
        $error_message = __('No tab items found.', 'textdomain');

        if (!empty($group_slug)) {
            $term_for_error = $term ?? get_term_by('slug', $group_slug, 'tab_group');
            if ($term_for_error && !is_wp_error($term_for_error)) {
                $error_message = sprintf(__('No tab items found in the group: "%s".', 'textdomain'), esc_html($term_for_error->name));
            } else {
                $error_message = sprintf(__('No tab items found for the group slug: "%s".', 'textdomain'), esc_html($group_slug));
            }
        }

        return '<p style="text-align: center; padding: 20px; border: 1px dashed #c02126; border-radius: 8px; background-color: #fef0f0; color: #c02126; max-width: 800px; margin: 2rem auto;">' . $error_message . '</p>';
    }

    $output = '';
    $tab_headers = '';
    $tab_contents = '';
    $first_tab_id = $custom_tab_data[0]['id'];

    // Check if header should be shown based on attribute and content presence
    $has_header_content = !empty($title) || !empty($description);
    $search_placeholder = __('Search tab titles and content...', 'textdomain');

    if ($show_header && $has_header_content) {
        $output .= sprintf(
            '<div class="custom-tabs-header-wrap">
                %1$s
                %2$s
                %3$s
            </div>',
            !empty($title) ? '<h1>' . esc_html($title) . '</h1>' : '',
            !empty($description) ? '<p>' . esc_html($description) . '</p>' : '',
            // The search bar is included inside the header wrap
            sprintf(
                '<div class="custom-tabs-search-bar-wrap" data-tabs-id="%1$s-search">
                    <div class="search-inner-bar">
                        <input type="text" placeholder="%2$s" class="custom-tabs-search-input">
                        <div class="search-button-area" title="Search Tabs">
                            <i class="fas fa-search"></i> 
                        </div>
                    </div>
                </div>',
                $tabs_group_id,
                $search_placeholder
            )
        );
    }

    foreach ($custom_tab_data as $tab) {
        $tab_id = esc_attr($tab['id']);
        $tab_title = esc_html($tab['title']);

        $search_data = esc_attr($tab_title . ' ' . strip_tags($tab['raw_content']));

        $tab_headers .= sprintf(
            '<li class="tab-header-item" data-search-content="%3$s">
                <a href="#%1$s" data-target="%1$s" class="custom-tabs-header tab-inactive">
                    %2$s
                </a>
            </li>',
            $tab_id,
            $tab_title,
            $search_data
        );

        $tab_contents .= sprintf(
            '<div id="%1$s" class="custom-tabs-content tab-content-panel hidden">
                %2$s
            </div>',
            $tab_id,
            $tab['content']
        );
    }

    // Concatenate layout class and content mode class
    $final_container_classes = $layout_class . ' ' . $content_mode_class;

    $output .= sprintf(
        '<div class="custom-tabs-container %4$s" data-tabs-id="%1$s">
            <ul class="tab-headers-list">
                %2$s
            </ul>
            <div class="tab-contents-wrap">
                %3$s
                <p class="no-results-message hidden">No results found matching your search criteria.</p>
            </div>
        </div>',
        $tabs_group_id,
        $tab_headers,
        $tab_contents,
        $final_container_classes // Use the combined classes
    );

    $output .= '
    <style>
        .custom-tabs-container { 
            font-family: var(--font-family, sans-serif); 
            width: 100%; /* Full width */
            margin: 0 auto;
        }
        .hidden { 
            display: none !important; 
        }

        /* --- New Header Styles (Based on blog-header theme style) --- */
        .custom-tabs-header-wrap {
            text-align: center;
            padding: var(--section-padding-small, 3rem) 0; 
            background: linear-gradient(135deg, var(--light-gray) 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            margin: 2rem auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            width: 100%; /* Ensure it spans full container width */
        }
        .custom-tabs-header-wrap h1 {
            font-size: var(--h2-font-size, 2.2rem);
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            padding: 0 15px; 
        }
        .custom-tabs-header-wrap p {
            max-width: 700px;
            margin: 0 auto 1.5rem;
            color: #666;
            font-size: 1.1rem;
            padding: 0 15px; 
        }

        /* --- Search Bar Styles (Replicating Theme Design) --- */
        .custom-tabs-search-bar-wrap {
            max-width: 550px; /* Match the max-width used in the theme\'s search-container */
            margin: 1.5rem auto 0; /* Adjust margin inside the header wrap */
        }
        .search-inner-bar {
            display: flex;
            width: 100%;
            border: 2px solid #ddd; /* Match theme border style */
            border-radius: var(--button-radius); /* Full radius for wrap */
            overflow: hidden;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .search-inner-bar:focus-within {
                border-color: var(--primary-color); 
        }
        .custom-tabs-search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            border: none;
            outline: none;
            color: var(--secondary-color);
            background: #fff;
        }
        .search-button-area {
            background: var(--primary-color);
            color: white;
            padding: 1rem 1.8rem;
            font-weight: 600;
            cursor: default;
            transition: background 0.3s ease;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem; 
        }
        .search-button-area:hover {
            background: var(--primary-hover);
        }
        .search-button-area .search-icon {
            font-size: 1.2rem;
            color: white;
            padding: 0;
            position: static;
            transform: none;
        }

        /* --- Global Tab Header Styles (Optimized for vertical/fallback) --- */
        .tab-headers-list { 
            list-style: none; 
            margin: 0; 
            padding: 10px; 
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            gap: 8px;
        }
        .tab-header-item { 
            margin: 0; 
            padding: 0; 
            cursor: pointer; 
        }
        .custom-tabs-header { 
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark-gray); 
            border-radius: calc(var(--border-radius) / 2); /* Slightly smaller radius for tabs */
            text-align: center;
            transition: all 0.3s ease-in-out;
            border: 1px solid transparent;
            font-size: 16px;
        }
        .custom-tabs-header:hover {
            color: var(--primary-color); 
            background-color: rgba(192, 33, 38, 0.05); /* Light primary color hover */
        }

        /* --- Active Tab State (Fallback for non-top/vertical-mobile) --- */
        .tab-active { 
            background-color: var(--primary-color); 
            color: #ffffff !important; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }
        .tab-active:hover {
             color: #ffffff !important;
             background-color: var(--primary-hover);
        }

        /* --- Content Panels: Default Styling --- */

        .tab-contents-wrap a {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
            color: var(--primary-color);
            text-decoration: none;
        }

        .tab-content-panel { 
            padding: 25px;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            min-height: 150px;
            animation: fadeIn 0.4s ease-in-out; 
        }
        
        /* NEW: Content Mode Active (Borderless/Shadowless) */
        .content-mode-active .tab-content-panel {
            border: none !important;
            box-shadow: none !important;
            background-color: transparent !important; /* Use container background if desired, or #ffffff if that\'s the goal */
            /* If you want white background without border/shadow: 
            background-color: #ffffff !important; */
        }

        /* --- No Results Message Styling --- */
        .no-results-message {
            text-align: center;
            padding: 30px;
            font-size: 1.1rem;
            color: var(--secondary-color);
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin: 0; /* Remove default paragraph margin */
        }

        /* --- Animations --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =======================================
            LAYOUT: TOP (Default/Horizontal) - MODIFIED FOR BUTTON LOOK
            ======================================= */
        .layout-top .tab-headers-list { 
            display: flex;
            justify-content: center;
            border-bottom: none; /* Remove border */
            margin-bottom: 25px; 
            padding: 0; /* Reduced padding */
            background-color: transparent; /* Make background transparent */
            box-shadow: none; /* Remove list shadow */
            flex-wrap: wrap; /* Allow wrapping */
        }
        .layout-top .tab-header-item { 
            flex-grow: 0; /* Prevents stretching */
            flex-shrink: 0;
            margin: 4px 4px; /* Add horizontal and vertical margin/gap */
            
            /* Button Container Styles (LI) */
            padding: 0.6rem 1.6rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 30px; /* Highly rounded corners */
            font-weight: 500;
            transition: all 0.3s ease; /* Add transition for hover effect */
        }
        
        /* Apply Hover/Active to the LI item wrapper (The Button) */
        .layout-top .tab-header-item:hover, 
        .layout-top .tab-header-item.is-active { /* is-active class applied by JS */
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .layout-top .custom-tabs-header { 
            /* === Apply link specific styles for button === */
            padding: 0; /* Remove padding from link, as it\'s on the LI */
            text-decoration: none; 
            color: var(--secondary-color);
            font-size: small; /* Requested small font size */
            white-space: nowrap; 
            
            /* Reset/Override conflicting styles */
            display: block;
            background: transparent !important;
            border: none !important; 
            border-radius: 0 !important; 
            position: static;
        }
        
        /* Ensure link color changes on hover/active of the LI item */
        .layout-top .tab-header-item:hover .custom-tabs-header,
        .layout-top .tab-header-item.is-active .custom-tabs-header {
            color: white !important;
        }
        
        /* Reset default tab-active styles for the link element in top layout */
        .layout-top .tab-active { 
             background-color: transparent !important; 
             color: var(--secondary-color) !important; /* Will be overridden by .is-active */
             box-shadow: none !important;
             border-color: transparent !important;
        }


        /* =======================================
            LAYOUT: VERTICAL (Left/Right) - Desktop Only
            30% / 70% split
            ======================================= */
        @media (min-width: 769px) {
            .layout-left, .layout-right {
                display: flex;
                gap: 2rem;
                align-items: flex-start;
            }

            /* Styles common to both vertical layouts */
            .layout-left .tab-headers-list, .layout-right .tab-headers-list {
                flex-direction: column; 
                width: 28%; /* 3 parts of the 10 parts */
                border-bottom: none;
                padding: 10px 0;
                box-shadow: none; 
            }
            .layout-left .tab-contents-wrap, .layout-right .tab-contents-wrap {
                flex-grow: 1;
                width: 72%; /* 7 parts of the 10 parts */
                margin-top: 0; 
            }
            .layout-left .tab-header-item, .layout-right .tab-header-item {
                flex-grow: 0;
                width: 100%; 
                padding: 0; /* Reset padding for vertical list item */
                background: transparent; /* Reset background for vertical list item */
                border: none; /* Reset border for vertical list item */
                border-radius: 0; /* Reset radius for vertical list item */
            }
            .layout-left .custom-tabs-header, .layout-right .custom-tabs-header {
                text-align: left; 
                border-radius: 0; 
                padding: 10px 16px;
                border: 1px solid transparent;
                font-size: 16px; /* Revert font size for vertical layout */
                position: relative; /* REQUIRED: For positioning the icon */
                padding-right: 35px; /* Space for the icon */
            }
            .layout-left .tab-active::after, .layout-right .tab-active::after,
            .layout-left .custom-tabs-header:hover::after, .layout-right .custom-tabs-header:hover::after {
                color: var(--primary-color);
            }
            .layout-left .custom-tabs-header::after, .layout-right .custom-tabs-header::after {
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                content: "\f054";
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.7rem;
                color: var(--dark-gray);
                transition: color 0.3s ease;
            }
            .layout-left .custom-tabs-header:hover, .layout-right .custom-tabs-header:hover {
                background-color: rgba(192, 33, 38, 0.05); 
            }

            /* --- LEFT-SPECIFIC STYLES --- */

            .layout-left .custom-tabs-header {
                border-right: 3px solid transparent; /* Highlight on the right side */
            }

            .layout-left .tab-header-item.is-active,
            .layout-left .tab-active { /* Vertical layout relies on tab-active for link color */
                 /* Reset button overrides */
                background: transparent !important;
                border: none !important;
                color: var(--dark-gray); 
            }
            
            .layout-left .tab-active { /* Re-apply active link state */
                background-color: rgba(192, 33, 38, 0.1) !important; 
                color: var(--primary-color) !important;
                border-color: rgba(192, 33, 38, 0.1) !important; 
                border-right: 3px solid var(--primary-color) !important;
                box-shadow: none !important;
            }

            /* --- RIGHT-SPECIFIC STYLES --- */
            .layout-right {
                flex-direction: row-reverse; /* Swap tab list and content */
            }
            .layout-right .tab-headers-list {
                border-left: 2px solid #ddd;
                border-right: none;
            }
            .layout-right .custom-tabs-header {
                border-left: 3px solid transparent; /* Highlight on the left side */
                text-align: right;
            }
            .layout-right .custom-tabs-header:hover {
                border-left-color: var(--primary-color);
                border-right-color: transparent;
            }
            .layout-right .tab-header-item.is-active,
            .layout-right .tab-active {
                 /* Reset button overrides */
                background: transparent !important;
                border: none !important;
                color: var(--dark-gray); 
            }

            .layout-right .tab-active {
                background-color: rgba(192, 33, 38, 0.1) !important; 
                color: var(--primary-color) !important;
                border-color: rgba(192, 33, 38, 0.1) !important; 
                border-left: 3px solid var(--primary-color) !important;
                border-right: transparent !important;
                box-shadow: none !important;
            }
             .layout-right .tab-active:hover {
                background-color: rgba(192, 33, 38, 0.15) !important;
            }
        }

        /* =======================================
            RESPONSIVENESS (Mobile: 768px and below)
            ======================================= */
        @media (max-width: 768px) {
            .layout-left, .layout-right, .layout-top {
                display: block; 
            }
             /* Search bar cleanup on small screens */
            .search-inner-bar {
                /* Adjust for smaller screens: remove box shadow */
                box-shadow: none; 
                border-color: transparent;
            }
            .custom-tabs-search-input {
                padding: 0.8rem 1rem;
            }
            .search-button-area {
                padding: 0.8rem 1.2rem;
            }
            .tab-headers-list { 
                justify-content: flex-start; 
                flex-wrap: nowrap; 
                overflow-x: auto; 
                -webkit-overflow-scrolling: touch; 
                padding: 5px;
                border-right: none !important; 
                border-left: none !important;
                border-bottom: 2px solid var(--light-gray); 
                flex-direction: row !important;
                width: 100% !important; 
            }
            .tab-header-item { 
                flex-shrink: 0; 
                min-width: unset;
                margin: 0 4px; /* Adjust spacing for mobile */
                padding: 0; /* Reset from top layout button */
                border-radius: calc(var(--border-radius) / 2); /* Revert to standard tab look on mobile */
                background: var(--light-gray); /* Mobile default background for item */
                border: 1px solid transparent;
            }
            
            /* Apply mobile standard tab active state to LI */
            .tab-header-item.is-active {
                background: var(--primary-color);
                border-color: var(--primary-color);
            }

            .custom-tabs-header { 
                padding: 10px 15px; 
                font-size: 14px; 
                border-radius: calc(var(--border-radius) / 2);
                border: 1px solid transparent;
                text-align: center !important; 
                padding-right: 15px; /* Reset vertical icon padding */
            }
            
            .tab-header-item.is-active .custom-tabs-header {
                 color: white !important;
            }
            
            /* Reset vertical active styles on mobile */
            .layout-right .custom-tabs-header, .layout-left .custom-tabs-header {
                border-left: 1px solid transparent;
                border-right: 1px solid transparent;
            }
            .layout-left .tab-contents-wrap, .layout-right .tab-contents-wrap {
                margin-top: 25px; 
            }
        }
    </style>';

    $output .= '
    <script>
    document.addEventListener(\'DOMContentLoaded\', function() {
        const tabsContainer = document.querySelector(\'.custom-tabs-container[data-tabs-id="\' + \'' . esc_js($tabs_group_id) . '\' + \'"]\');

        if (!tabsContainer) return; 

        const searchInput = document.querySelector(\'.custom-tabs-search-bar-wrap[data-tabs-id="\' + \'' . esc_js($tabs_group_id) . '-search\' + \'"] .custom-tabs-search-input\');
        const noResultsMessage = tabsContainer.querySelector(\'.no-results-message\');

        const headers = tabsContainer.querySelectorAll(\'.tab-header-item\');
        const contents = tabsContainer.querySelectorAll(\'.custom-tabs-content\');
        const firstTabId = \'' . esc_js($first_tab_id) . '\';

        function getCssVar(variableName) {
            return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
        }

        function activateTab(tabId) {
            if (noResultsMessage) noResultsMessage.classList.add(\'hidden\');

            headers.forEach(h => {
                const link = h.querySelector(\'.custom-tabs-header\');
                
                // Clear both classes for all tabs
                link.classList.remove(\'tab-active\');
                h.classList.remove(\'is-active\'); // Custom class for LI wrapper styling
                
                link.classList.add(\'tab-inactive\');
                
                // Reset all inline styles 
                link.style.backgroundColor = \'\'; 
                link.style.color = \'\';
                link.style.boxShadow = \'\';
                link.style.borderColor = \'\';
                link.style.borderRight = \'\'; 
                link.style.borderLeft = \'\'; 
            });

            contents.forEach(c => c.classList.add(\'hidden\')); 

            const targetHeaderLink = tabsContainer.querySelector(\'.custom-tabs-header[data-target="\' + tabId + \'"]\');
            if (targetHeaderLink) {
                targetHeaderLink.classList.add(\'tab-active\');
                targetHeaderLink.classList.remove(\'tab-inactive\');
                
                // Apply LI active class for button styling (layout-top) or vertical highlighting (layout-left/right)
                const targetHeaderItem = targetHeaderLink.closest(\'.tab-header-item\');
                if (targetHeaderItem) {
                    targetHeaderItem.classList.add(\'is-active\');
                }

                const primaryColor = getCssVar(\'--primary-color\') || \'#c02126\';

                const isVerticalLayout = tabsContainer.classList.contains(\'layout-left\') || tabsContainer.classList.contains(\'layout-right\');
                const isRightLayout = tabsContainer.classList.contains(\'layout-right\');


                if (isVerticalLayout && window.innerWidth > 768) {
                    const hexToRgba = (hex, alpha) => {
                        if(hex.length === 4) {
                            hex = \'#\' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
                        }
                        const r = parseInt(hex.substring(1, 3), 16);
                        const g = parseInt(hex.substring(3, 5), 16);
                        const b = parseInt(hex.substring(5, 7), 16);
                        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                    };

                    // Apply vertical active styles via JS (to override CSS block and ensure correct color)
                    targetHeaderLink.style.backgroundColor = hexToRgba(primaryColor, 0.1);
                    targetHeaderLink.style.color = primaryColor;
                    targetHeaderLink.style.boxShadow = \'none\';
                    targetHeaderLink.style.borderColor = hexToRgba(primaryColor, 0.1);

                    if (!isRightLayout) {
                        targetHeaderLink.style.borderRight = \'3px solid \' + primaryColor;
                        targetHeaderLink.style.borderLeft = \'transparent\';
                    } else {
                        targetHeaderLink.style.borderLeft = \'3px solid \' + primaryColor;
                        targetHeaderLink.style.borderRight = \'transparent\';
                    }

                } else if (targetHeaderItem && targetHeaderItem.classList.contains(\'is-active\')) {
                    // This handles active state for layout-top (Desktop & Mobile) and Mobile vertical layouts. 
                    // No inline styles needed on the link, as the LI is styled via CSS .is-active.
                    targetHeaderLink.style.color = \'white\';
                }
            }

            const targetContent = tabsContainer.querySelector(\'.custom-tabs-content[id="\' + tabId + \'"]\');
            if (targetContent) {
                targetContent.classList.remove(\'hidden\');
            }
        }

        function filterTabs(searchTerm) {
            const normalizedSearch = searchTerm.toLowerCase().trim();
            let hasVisibleTabs = false;
            let firstVisibleTabId = null;

            // Hide all contents and no-results message before filtering
            contents.forEach(c => c.classList.add(\'hidden\'));
            if (noResultsMessage) noResultsMessage.classList.add(\'hidden\');

            headers.forEach(item => {
                const searchContent = item.getAttribute(\'data-search-content\').toLowerCase();
                const link = item.querySelector(\'.custom-tabs-header\');
                const tabId = link.getAttribute(\'data-target\');

                if (normalizedSearch === \'\' || searchContent.includes(normalizedSearch)) {
                    item.style.display = \'block\';
                    if (!firstVisibleTabId) {
                        firstVisibleTabId = tabId;
                    }
                    hasVisibleTabs = true;
                } else {
                    item.style.display = \'none\';
                }
            });

            if (hasVisibleTabs) {
                const currentHashId = window.location.hash.substring(1);
                // Check if the currently active tab is visible after filtering
                const currentActiveItem = tabsContainer.querySelector(\'.tab-header-item a[data-target="\' + currentHashId + \'"]\');
                const currentActiveIsHidden = currentActiveItem && currentActiveItem.closest(\'.tab-header-item\').style.display === \'none\';

                if (firstVisibleTabId && (!currentActiveItem || currentActiveIsHidden)) {
                    // Activate the first visible tab
                    activateTab(firstVisibleTabId);
                    // Update URL hash without creating history entry
                    if (history.replaceState) {
                        history.replaceState(null, null, \'#\' + firstVisibleTabId);
                    } else {
                        window.location.hash = firstVisibleTabId;
                    }
                } else if (currentActiveItem) {
                    // Reactivate the existing active tab if it\'s visible
                    activateTab(currentHashId);
                }
            } else {
                // No results found
                if (noResultsMessage) {
                    noResultsMessage.classList.remove(\'hidden\');
                }
            }
        }

        // --- Event Listeners ---

        // 1. Tab Click Handler
        headers.forEach(item => {
            const header = item.querySelector(\'.custom-tabs-header\');
            header.addEventListener(\'click\', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute(\'data-target\');

                // Update URL hash
                if (history.pushState) {
                    history.pushState(null, null, \'#\' + tabId);
                } else {
                    window.location.hash = tabId;
                }

                activateTab(tabId);
            });
        });

        // 2. Search Input Handler
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener(\'keyup\', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterTabs(this.value);
                }, 200);
            });

            searchInput.addEventListener(\'paste\', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterTabs(this.value);
                }, 300);
            });
        }

        // 3. Initial Activation (Hash check)
        let initialTabId = firstTabId;

        if (window.location.hash) {
            const hashId = window.location.hash.substring(1);
            const exists = tabsContainer.querySelector(\'.custom-tabs-content[id="\' + hashId + \'"]\');
            if (exists) {
                initialTabId = hashId;
            }
        }

        // 4. Hash Change Handler (for back/forward buttons)
        window.addEventListener(\'hashchange\', function() {
            const hashId = window.location.hash.substring(1);
            if (hashId) {
                const exists = tabsContainer.querySelector(\'.custom-tabs-content[id="\' + hashId + \'"]\');
                if (exists) {
                    activateTab(hashId);
                }
            } else {
                activateTab(firstTabId);
            }
        });

        // 5. Resize Handler (for adjusting vertical/horizontal styles)
        let resizeTimeout;
        window.addEventListener(\'resize\', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Recalculate and apply the active state correctly based on new screen size
                const activeTabId = tabsContainer.querySelector(\'.tab-active\') 
                                                ? tabsContainer.querySelector(\'.tab-active\').getAttribute(\'data-target\') 
                                                : (window.location.hash ? window.location.hash.substring(1) : firstTabId);
                
                activateTab(activeTabId);
            }, 100);
        });

        // Final initial activation
        activateTab(initialTabId);
    });
    </script>';

    $custom_tab_data = array();

    return $output;
}

add_shortcode('custom_tabs', 'custom_tabs_shortcode');
