# FluxStack Product Catalog

A custom WordPress product catalog plugin without e-commerce functionality. Creates a custom post type for products with categories, brands, tags, and types.

## Features

- **Custom Post Type**: Products with full WordPress editor support
- **Taxonomies**: Categories (hierarchical), Brands, Types (hierarchical), and Tags
- **ACF Integration**: Product information fields, specifications, and gallery
- **Frontend Templates**: Fully customizable template system
- **AJAX Filtering**: Real-time product filtering without page reload
- **Infinite Scroll**: Automatic loading with load more button fallback
- **Responsive Design**: Mobile-first approach with collapsible filters
- **Lightbox Gallery**: Custom lightweight image gallery with keyboard navigation
- **Specification Tabs**: Organized product specifications with tabbed interface
- **Responsive Tables**: Wide tables get a scroll toolbar with arrow buttons and visual hints
- **Breadcrumb Navigation**: SEO-friendly breadcrumbs
- **Template Override System**: Easy customization via theme directory

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Advanced Custom Fields PRO

## Installation

1. Upload the plugin files to `/wp-content/plugins/fs-product-catalog/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure ACF Pro is installed and activated

## Quick Start

After installation:
1. Go to **Products** in WordPress admin
2. Add your first product with title, content, and featured image
3. Add product information and specifications using ACF fields
4. Assign categories, brands, types, or tags
5. View your product on the frontend
6. Configure display options under **Products > Settings**

Visit the product archive at: `yoursite.com/product/`

## Customization

### Admin Settings

Most display options can be configured from **Products > Settings** in the WordPress admin:

- **General**: Products per page, grid columns, sort order, breadcrumbs
- **Single Product**: Sidebar on/off, position, visible sections, items limit
- **Archive**: Filter sidebar on/off, position, visible filter groups
- **Product Card**: Category label, excerpt, "View Details" link, image ratio

All settings can also be overridden via PHP filters for developers (see below).

### Template Override System

The plugin uses a template hierarchy that allows you to override any template:

1. **Theme Directory** (checked first): `{your-theme}/fs-product-catalog/`
2. **Plugin Directory** (fallback): `{plugin}/templates/`

**Example**: To customize the product card:
1. Copy `{plugin}/templates/parts/loop/product-card.php`
2. Paste to `{your-theme}/fs-product-catalog/parts/loop/product-card.php`
3. Modify as needed

For a complete list of available templates, see [DEVELOPER.md](DEVELOPER.md#template-system)

### CSS Customization

The plugin uses CSS custom properties (variables) for easy styling:

```css
/* Add to your theme's style.css */
:root {
	--fs-primary: #ff6b6b;        /* Change primary color */
	--fs-gap: 1.5rem;             /* Adjust spacing */
	--fs-border-radius: 8px;      /* Change border radius */
}
```

For complete CSS documentation, see [DEVELOPER.md](DEVELOPER.md#css-architecture)

### Hooks & Filters

Common customization examples:

```php
// Change products per page
add_filter('fs_product_posts_per_page', function() {
	return 24;
});

// Change archive columns
add_filter('fs_product_archive_columns', function() {
	return 4;
});

// Disable single product sidebar
add_filter('fs_product_show_single_sidebar', '__return_false');

// Add custom content after product
add_action('fs_product_after_single_product', function() {
	echo '<div class="custom-content">Your content here</div>';
});
```

For complete hooks reference, see [DEVELOPER.md](DEVELOPER.md#hooks--filters-reference)

## Product Structure

### ACF Fields
- **Product Information**: Repeater field with title and content
- **Specifications**: Repeater field with tab title and content (tabbed interface)
- **Gallery**: Multiple images with lightbox support

### Taxonomies
- **Categories**: Hierarchical with image support
- **Brands**: Non-hierarchical with image support
- **Types**: Hierarchical with image support
- **Tags**: Non-hierarchical

## Technical Features

- **AJAX Filtering**: Real-time search and filtering without page reload
- **Infinite Scroll**: Automatic loading with Intersection Observer API
- **Accessibility**: ARIA labels, keyboard navigation, screen reader support
- **Performance**: Conditional asset loading, optimized queries
- **Browser Support**: Modern browsers with IE11 graceful degradation
- **Standards Compliant**: Follows WordPress PHP, JavaScript, and CSS coding standards

## Documentation

- **[README.md](README.md)** - This file (user guide and quick start)
- **[DEVELOPER.md](DEVELOPER.md)** - Complete technical documentation for developers

## Changelog
- **[CHANGELOG.md](CHANGELOG.md)** - Plugin Changelog

## Support & Contributing

- **Issues**: Report bugs via the plugin repository
- **Contributing**: See [DEVELOPER.md](DEVELOPER.md#contributing) for guidelines
- **Standards**: All code follows WordPress Coding Standards

## License

GPL v2 or later - [License URI](https://www.gnu.org/licenses/gpl-2.0.html)

## Credits

**Author**: Ajith R N  
**Website**: [ajithrn.com](https://ajithrn.com)  
**Plugin URI**: [ajithrn.com](https://ajithrn.com)
