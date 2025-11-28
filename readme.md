CIPIT Custom Tabs adds powerful tabbed content to WordPress with deep linking (#tab-id), dynamic loading via Custom Post Types, vertical/horizontal layouts, custom IDs, and a built-in search bar.

### Description

CIPIT Custom Tabs allows you to create dynamic, searchable tabbed content using:
- **Custom Post Type** (`Tab Items`) for content structure.
- **Taxonomy** (`Tab Groups`) to organize content and set default headers.
- OR **Static Shortcodes** for simple, hardcoded tabs.

The plugin automatically uses the **Tab Group Name** and **Description** to populate the header, significantly simplifying the shortcode usage.

### Key Features
* Dynamic tabs using CPT (Tab Items) and Tab Groups.
* **Automatic Header:** Uses the Tab Group Name and Description as the default header.
* Static mode using nested shortcodes.
* Deep linking support using `#your-tab-id` to jump directly to specific content.
* Custom Tab ID field in admin for permanent, clean URLs.
* Built-in live search (filters titles + content) included in the header area.
* 3 responsive layouts: `top` (default/horizontal), `left`, and `right` (vertical).
* Theme variable support (uses CSS vars) for easy styling integration.
* Smooth animations and clean UI based on the Golden Ratio principle.

This plugin is ideal for documentation, research content, multi-section pages, FAQs, and anything requiring structured, easy-to-use navigation.

### Installation

1. Upload the plugin folder to:
   `/wp-content/plugins/`
2. Activate the plugin through **WordPress → Plugins**
3. Go to **Custom Tabs → Tab Groups** to create your first group (add Name and Description).
4. Go to **Custom Tabs → Tab Items** to create content, assigning items to the new **Tab Group**.
5. Render dynamic tabs using the simplified shortcode:

```bash
[custom_tabs group="artificial-intelligence-center" layout="left"]
```

# Shortcode Reference: `[custom_tabs]`

The `[custom_tabs]` shortcode is the main wrapper for displaying your tab groups or individual tabs.

## Attributes

| Attribute       | Default    | Description |
|-----------------|------------|-------------|
| `group`         | `''`       | Displays content items associated with this specific Tab Group slug. |
| `layout`        | `'top'`    | Controls the navigation appearance:<br>• `'top'` (horizontal, rounded buttons)<br>• `'left'` or `'right'` (vertical sidebar for desktop) |
| `title`         | `''`        | Overrides the H1 title inherited from the Tab Group taxonomy or sets a custom title. |
| `description`   | `''`       | Overrides the description inherited from the Tab Group taxonomy or sets a custom description. |
| `show-header`   | `'true'`   | Set to `'false'` to hide the main title, description, and search bar header block. |
| `content-mode`  | `'false'`  | **NEW:** Controls the visual styling of the tab content panels. |

---

## Content Panel Modes (`content-mode`)

The `content-mode` attribute allows you to choose between the default "card" panel design and a seamless, integrated design—useful when the surrounding page already provides sufficient visual separation.

| Value (`content-mode`) | Appearance | CSS Changes (from default) | Use Case |
|------------------------|------------|----------------------------|----------|
| `'false'` (Default)    | Panel displays with a border, box shadow, and white background (standard card look). | None | Standard tab functionality; emphasizes content separation. |
| `'true'`               | Panel displays without any border or box shadow, and uses a transparent background. | `border: none; box-shadow: none; background-color: transparent;` | Seamlessly integrating content with the page background or a parent container. |

### Frequently Asked Questions
How do I activate dynamic mode?
Create Tab Items and assign them to a Tab Group. Then use the group attribute:

```bash
[custom_tabs group="group-slug"]
```

#### How do I set a custom deep linking ID?
Edit any Tab Item → right sidebar → “Custom Tab ID”. This is used in the URL hash (e.g., `#my-custom-id`).

How do I override the default Group Title/Description?
Use the title and/or description attributes in the shortcode:

```bash
[custom_tabs group="group-slug" title="Override Title" description="A new summary"]
```

#### What layouts are available?
`layout="top"` (Horizontal)

`layout="left"` (Vertical, Tabs on Left)

`layout="right"` (Vertical, Tabs on Right)

#### Is the search optional?
The search bar is included inside the header wrap. If you use the title or description attributes, or if the Tab Group has a Name or Description, the header (including the search bar) will be visible by default. 

Use `show-header="false"` to hide the entire header/search area. Like this:
```bash
[custom_tabs group="group-slug" show-header="false"]
```


