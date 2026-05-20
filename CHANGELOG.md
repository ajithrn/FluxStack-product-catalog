# Changelog

All notable changes to the FluxStack Product Catalog plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.0] - 2025-05-20

### Added
- **Admin Settings Page**: Full settings UI under Products > Settings
  - Tabbed interface: General, Single Product, Archive, Product Card
  - AJAX save with toast notifications (no page reload)
  - Ctrl+S / Cmd+S keyboard shortcut to save
  - Settings link on the plugins page
  - FluxStack-style UI: rounded cards, pill tabs, toggle switches, custom checkboxes
  - Contextual help text on every field explaining what it does and when to use it
  - Grouped checkboxes for sidebar sections and card elements
- **Settings Integration**: Settings feed into existing filter system at priority 5
  - Developer filters (priority 10) still override settings page values
  - All existing `apply_filters()` hooks continue to work as before
- **New Settings Options**:
  - Products per page, archive columns, default sort order
  - Sidebar visibility and position (single + archive)
  - Sidebar section toggles (search, categories, brands, types, tags)
  - Sidebar items limit per section
  - Product card elements (category, excerpt, more link, image ratio)

### Technical
- New file: `includes/class-fs-product-settings.php` — Settings class with AJAX save, sanitization, defaults
- New file: `templates/admin/settings-page.php` — Tabbed settings page template with contextual descriptions
- New file: `assets/css/admin-settings.css` — FluxStack-style admin UI (cards, tabs, toggles, checkboxes, toast)
- New file: `assets/js/admin-settings.js` — Tab switching, AJAX save, Ctrl+S shortcut, toast feedback
- Updated `fs-product-catalog.php`: Load settings class, add settings link on plugins page

## [1.3.0] - 2025-05-20

### Security
- **Fixed query injection in `load_more_products()`**: Removed unsafe `array_merge` with client-supplied `query_vars`. Now uses the same whitelisted parameter approach as `filter_products()`.
- **Capped `per_page` parameter**: Both AJAX handlers now limit to max 100 items per request to prevent DoS via large queries.
- **Secured ACF JSON save point**: Added `is_admin()` and `current_user_can('manage_options')` checks before modifying the save path. Input is now sanitized.

### Fixed
- **Removed broken taxonomy column sorting**: Taxonomy columns in admin were registered as sortable but the sort logic was incorrect (sorted by post title instead of term). Removed fake sortable behavior.
- **Removed `extract()` usage**: Template loader no longer uses `extract()` to pass args. Eliminates variable pollution risk and PHPCS warnings.

### Added
- **Transient caching for sidebar taxonomy queries**: New `FS_Product_Frontend::get_cached_terms()` method caches `get_terms()` results for 1 hour. Cache auto-busts on term create/edit/delete.
- **`uninstall.php`**: Cleans up plugin options and transients when plugin is deleted. Does not remove user content (posts, terms, ACF data).

### Technical
- Updated `includes/class-fs-product-ajax.php`: Rewrote `load_more_products()` with safe parameter handling, capped `per_page` in both handlers
- Updated `includes/class-fs-product-acf.php`: Added capability and admin checks to save point filter
- Updated `includes/class-fs-product-template-loader.php`: Removed `extract()` from `get_template_part()` and `get_template()`
- Updated `includes/class-fs-product-cpt.php`: Removed broken sortable columns and sort handler
- Updated `includes/class-fs-product-frontend.php`: Added `get_cached_terms()`, `flush_term_cache()`, and cache invalidation hooks
- Updated `templates/parts/sidebar-single.php`: Uses cached term queries
- Updated `templates/parts/sidebar-filters.php`: Uses cached term queries
- Added `uninstall.php`

## [1.2.0] - 2025-05-20

### Added
- **Responsive Tables**: Tables in product content, specifications, and info sections are now responsive
  - Auto-wrapped in scrollable containers with a toolbar UI
  - Toolbar shows "← Scroll to view more →" hint text with left/right arrow buttons
  - Right-edge fade gradient indicates more content is available
  - Buttons disable at scroll boundaries
  - Toolbar only appears when table overflows its container
  - Touch/swipe scrolling supported on mobile
- **Content Typography**: Added comprehensive styles for WYSIWYG content
  - Tables: borders, alternating row backgrounds, hover highlight
  - Lists (ul/ol): proper indentation and spacing
  - Headings (h2-h4): relative sizing within content areas
  - Blockquotes: left border accent with background
  - Images: responsive max-width with border radius
- **Missing CSS**: Added styles for previously unstyled template classes
  - `.fs-product-archive-main`, `.fs-toggle-icon`, `.fs-filter-toggle-icon`
  - `.fs-specs-tabs`, `.fs-specs-tabs-content`, `.fs-card-thumbnail`
  - `.fs-lightbox-current`, `.fs-lightbox-total`, `.fs-product-loading-text`

### Changed
- **Single Product Sidebar**: Now enabled by default (`fs_product_show_single_sidebar` defaults to `true`)
- **CSS Architecture**: Refactored sidebar/filter styles into shared components in `frontend-common.css`
  - Removed duplicated sidebar styles from `frontend-single.css` and `frontend-archive.css`
  - Both archive and single sidebars now share the same visual styling
  - Single sidebar template updated to use shared `.fs-filters-wrap` component structure
- **Single Sidebar Template**: Rewritten to use same HTML structure as archive sidebar for visual consistency
  - Uses `.fs-filters-wrap`, `.fs-filter-group`, `.fs-filter-title`, `.fs-filter-content` classes
  - Navigation links instead of checkboxes (browse vs filter)
- **Grid Layout**: Single product layout uses CSS `:has()` selector for sidebar detection
  - Falls back to single column when sidebar is disabled via filter
- **Grid Overflow Fix**: Added `min-width: 0` and `overflow-x: hidden` to prevent wide tables from blowing out the grid layout

### Technical
- Updated `assets/css/frontend-common.css`: Added shared sidebar/filter component styles, toggle icons, responsive behavior
- Updated `assets/css/frontend-single.css`: Added content typography, responsive table wrapper, removed duplicated sidebar styles
- Updated `assets/css/frontend-archive.css`: Added `.fs-product-archive-main`, `.fs-product-loading-text`, removed duplicated sidebar styles
- Updated `assets/js/frontend-single.js`: Added `ResponsiveTables` module for auto-wrapping tables with scroll toolbar
- Updated `templates/parts/sidebar-single.php`: Rewritten with shared component structure
- Updated `includes/class-fs-product-frontend.php`: Changed `fs_product_show_single_sidebar` default from `false` to `true`

## [1.1.1] - 2025-01-27

### Added
- **Single Product Sidebar**: Optional sidebar for single product pages with search, categories, brands, types, and tags
  - New template: `templates/parts/sidebar-single.php`
  - Disabled by default, enable with `add_filter('fs_product_show_single_sidebar', '__return_true')`
- **Display Filters for Product Cards**: Added filters to control visibility of product card elements
  - `fs_product_card_show_category`: Show/hide category (default: true)
  - `fs_product_card_show_excerpt`: Show/hide excerpt (default: true)
  - `fs_product_card_show_more_link`: Show/hide "View Details" link (default: true)
- **Single Sidebar Display Filters**: Added filters to control single product sidebar sections
  - `fs_single_sidebar_show_search`: Show/hide search box (default: true)
  - `fs_single_sidebar_show_categories`: Show/hide categories (default: true)
  - `fs_single_sidebar_show_brands`: Show/hide brands (default: true)
  - `fs_single_sidebar_show_types`: Show/hide types (default: true)
  - `fs_single_sidebar_show_tags`: Show/hide tags (default: true)
- **Archive Sidebar Display Filters**: Added filters to control archive sidebar sections
  - `fs_archive_sidebar_show_search`: Show/hide search box (default: true)
  - `fs_archive_sidebar_show_categories`: Show/hide categories (default: true)
  - `fs_archive_sidebar_show_brands`: Show/hide brands (default: true)
  - `fs_archive_sidebar_show_types`: Show/hide types (default: true)
  - `fs_archive_sidebar_show_tags`: Show/hide tags (default: true)

### Changed
- **Product Card Styling**: Removed zoom effect on hover, only shows shadow
- **Product Card Images**: Changed from 3:4 aspect ratio to 1:1 (square)
- **Product Card Title**: Increased font size by 5% (1.1rem → 1.155rem)
- **Product Card Category**: Hidden by default in grid view (can be re-enabled with filter)
- **Font Family**: Changed to `inherit` to use theme fonts instead of system fonts
- **Single Product Sidebar Position**: Changed default position from right to left

### Technical
- Updated `assets/js/frontend-archive.js`: Fixed AJAX array parameter handling in both `applyFilters()` and `loadMore()` functions
- Updated `assets/css/frontend-archive.css`: Removed hover transforms, changed image aspect ratio, hidden category, increased title size
- Updated `assets/css/frontend-common.css`: Changed font-family to inherit
- Updated `assets/css/frontend-single.css`: Added complete sidebar styling with responsive design
- Updated `templates/single-product.php`: Added sidebar layout support
- Updated `templates/parts/loop/product-card.php`: Added display filters for card elements
- Updated `templates/parts/sidebar-filters.php`: Added visibility checks for archive sidebar sections
- Updated `includes/class-fs-product-frontend.php`: Added methods for single and archive sidebar control, display filters
- Updated `DEVELOPER.md`: Added documentation for new filters and features

## [1.1.0] - 2025-01-27

### Added
- **Frontend Template System**: Complete template hierarchy with theme override support
- **Template Loader**: WordPress-style template loading with fallback system
- **Single Product Layout**: Title, content, 50/50 image/info split, specification tabs
- **Archive Templates**: Product archive and all taxonomy archive templates
- **Template Parts**: Modular components (breadcrumbs, header, content, gallery, info, specs, sidebar, product cards)
- **AJAX Filtering**: Real-time product filtering without page reload
- **Search Functionality**: Debounced search input with live results
- **Infinite Scroll**: Automatic loading using Intersection Observer API
- **Load More Button**: Fallback for infinite scroll with manual loading
- **Custom Lightbox Gallery**: Lightweight image gallery with keyboard navigation
- **Specification Tabs**: Tabbed interface for product specifications
- **Breadcrumb Navigation**: SEO-friendly breadcrumbs with hierarchical support
- **Filter Sidebar**: Search, categories, brands, types, and tags filtering
- **Active Filters Display**: Visual representation of applied filters
- **Mobile Responsive Design**: Mobile-first approach with collapsible filters
- **CSS Variables System**: Easy customization via CSS custom properties
- **Conditional Asset Loading**: Only loads CSS/JS when needed for better performance
- **WordPress Hooks & Filters**: Extensive customization options for developers
- **Developer Documentation**: Comprehensive DEVELOPER.md with technical details

### Frontend Assets
- `frontend-common.css`: Shared styles with CSS variables
- `frontend-single.css`: Single product page styles
- `frontend-archive.css`: Archive and filter styles
- `frontend-single.js`: Gallery lightbox and tabs functionality
- `frontend-archive.js`: AJAX filtering and infinite scroll

### New Classes
- `FS_Product_Template_Loader`: Template hierarchy and loading
- `FS_Product_Frontend`: Asset management and frontend hooks
- `FS_Product_Ajax`: AJAX request handling for filters and pagination

### Templates
- `single-product.php`: Single product template
- `archive-product.php`: Product archive template
- `taxonomy-category.php`: Category archive template
- `taxonomy-brand.php`: Brand archive template
- `taxonomy-type.php`: Type archive template
- `taxonomy-tag.php`: Tag archive template
- Template parts in `parts/` directory
- Loop templates in `parts/loop/` directory

### Hooks & Filters
- `fs_product_before_main_content`: Action before main content
- `fs_product_after_main_content`: Action after main content
- `fs_product_before_single_product`: Action before single product
- `fs_product_after_single_product`: Action after single product
- `fs_product_posts_per_page`: Filter products per page (default: 12)
- `fs_product_archive_columns`: Filter archive columns (default: 3)
- `fs_product_sidebar_position`: Filter sidebar position (default: 'left')
- `fs_product_show_breadcrumbs`: Filter breadcrumb display (default: true)
- `fs_product_show_sidebar`: Filter sidebar display (default: true)
- `fs_product_thumbnail_size`: Filter thumbnail size (default: 'large')
- `fs_product_ajax_query_args`: Filter AJAX query arguments
- `fs_product_breadcrumbs`: Filter breadcrumb array

### Documentation
- Updated README.md with user-focused content
- Added DEVELOPER.md with complete technical documentation
- Separated user and developer documentation for clarity

### Technical Improvements
- WordPress Coding Standards compliant (PHP, JavaScript, CSS)
- Modular JavaScript using module pattern
- BEM naming convention for CSS classes
- Semantic HTML5 markup
- ARIA labels and keyboard navigation support
- Nonce verification for all AJAX requests
- Proper input sanitization and output escaping
- Mobile-first responsive design
- Performance optimized with conditional loading

### Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11 with graceful degradation
- Mobile browsers (iOS Safari, Chrome Mobile)

### Accessibility
- ARIA labels for interactive elements
- Keyboard navigation support (arrow keys, ESC, tab)
- Focus management for modals and tabs
- Screen reader friendly markup
- Semantic HTML structure

---

## [1.0.0] - 2025-01-27

### Added
- Custom Post Type 'fs-products' with /product/ URL structure
- Classic editor support for product descriptions
- Four custom taxonomies:
  * Product Categories (hierarchical, with images)
  * Product Brands (non-hierarchical, with images)
  * Product Tags (non-hierarchical)
  * Product Types (hierarchical, with images)
- ACF Pro integration with JSON-based field storage
- Product Information tab with repeater field (unlimited rows)
- Product Specifications tab with repeater field (unlimited rows)
- Product Gallery field for multiple images
- Custom admin columns with thumbnails and taxonomy filters
- Taxonomy image support via ACF fields
- ACF Pro dependency checking with auto-deactivation
- Custom admin styling for enhanced UX
- Helper functions for retrieving products and taxonomies
- Translation-ready with text domain 'fs-product-catalog'

### Technical Implementation
- WordPress coding standards compliant
- Modular class-based architecture
- ACF JSON for version control friendly field management
- Proper escaping and sanitization
- Singleton pattern for main plugin class
- Custom rewrite rules with flush on activation/deactivation

### Files Structure
- `fs-product-catalog.php`: Main plugin file with dependency checking
- `includes/class-fs-product-cpt.php`: Custom Post Type registration and management
- `includes/class-fs-product-taxonomies.php`: Taxonomy registration and image support
- `includes/class-fs-product-acf.php`: ACF integration and field management
- `acf-json/group_fs_product_meta_fields.json`: ACF field definitions
- `assets/css/admin.css`: Admin interface styling

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- ACF Pro plugin (required dependency)

---

## Version History

- **1.4.0** (2025-05-20): Admin settings page with tabbed UI, AJAX save, all options configurable from dashboard
- **1.3.0** (2025-05-20): Security fixes, transient caching, removed extract(), uninstall handler
- **1.2.0** (2025-05-20): Responsive tables, content typography, shared CSS architecture, single sidebar enabled by default
- **1.1.1** (2025-01-27): Bug fixes, single product sidebar, display filters, styling improvements
- **1.1.0** (2025-01-27): Frontend template system, AJAX filtering, infinite scroll
- **1.0.0** (2025-01-27): Initial release with custom post type and admin features
