## CIPIT Custom Tabs
Contributors: Kevin Muchwat
Tags: tabs, shortcode, deep linking, search, content tabs, ui
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CIPIT Custom Tabs adds powerful tabbed content to WordPress with deep linking (#tab-id), dynamic loading via Custom Post Types, vertical/horizontal layouts, custom IDs, and a built-in search bar.

### Description

CIPIT Custom Tabs allows you to create dynamic, searchable tabbed content using both:
- A Custom Post Type (`tab_item`)
- A taxonomy (`tab_group`)
- OR static shortcodes when needed

### Key Features
* Dynamic tabs using CPT (Tab Items)
* Static mode using shortcodes
* Deep linking support using `#your-tab-id`
* Custom Tab ID field in admin
* Built-in live search (filters titles + content)
* 3 layouts: top, left, right
* Fully responsive
* Theme variable support (uses CSS vars)
* Smooth animations and clean UI

This plugin is ideal for documentation, research content, multi-section pages, FAQs, and anything requiring structured, easy-to-use navigation.

### Installation

1. Upload the plugin folder to:
   `/wp-content/plugins/`
2. Activate the plugin through **WordPress → Plugins**
3. Go to **Custom Tabs → Add New** to create Tab Items
4. Create a **Tab Group** and assign your Tab Items to it
5. Render dynamic tabs using the shortcode:

```bash
[custom_tabs group="my-group" layout="left" title="My Tabs" description="This is my custom tab"]
```


### Frequently Asked Questions

#### How do I activate dynamic mode? 
Create Tab Items and assign them to a Tab Group. Then use:
```bash
[custom_tabs group="group-slug"]
```

#### How do I set a custom deep linking ID? 
Edit any Tab Item → right sidebar → “Custom Tab ID”.

#### What layouts are available? 
- `layout="top"`
- `layout="left"`
- `layout="right"`

#### Is the search optional? 
Yes. It only appears when `title` or `description` attributes are used.

### Screenshots

1. Admin Tab Items list  
2. Edit screen showing Custom Tab ID  
3. Horizontal layout  
4. Vertical layout with search enabled  

### Changelog 

#### 1.0
* Initial plugin release

