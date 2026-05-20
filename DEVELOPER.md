# Developer Documentation

## FluxStack Product Catalog - Developer Guide

This document provides detailed technical information for developers working with or extending the FluxStack Product Catalog plugin.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Class Structure](#class-structure)
3. [Template System](#template-system)
4. [Hooks & Filters Reference](#hooks--filters-reference)
5. [AJAX Implementation](#ajax-implementation)
6. [CSS Architecture](#css-architecture)
7. [JavaScript Modules](#javascript-modules)
8. [Extending the Plugin](#extending-the-plugin)
9. [Best Practices](#best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### Plugin Structure

```
fs-product-catalog/
├── fs-product-catalog.php          # Main plugin file
├── includes/                        # PHP classes
│   ├── class-fs-product-cpt.php
│   ├── class-fs-product-taxonomies.php
│   ├── class-fs-product-acf.php
│   ├── class-fs-product-template-loader.php
│   ├── class-fs-product-frontend.php
│   └── class-fs-product-ajax.php
├── templates/                       # Frontend templates
│   ├── single-product.php
│   ├── archive-product.php
│   ├── taxonomy-*.php
│   └── parts/
│       ├── breadcrumbs.php
│       ├── product-*.php
│       ├── sidebar-filters.php
│       ├── sidebar-single.php
│       └── loop/
│           ├── product-card.php
│           └── no-products.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   ├── frontend-common.css
│   │   ├── frontend-single.css
│   │   └── frontend-archive.css
│   └── js/
│       ├── frontend-single.js
│       └── frontend-archive.js
├── acf-json/                        # ACF field definitions
└── languages/                       # Translation files
```

### Design Patterns

- **Singleton Pattern**: Main plugin class
- **Static Classes**: Feature-specific classes (CPT, Taxonomies, etc.)
- **Template Hierarchy**: WordPress-style template loading
- **Module Pattern**: JavaScript organization
- **CSS Custom Properties**: Theming system

---

## Class Structure

### Main Plugin Class: `FS_Product_Catalog`

**File**: `fs-product-catalog.php`

```php
class FS_Product_Catalog {
    private static $instance = null;
    
    public static function get_instance() { }
    private function __construct() { }
    private function load_dependencies() { }
    private function init_hooks() { }
    public function init_components() { }
}
```

**Responsibilities**:
- Plugin initialization
- Dependency management
- Component registration
- ACF Pro dependency check

### Custom Post Type: `FS_Product_CPT`

**File**: `includes/class-fs-product-cpt.php`

```php
class FS_Product_CPT {
    public static function init() { }
    public static function register_post_type() { }
    public static function disable_block_editor() { }
    public static function add_admin_columns() { }
    public static function get_products() { }
    public static function get_products_by_taxonomy() { }
}
```

**Key Methods**:
- `register_post_type()`: Registers 'fs-products' post type
- `get_products($args)`: Query products with custom args
- `get_products_by_taxonomy($taxonomy, $terms, $limit)`: Filter by taxonomy

### Taxonomies: `FS_Product_Taxonomies`

**File**: `includes/class-fs-product-taxonomies.php`

```php
class FS_Product_Taxonomies {
    public static function init() { }
    public static function register_taxonomies() { }
    public static function register_taxonomy_image_fields() { }
    public static function get_terms_with_images($taxonomy, $args) { }
}
```

**Registered Taxonomies**:
- `fs-product-category` (hierarchical)
- `fs-product-brand` (non-hierarchical)
- `fs-product-type` (hierarchical)
- `fs-product-tag` (non-hierarchical)

### Template Loader: `FS_Product_Template_Loader`

**File**: `includes/class-fs-product-template-loader.php`

```php
class FS_Product_Template_Loader {
    public static function init() { }
    public static function template_loader($template) { }
    public static function locate_template($template_name) { }
    public static function get_template_part($slug, $name, $args) { }
    public static function get_template($template_name, $args) { }
}
```

**Template Hierarchy**:
1. `{theme}/fs-product-catalog/{template}.php`
2. `{plugin}/templates/{template}.php`

### Frontend Handler: `FS_Product_Frontend`

**File**: `includes/class-fs-product-frontend.php`

```php
class FS_Product_Frontend {
    public static function init() { }
    public static function enqueue_frontend_assets() { }
    public static function is_product_page() { }
    public static function is_product_archive() { }
    public static function get_products_per_page() { }
    public static function get_archive_columns() { }
    public static function show_breadcrumbs() { }
    public static function show_sidebar() { }
}
```

**Asset Loading Strategy**:
- Conditional loading based on page type
- Common CSS for all product pages
- Specific CSS/JS for single vs archive
- Localized JavaScript data

### AJAX Handler: `FS_Product_Ajax`

**File**: `includes/class-fs-product-ajax.php`

```php
class FS_Product_Ajax {
    public static function init() { }
    public static function filter_products() { }
    public static function load_more_products() { }
    public static function get_filter_counts($taxonomy, $args) { }
}
```

**AJAX Actions**:
- `fs_filter_products`: Apply filters and return results
- `fs_load_more_products`: Load next page of products

---

## Template System

### Template Loading Process

```php
// 1. WordPress calls template_include filter
apply_filters('template_include', $template);

// 2. Plugin checks if it's a product page
if (is_singular('fs-products')) {
    // 3. Locate template (theme first, then plugin)
    $template = FS_Product_Template_Loader::locate_template('single-product.php');
}

// 4. Template is loaded
include $template;
```

### Using Template Parts

```php
// In your template file
FS_Product_Template_Loader::get_template_part('product-header');

// With arguments
FS_Product_Template_Loader::get_template_part('product-card', '', array(
    'show_excerpt' => true,
    'image_size' => 'medium'
));
```

### Creating Custom Templates

**Example: Custom Product Card**

1. Create file: `{theme}/fs-product-catalog/parts/loop/product-card.php`

```php
<?php
// Custom product card template
$product_id = get_the_ID();
$custom_field = get_field('custom_field', $product_id);
?>

<article class="custom-product-card">
    <a href="<?php the_permalink(); ?>">
        <?php the_post_thumbnail('medium'); ?>
        <h3><?php the_title(); ?></h3>
        <?php if ($custom_field): ?>
            <div class="custom-field"><?php echo esc_html($custom_field); ?></div>
        <?php endif; ?>
    </a>
</article>
```

2. The plugin will automatically use your custom template

---

## Hooks & Filters Reference

### Action Hooks

#### Content Hooks

```php
// Before main content wrapper
do_action('fs_product_before_main_content');

// After main content wrapper
do_action('fs_product_after_main_content');

// Before single product content
do_action('fs_product_before_single_product');

// After single product content
do_action('fs_product_after_single_product');

// Sidebar area
do_action('fs_product_sidebar');
```

**Usage Example**:
```php
add_action('fs_product_after_single_product', function() {
    echo '<div class="related-products">';
    // Display related products
    echo '</div>';
});
```

### Filter Hooks

#### Template Filters

```php
// Modify template path
apply_filters('fs_product_template_path', $path);

// Modify template parts array
apply_filters('fs_product_get_template_part', $templates, $slug, $name);

// Before template is included
do_action('fs_product_before_template_part', $template_name, $located, $args);

// After template is included
do_action('fs_product_after_template_part', $template_name, $located, $args);
```

#### Layout Filters

```php
// Products per page (default: 12)
apply_filters('fs_product_posts_per_page', 12);

// Archive columns (default: 3)
apply_filters('fs_product_archive_columns', 3);

// Sidebar position (default: 'left')
apply_filters('fs_product_sidebar_position', 'left');
```

#### Display Filters

```php
// Show breadcrumbs (default: true)
apply_filters('fs_product_show_breadcrumbs', true);

// Show sidebar on archive pages (default: true)
apply_filters('fs_product_show_sidebar', true);

// Show sidebar on single product pages (default: true)
apply_filters('fs_product_show_single_sidebar', true);

// Single product sidebar position (default: 'left')
apply_filters('fs_product_single_sidebar_position', 'left');

// Single sidebar sections visibility (default: true for all)
apply_filters('fs_single_sidebar_show_search', true);
apply_filters('fs_single_sidebar_show_categories', true);
apply_filters('fs_single_sidebar_show_brands', true);
apply_filters('fs_single_sidebar_show_types', true);
apply_filters('fs_single_sidebar_show_tags', true);

// Archive sidebar sections visibility (default: true for all)
apply_filters('fs_archive_sidebar_show_search', true);
apply_filters('fs_archive_sidebar_show_categories', true);
apply_filters('fs_archive_sidebar_show_brands', true);
apply_filters('fs_archive_sidebar_show_types', true);
apply_filters('fs_archive_sidebar_show_tags', true);

// Product card elements visibility (default: true for all)
apply_filters('fs_product_card_show_category', true);
apply_filters('fs_product_card_show_excerpt', true);
apply_filters('fs_product_card_show_more_link', true);

// Thumbnail size (default: 'large')
apply_filters('fs_product_thumbnail_size', 'large');

// Gallery thumbnail size (default: 'thumbnail')
apply_filters('fs_product_gallery_thumbnail_size', 'thumbnail');
```

#### Query Filters

```php
// Modify AJAX filter query args
apply_filters('fs_product_ajax_query_args', $args);

// Modify load more query args
apply_filters('fs_product_load_more_query_args', $args);
```

#### Breadcrumb Filters

```php
// Modify breadcrumb array
apply_filters('fs_product_breadcrumbs', $breadcrumbs);
```

**Breadcrumb Structure**:
```php
$breadcrumbs = array(
    array(
        'url' => 'https://example.com',
        'text' => 'Home'
    ),
    array(
        'url' => 'https://example.com/products',
        'text' => 'Products'
    ),
    array(
        'url' => '', // Empty for current page
        'text' => 'Product Name'
    )
);
```

---

## AJAX Implementation

### Filter Products

**JavaScript Request**:
```javascript
fetch(fsProductCatalog.ajaxUrl, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'fs_filter_products',
        nonce: fsProductCatalog.nonce,
        search: 'search term',
        categories: [1, 2, 3],
        brands: [4, 5],
        types: [6],
        tags: [7, 8],
        paged: 1,
        per_page: 12
    })
})
.then(response => response.json())
.then(result => {
    // result.success
    // result.html
    // result.found
    // result.max_pages
    // result.current
});
```

**PHP Handler**:
```php
public static function filter_products() {
    check_ajax_referer('fs_product_filter_nonce', 'nonce');
    
    // Sanitize inputs
    $search = sanitize_text_field(wp_unslash($_POST['search']));
    $categories = array_map('absint', (array) $_POST['categories']);
    
    // Build query
    $args = array(
        'post_type' => 'fs-products',
        's' => $search,
        'tax_query' => array(/* ... */)
    );
    
    // Execute query
    $query = new WP_Query($args);
    
    // Return JSON
    wp_send_json(array(
        'success' => true,
        'html' => $html,
        'found' => $query->found_posts,
        'max_pages' => $query->max_num_pages
    ));
}
```

### Security

**Nonce Verification**:
```php
check_ajax_referer('fs_product_filter_nonce', 'nonce');
```

**Input Sanitization**:
```php
$search = sanitize_text_field(wp_unslash($_POST['search']));
$categories = array_map('absint', (array) $_POST['categories']);
```

**Output Escaping**:
```php
echo esc_html($text);
echo esc_url($url);
echo esc_attr($attribute);
echo wp_kses_post($html);
```

---

## CSS Architecture

### File Organization

The CSS is split into three files with a clear responsibility hierarchy:

- **`frontend-common.css`**: Loaded on all product pages. Contains CSS variables, container styles, breadcrumbs, utility classes, and **shared sidebar/filter component styles**.
- **`frontend-single.css`**: Single product pages only. Contains layout grid, product header, gallery, lightbox, info, specifications, and single-specific overrides.
- **`frontend-archive.css`**: Archive/taxonomy pages only. Contains archive header, product grid, product cards, load more, no-products state, and archive-specific layout rules.

Shared components (like the sidebar/filter panel) are defined once in `frontend-common.css` and reused by both single and archive templates. Page-specific files only contain overrides or layout rules unique to that context.

### Variable System

**Base Variables** (`frontend-common.css`):
```css
:root {
    /* Colors */
    --fs-primary: #007bff;
    --fs-primary-hover: #0056b3;
    --fs-text: #333;
    --fs-text-light: #666;
    --fs-border: #ddd;
    --fs-bg: #fff;
    
    /* Spacing */
    --fs-gap: 2rem;
    --fs-gap-sm: 1rem;
    --fs-gap-lg: 3rem;
    
    /* Layout */
    --fs-container-width: 1200px;
    --fs-sidebar-width: 280px;
    --fs-border-radius: 4px;
    
    /* Typography */
    --fs-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --fs-font-size: 16px;
    --fs-line-height: 1.6;
    
    /* Transitions */
    --fs-transition: 0.3s ease;
    --fs-transition-fast: 0.15s ease;
    
    /* Shadows */
    --fs-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
```

### Customization Methods

**Method 1: Override Variables**
```css
/* In your theme's style.css */
:root {
    --fs-primary: #ff6b6b;
    --fs-gap: 1.5rem;
    --fs-border-radius: 8px;
}
```

**Method 2: Override Classes**
```css
.fs-product-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.fs-product-card:hover {
    transform: translateY(-4px);
}
```

**Method 3: Add Custom Styles**
```css
.fs-product-single {
    max-width: 1400px;
}

.fs-product-card-title {
    font-family: 'Your Custom Font', sans-serif;
}
```

### BEM Naming Convention

```css
/* Block */
.fs-product-card { }

/* Element */
.fs-product-card-image { }
.fs-product-card-title { }
.fs-product-card-content { }

/* Modifier */
.fs-product-card--featured { }
.fs-product-card--large { }
```

---

## JavaScript Modules

### Gallery Module

**File**: `assets/js/frontend-single.js`

```javascript
const Gallery = {
    lightbox: null,
    images: [],
    currentIndex: 0,
    
    init: function() { },
    bindEvents: function() { },
    openLightbox: function(index) { },
    closeLightbox: function() { },
    prevImage: function() { },
    nextImage: function() { },
    updateLightboxImage: function() { }
};
```

**Usage**:
```javascript
// Gallery automatically initializes on DOM ready
// Images are loaded from JSON in template

// Manual control (if needed)
Gallery.openLightbox(0); // Open at first image
Gallery.nextImage();     // Go to next
Gallery.closeLightbox(); // Close
```

### Tabs Module

```javascript
const Tabs = {
    init: function() { },
    bindEvents: function(tabButtons) { },
    switchTab: function(tabIndex, tabButtons) { }
};
```

### Filters Module

**File**: `assets/js/frontend-archive.js`

```javascript
const Filters = {
    isFiltering: false,
    searchTimeout: null,
    
    init: function() { },
    bindEvents: function() { },
    applyFilters: function() { },
    getFilterData: function() { },
    clearFilters: function() { },
    updateActiveFilters: function() { }
};
```

### Infinite Scroll Module

```javascript
const InfiniteScroll = {
    isLoading: false,
    observer: null,
    
    init: function() { },
    bindEvents: function(loadMoreBtn) { },
    setupIntersectionObserver: function(loadMoreBtn) { },
    loadMore: function(button) { }
};
```

**Intersection Observer**:
```javascript
this.observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
        if (entry.isIntersecting && !self.isLoading) {
            self.loadMore(loadMoreBtn);
        }
    });
}, {
    rootMargin: '200px' // Trigger 200px before button
});
```

---

## Extending the Plugin

### Adding Custom Product Fields

**Step 1: Register ACF Fields**
```php
add_action('acf/init', function() {
    acf_add_local_field_group(array(
        'key' => 'group_custom_product_fields',
        'title' => 'Custom Product Fields',
        'fields' => array(
            array(
                'key' => 'field_custom_price',
                'label' => 'Price',
                'name' => 'custom_price',
                'type' => 'number',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'fs-products',
                ),
            ),
        ),
    ));
});
```

**Step 2: Display in Template**
```php
// In your custom template
$price = get_field('custom_price');
if ($price) {
    echo '<div class="product-price">$' . esc_html($price) . '</div>';
}
```

### Adding Custom Taxonomy

```php
add_action('init', function() {
    register_taxonomy('fs-product-color', 'fs-products', array(
        'label' => 'Colors',
        'hierarchical' => false,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'product-color'),
    ));
});
```

### Custom Query Modifications

```php
add_filter('fs_product_ajax_query_args', function($args) {
    // Only show products from last 30 days
    $args['date_query'] = array(
        array(
            'after' => '30 days ago',
        ),
    );
    return $args;
});
```

### Adding Related Products

```php
add_action('fs_product_after_single_product', function() {
    $categories = get_the_terms(get_the_ID(), 'fs-product-category');
    
    if ($categories) {
        $category_ids = wp_list_pluck($categories, 'term_id');
        
        $related = FS_Product_CPT::get_products_by_taxonomy(
            'fs-product-category',
            $category_ids,
            4
        );
        
        if ($related->have_posts()) {
            echo '<div class="related-products">';
            echo '<h2>Related Products</h2>';
            echo '<div class="product-grid">';
            
            while ($related->have_posts()) {
                $related->the_post();
                FS_Product_Template_Loader::get_template_part('loop/product-card');
            }
            
            echo '</div></div>';
            wp_reset_postdata();
        }
    }
});
```

---

## Best Practices

### Template Development

1. **Always check for data before displaying**:
```php
<?php if (has_post_thumbnail()): ?>
    <?php the_post_thumbnail(); ?>
<?php endif; ?>
```

2. **Use template loader for parts**:
```php
// Good
FS_Product_Template_Loader::get_template_part('product-header');

// Avoid
include 'product-header.php';
```

3. **Pass data via arguments**:
```php
FS_Product_Template_Loader::get_template_part('product-card', '', array(
    'show_price' => true,
    'image_size' => 'large'
));
```

### CSS Development

1. **Use CSS variables for theming**:
```css
/* Good */
.custom-element {
    color: var(--fs-primary);
    padding: var(--fs-gap);
}

/* Avoid */
.custom-element {
    color: #007bff;
    padding: 2rem;
}
```

2. **Follow BEM naming**:
```css
.fs-custom-block { }
.fs-custom-block__element { }
.fs-custom-block--modifier { }
```

3. **Mobile-first responsive design**:
```css
/* Base styles (mobile) */
.element {
    font-size: 14px;
}

/* Tablet and up */
@media (min-width: 768px) {
    .element {
        font-size: 16px;
    }
}
```

### JavaScript Development

1. **Use module pattern**:
```javascript
const MyModule = {
    init: function() { },
    method: function() { }
};
```

2. **Check for elements before binding**:
```javascript
const button = document.querySelector('.my-button');
if (button) {
    button.addEventListener('click', handler);
}
```

3. **Use event delegation for dynamic content**:
```javascript
document.addEventListener('click', function(e) {
    if (e.target.matches('.dynamic-button')) {
        // Handle click
    }
});
```

### Security

1. **Always escape output**:
```php
echo esc_html($text);
echo esc_url($url);
echo esc_attr($attr);
```

2. **Sanitize input**:
```php
$input = sanitize_text_field($_POST['input']);
$email = sanitize_email($_POST['email']);
```

3. **Verify nonces**:
```php
check_ajax_referer('fs_product_filter_nonce', 'nonce');
```

---

## Troubleshooting

### Templates Not Loading

**Issue**: Custom template not being used

**Solution**:
1. Check file path: `{theme}/fs-product-catalog/{template}.php`
2. Clear WordPress cache
3. Check file permissions
4. Verify template name matches exactly

### AJAX Not Working

**Issue**: Filters not applying

**Solution**:
1. Check browser console for JavaScript errors
2. Verify nonce is being passed correctly
3. Check AJAX URL: `console.log(fsProductCatalog.ajaxUrl)`
4. Enable WordPress debug mode to see PHP errors

### Styles Not Applying

**Issue**: CSS not loading or being overridden

**Solution**:
1. Check if on correct page type (single/archive)
2. Clear browser cache
3. Check CSS specificity
4. Use `!important` sparingly for testing
5. Inspect element to see which styles are applied

### Images Not Displaying

**Issue**: Gallery or thumbnails not showing

**Solution**:
1. Regenerate thumbnails
2. Check image size exists
3. Verify ACF gallery field has images
4. Check file permissions

### Performance Issues

**Issue**: Slow page load

**Solution**:
1. Enable object caching
2. Optimize images
3. Limit products per page
4. Use CDN for assets
5. Enable lazy loading

---

## Additional Resources

### WordPress Coding Standards
- [PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- [CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)

### ACF Documentation
- [ACF Documentation](https://www.advancedcustomfields.com/resources/)
- [ACF Field Types](https://www.advancedcustomfields.com/resources/#field-types)

### WordPress Template Hierarchy
- [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/)
- [Template Tags](https://developer.wordpress.org/themes/basics/template-tags/)

---

## Support

For technical support or bug reports, please contact the development team or submit an issue to the plugin repository.

## Contributing

When contributing code:
1. Follow WordPress Coding Standards
2. Add PHPDoc blocks to all functions
3. Write clear commit messages
4. Test on multiple WordPress versions
5. Ensure backward compatibility

---

**Last Updated**: 2025-05-20
**Version**: 1.1.2
