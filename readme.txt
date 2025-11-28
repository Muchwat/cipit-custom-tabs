=== CIPIT Custom Tabs ===
Contributors: Kevin Muchwat
Tags: tabs, shortcode, deep linking, search, content tabs, ui, dynamic content, cpts
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Implements a custom [custom_tabs] shortcode system with deep linking, dynamic content, layouts, search, and more.

== Description ==

CIPIT Custom Tabs adds powerful tabbed content to WordPress with deep linking (#tab-id), dynamic loading via Custom Post Types, vertical/horizontal layouts, custom IDs, and a built-in search bar.

This plugin allows you to create dynamic, searchable tabbed content using:

* **Custom Post Type** (`Tab Items`) for content structure.
* **Taxonomy** (`Tab Groups`) to organize content and set default headers.
* OR **Static Shortcodes** for simple, hardcoded tabs.

The plugin automatically uses the **Tab Group Name** and **Description** to populate the header, significantly simplifying the shortcode usage.

### Key Features
* Dynamic tabs using CPT (Tab Items) and Tab Groups.
* **Automatic Header:** Uses the Tab Group Name and Description as the default header.
* Deep linking support using `#your-tab-id` to jump directly to specific content.
* Custom Tab ID field in admin for permanent, clean URLs.
* Built-in live search (filters titles + content) included in the header area.
* 3 responsive layouts: `top` (default/horizontal), `left`, and `right` (vertical).
* Theme variable support (uses CSS vars) for easy styling integration.

== Installation ==

1.  Upload the plugin folder to: `/wp-content/plugins/`
2.  Activate the plugin through **WordPress -> Plugins**
3.  Go to **Custom Tabs -> Tab Groups** to create your first group (add Name and Description).
4.  Go to **Custom Tabs -> Tab Items** to create content, assigning items to the new **Tab Group**.
5.  Render dynamic tabs using the simplified shortcode:
    
    `[custom_tabs group="artificial-intelligence-center" layout="left"]`


== Frequently Asked Questions ==

= How do I activate dynamic mode? =

Create Tab Items and assign them to a Tab Group. Then use the `group` attribute:

`[custom_tabs group="group-slug"]`

= How do I set a custom deep linking ID? =

Edit any Tab Item -> right sidebar -> **“Custom Tab ID”**. This is used in the URL hash (e.g., `#my-custom-id`).

= How do I override the default Group Title/Description? =

Use the `title` and/or `description` attributes in the shortcode:

`[custom_tabs group="group-slug" title="Override Title" description="A new summary"]`

= What layouts are available? =

* `layout="top"` (Horizontal)
* `layout="left"` (Vertical, Tabs on Left)
* `layout="right"` (Vertical, Tabs on Right)

= Is the search optional? =

The search bar is included inside the header wrap. If the Tab Group has a Name or Description, the header (including the search bar) will be visible by default. Use `show-header="false"` to hide the entire header/search area.

== Changelog ==
= 1.0 =
* Initial plugin release