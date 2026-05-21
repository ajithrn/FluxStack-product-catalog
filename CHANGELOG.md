# Changelog

All notable changes to the FluxStack Product Catalog plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2026-05-21

### Added
- **Product Inquiry Section**: "Request a Quote" box on single product pages
  - Quantity input with +/− buttons (all 44px height, aligned)
  - "Request a Quote" button links to quote form with product name, ID, category, and quantity pre-filled via URL params
  - "Contact Us" secondary button
  - Configurable placement: below product info (right column) or full-width above specs
  - Individual toggle for each button (quote, contact, quantity)
  - All settings in new **Inquiry** tab in Products > Settings
  - Settings: `inquiry_enabled`, `inquiry_placement`, `inquiry_show_quantity`, `inquiry_show_quote_btn`, `inquiry_show_contact_btn`, `inquiry_button_text`, `inquiry_form_url`, `inquiry_contact_text`, `inquiry_contact_url`
- **Common JS Module** (`assets/js/frontend-common.js`): Refactored sidebar interactions into shared module
  - Single source of truth for toggle, collapsible, and show-more logic
  - Prevents double-binding when archive + single JS both load
  - Uses `data-bound` attribute guards
- **Clickable Filter Header**: Entire "Filter Products" / "Browse Products" bar is now clickable (not just the icon)
  - `+`/`−` icon as visual indicator (replaces hamburger)
  - Hover state on the bar
- **Section Collapse Icons**: Replaced chevron arrows with `+`/`−` text characters for clarity
- **Single Sidebar Toggle**: Added missing toggle button to `sidebar-single.php` for mobile

### Changed
- **Spacing Tightened**: Reduced global spacing variables
  - `--fs-gap`: 2rem → 1.5rem
  - `--fs-gap-sm`: 1rem → 0.75rem
  - `--fs-gap-lg`: 3rem → 2rem
- **Base Font Size**: Reduced from 15-17px to 14-15px for denser content
- **Single Product Layout**: Added `.fs-product-main-right` wrapper for info + inquiry stacking
- **Build Script**: `frontend.min.js` now includes `frontend-common.js` first in concatenation order

### Fixed
- **Double Event Binding**: Sidebar collapsible toggle was bound twice (from archive + single IIFEs), causing toggle-on then toggle-off (net zero). Fixed with `data-bound` guards.
- **Mobile Filter Toggle**: Was missing from single product sidebar template
- **768px Breakpoint Conflict**: Removed CSS that hid `.fs-filter-content` and required `.active` class (conflicted with `.is-collapsed` JS logic)

### Technical
- New file: `assets/js/frontend-common.js` — Shared sidebar module
- New file: `templates/parts/product-inquiry.php` — Inquiry section template
- Updated `templates/single-product.php`: Added `.fs-product-main-right` wrapper, inquiry after-info placement
- Updated `templates/parts/sidebar-single.php`: Added toggle button
- Updated `includes/class-fs-product-settings.php`: Added inquiry settings (defaults, sanitization, toggles)
- Updated `includes/class-fs-product-frontend.php`: Added `render_inquiry_section()`, `render_inquiry_after_info()` methods
- Updated `templates/admin/settings-page.php`: Added Inquiry tab (before Advanced)
- Updated `assets/css/frontend-common.css`: Clickable header, +/− icons, spacing reduction, font size
- Updated `assets/css/frontend-single.css`: Inquiry section styles, tighter spacing
- Updated `assets/js/frontend-archive.js`: Removed Sidebar module (moved to common)
- Updated `assets/js/frontend-single.js`: Removed Sidebar module (moved to common)
- Updated `package.json`: Build includes `frontend-common.js`

## [2.1.0] - 2025-05-21

### Added
- **Asset Cache Busting**: Uses `filemtime()` on built files instead of version string — every `npm run build` auto-busts browser cache without manual version bumps

### Changed
- **Product Info section**: Card-based layout with gray header bar, section labels now have bottom borders for clear visual separation between items
- **Specifications section**: Contained card with gray tab navigation bar, active tab gets white background + blue accent, content area has proper padding
- **Section labels**: Smaller, lighter, wider letter-spacing with underline — reads as metadata dividers, not content

## [2.0.0] - 2025-05-20

### Added
- **PSR-4 Namespacing**: All classes now live under `FSProductCatalog\` namespace
  - `FSProductCatalog\CPT`, `FSProductCatalog\Frontend`, `FSProductCatalog\Ajax`, etc.
  - Composer classmap autoloader (no manual `require_once` calls)
- **Build Process**: npm-based asset pipeline
  - `npm run build` — concatenates and minifies all CSS/JS into single files
  - `npm run watch` — auto-rebuild on source file changes
  - Output: `assets/dist/frontend.min.css` (26KB), `assets/dist/frontend.min.js` (13KB), `assets/dist/admin.min.css`, `assets/dist/admin-settings.min.js`
  - 2 HTTP requests per page instead of 5-6
- **`.editorconfig`**: Consistent formatting across editors
- **`.phpcs.xml.dist`**: PHPCS configuration for WordPress-Extra standards
- **`composer.json`**: Autoloader configuration
- **`package.json`**: Build scripts and dev dependencies
- **`.gitignore`**: Excludes `node_modules/`
- **Asset Cache Busting**: Uses `filemtime()` on built files instead of version string — every `npm run build` auto-busts browser cache

### Changed
- **All classes renamed** (namespace handles the prefix now):
  - `FS_Product_CPT` → `FSProductCatalog\CPT`
  - `FS_Product_Frontend` → `FSProductCatalog\Frontend`
  - `FS_Product_Ajax` → `FSProductCatalog\Ajax`
  - `FS_Product_Settings` → `FSProductCatalog\Settings`
  - `FS_Product_Template_Loader` → `FSProductCatalog\TemplateLoader`
  - `FS_Product_REST_API` → `FSProductCatalog\RestAPI`
  - `FS_Product_Import_Export` → `FSProductCatalog\ImportExport`
  - `FS_Product_ACF` → `FSProductCatalog\ACF`
  - `FS_Product_Taxonomies` → `FSProductCatalog\Taxonomies`
- **All template references updated** to use fully-qualified namespaced class names
- **Asset loading simplified** — single bundled file per context, no fallback logic
- **Main plugin file** uses Composer autoloader instead of manual requires

### Breaking Changes
- Old class names (`FS_Product_Frontend`, etc.) no longer exist — no backward compatibility aliases
- `npm install && npm run build` is required after cloning
- `composer install` (or `composer dump-autoload`) is required for autoloading

## [1.9.0] - 2025-05-20

### Added
- **LCP Image Preload**: Single product pages output `<link rel="preload">` for the featured image with `imagesrcset` and `fetchpriority="high"` for faster Largest Contentful Paint
- **REST API Cache Headers**: All API responses include `Cache-Control: public, max-age=300` (products) or `max-age=600` (terms)

### Changed
- **Object Caching**: `get_cached_terms()` now checks `wp_using_ext_object_cache()` and uses `wp_cache_*` functions when a persistent object cache (Redis, Memcached) is available, falling back to transients otherwise
- **Cache Invalidation**: `flush_term_cache()` now clears both object cache and transients
- **CSS Performance**: Added `will-change: box-shadow` to product cards for smoother hover transitions
- **Table Styles**: Removed forced `thead` background, alternating row colors, and hover effects that were overriding WYSIWYG inline styles — tables now respect content-authored colors

### Technical
- Updated `includes/class-fs-product-frontend.php`: Object cache support, LCP preload, dual cache invalidation
- Updated `includes/class-fs-product-rest-api.php`: Cache-Control headers on all responses
- Updated `assets/css/frontend-common.css`: `will-change` hint on product cards
- Updated `assets/css/frontend-single.css`: Removed forced table colors (thead bg, alternating rows, hover, th color) to respect WYSIWYG inline styles

## [1.8.0] - 2025-05-20

### Added
- **REST API**: Public read endpoints under `fs-catalog/v1` namespace
  - `GET /products` — paginated list with search, orderby, and taxonomy filters (category, brand, type, tag by slug)
  - `GET /products/{id}` — single product with full content, gallery images, product info, and specifications
  - `GET /terms/{taxonomy}` — taxonomy terms with counts, parent info for hierarchical taxonomies
  - Response headers: `X-WP-Total`, `X-WP-TotalPages` for pagination
  - All endpoints are publicly readable (no auth required for GET)
- **Import/Export**: CSV-based product data management
  - **Export**: Download all products as CSV with optional taxonomy filters (category, brand, type)
  - **Import**: Upload CSV to create or update products
    - Products with existing ID are updated; new rows created as drafts
    - Taxonomy terms auto-created if they don't exist
    - Pipe-separated values for multiple terms (e.g. `wire-rope|chain`)
    - AJAX upload with results feedback (created/updated/skipped/errors)
  - Admin UI at Products > Import/Export with two-column layout
  - CSV columns: id, title, content, excerpt, status, menu_order, featured_image, categories, brands, types, tags
- **REST API Toggle**: API is disabled by default; enable from Products > Settings > Advanced
  - New "Advanced" settings tab with REST API toggle
  - Shows endpoint URL when enabled

### Technical
- New file: `includes/class-fs-product-rest-api.php` — REST API controller with route registration, response formatting
- New file: `includes/class-fs-product-import-export.php` — CSV export/import handlers with validation
- New file: `templates/admin/import-export-page.php` — Admin UI with export filters and import form
- Updated `fs-product-catalog.php`: Load and initialize REST API and Import/Export classes

## [1.6.0] - 2025-05-20

### Added
- **Product Sorting Dropdown**: Sort products on archive pages by name (A-Z/Z-A), date (newest/oldest), or default (menu order)
  - Dropdown placed in results bar next to product count
  - Triggers AJAX filter with `orderby` parameter
  - Persists sort selection across filter changes and load more
  - Visibility configurable via Settings > Archive > "Show Sorting Dropdown" toggle
  - Filter: `fs_product_show_sorting`
- **Related Products**: Shows products from the same category on single product pages
  - Displays 4 related products by default (configurable 1–8)
  - Excludes current product, randomized order
  - New template part: `templates/parts/related-products.php`
  - Filters: `fs_product_show_related`, `fs_product_related_count`, `fs_product_related_query_args`
  - Settings: toggle and count in Single Product tab
- **Three Pagination Modes**: Configurable in Settings > General > Pagination
  - **Load More** (default): Manual button click to append more products
  - **Infinite Scroll**: Auto-loads via IntersectionObserver when scrolling near bottom
  - **Numbered Pages**: Traditional pagination with page numbers, prev/next arrows, and SEO `<link rel="next/prev">` tags
  - Custom "Load More Button Text" field (shown only for Load More / Infinite Scroll modes)
  - Filter: `fs_product_pagination_mode`
  - New template part: `templates/parts/loop/pagination.php`
- **Schema.org Product Markup**: JSON-LD structured data on single product pages
  - Outputs name, description, image, brand, category, and URL
  - Hooked to `wp_head` for proper placement
  - Filter: `fs_product_schema` for extending/customizing the schema data
- **Lazy Loading for Gallery Images**: Performance optimization for image-heavy pages
  - Thumbnail images use `loading="lazy"`
  - Main image uses `loading="eager"` + `fetchpriority="high"` for LCP
- **Product Search Results Integration**: Sidebar search now uses product archive layout
  - New template: `templates/search-products.php`
  - Overrides default WP search when `post_type=fs-products`
  - Includes sidebar, grid layout, and pagination
  - Archive assets (CSS/JS) load on product search pages
- **Conditional Settings Fields**: Admin settings fields can now show/hide based on another field's value (used for pagination mode → button text)

### Changed
- **AJAX Handlers**: Both `filter_products()` and `load_more_products()` now accept `orderby` parameter
  - New `parse_orderby()` helper validates and maps sort values
  - Allowed values: `menu_order`, `title_asc`, `title_desc`, `date_desc`, `date_asc`
- **Frontend Class**: `is_product_archive()` now returns true for product search results
- **Localized JS Data**: Added `paginationMode` and custom `loadMore` text to `fsProductCatalog` object
- **Removed Infinite Scroll as default**: Load More button is now manual-click only by default; infinite scroll is opt-in via settings
- **Load More Button**: Restyled to neutral/theme-inheriting design (transparent background, border-based, inherits font-family) instead of hardcoded primary color — blends with any theme
- **Product Grid & Card CSS**: Moved from `frontend-archive.css` to `frontend-common.css` so cards render correctly on single product pages (related products) and search results

### Technical
- New file: `templates/parts/related-products.php` — Related products grid
- New file: `templates/parts/loop/pagination.php` — Numbered pagination with SEO tags
- New file: `templates/search-products.php` — Product search results template
- Updated `templates/archive-product.php`: Added sort dropdown (conditional), pagination mode support, custom button text
- Updated `templates/single-product.php`: Added related products after main content
- Updated `templates/parts/product-image-gallery.php`: Added lazy/eager loading attributes
- Updated `templates/admin/settings-page.php`: Added pagination section, related products section, sorting toggle, conditional field attributes
- Updated `includes/class-fs-product-frontend.php`: Added schema output, search template, `show_sorting()`, `get_load_more_text()`, `get_pagination_mode()` helpers
- Updated `includes/class-fs-product-ajax.php`: Added orderby support, `parse_orderby()` method
- Updated `includes/class-fs-product-settings.php`: Added `pagination_mode`, `load_more_text`, `show_sorting`, `show_related_products`, `related_products_count` settings with sanitization and filter registration
- Updated `assets/js/frontend-archive.js`: Sort dropdown binding, orderby in AJAX, LoadMore module with optional IntersectionObserver
- Updated `assets/js/admin-settings.js`: Added `ConditionalFields` module for dependent field visibility
- Updated `assets/css/frontend-common.css`: Moved product grid and card styles here (shared across all pages)
- Updated `assets/css/frontend-archive.css`: Removed duplicated grid/card styles, added sort dropdown, pagination, neutral load more button
- Updated `assets/css/frontend-single.css`: Related products section styles with responsive grid

## [1.5.0] - 2025-05-20

### Added
- **Sidebar Item Limits**: Sections with many items now show only the first N items (configurable, default: 8)
  - "Show more (24)" button reveals hidden items
  - "Show less" collapses back to the limit
  - Tags section defaults to 15 items
  - Separate "Items Per Section" setting for single and archive sidebars
  - Configurable via settings page or filters
- **Collapsible Section Headers**: All sidebar section titles are now clickable to collapse/expand
  - Chevron icon indicates open/closed state
  - Hover highlight on header bar
  - Collapsed cards lose shadow for "inactive" feel
- **Per-section item limit filters**:
  - `fs_sidebar_items_limit` — global default (8)
  - `fs_sidebar_categories_limit` — override for categories
  - `fs_sidebar_brands_limit` — override for brands
  - `fs_sidebar_types_limit` — override for types
  - `fs_sidebar_tags_limit` — override for tags (default: 15)

### Changed
- **Sidebar Layout**: Each section is now its own card instead of one monolithic container
  - Individual border, border-radius, and box shadow per section
  - Cards separated by gap (no more divider lines)
  - Hover shadow effect on cards
- **Sidebar Header**: Dark background with white text for clear visual hierarchy
- **Section Titles**: Uppercase, smaller font, gray background bar — acts as card header
- **Filter Items**: Added padding, border-radius, and hover background highlight per row
- **Active Filters**: Moved from bottom to top of sidebar
  - Appears between header and first filter section
  - Card with primary-colored border
  - Header row with title + "Clear All" link (red text)
  - Filter pills: light gray with border, × remove button
  - Compact, non-dominant design
- **Search Input**: Added focus ring with primary color glow
- **Archive sidebar**: Separate "Items Per Section" setting independent from single sidebar

### Technical
- Updated `templates/parts/sidebar-single.php`: Item limit logic, collapsible titles, show more buttons
- Updated `templates/parts/sidebar-filters.php`: Same + moved active filters to top with new HTML structure
- Updated `assets/css/frontend-common.css`: Card-based sidebar layout, improved filter styles, active filters redesign
- Updated `assets/js/frontend-single.js`: Added `Sidebar` module (show more + collapsible)
- Updated `assets/js/frontend-archive.js`: Added `Sidebar` module + updated `updateActiveFilters()` for new structure
- Updated `includes/class-fs-product-settings.php`: Added `archive_sidebar_items_limit` setting
- Updated `templates/admin/settings-page.php`: Added "Items Per Section" field to Archive tab

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

- **2.1.0** (2025-05-21): Cache busting via filemtime, improved product info and specs section styling
- **2.0.0** (2025-05-20): Feature-complete milestone, editorconfig, PHPCS config, architecture decision documented
- **1.9.0** (2025-05-20): Object caching, LCP preload, REST API cache headers, CSS performance hints
- **1.8.0** (2025-05-20): REST API endpoints, CSV import/export with admin UI
- **1.6.0** (2025-05-20): Sorting dropdown, related products, pagination, Schema.org markup, lazy loading, search integration
- **1.5.0** (2025-05-20): Card-based sidebar, item limits, collapsible sections, active filters at top, separate archive/single limits
- **1.4.0** (2025-05-20): Admin settings page with tabbed UI, AJAX save, all options configurable from dashboard
- **1.3.0** (2025-05-20): Security fixes, transient caching, removed extract(), uninstall handler
- **1.2.0** (2025-05-20): Responsive tables, content typography, shared CSS architecture, single sidebar enabled by default
- **1.1.1** (2025-01-27): Bug fixes, single product sidebar, display filters, styling improvements
- **1.1.0** (2025-01-27): Frontend template system, AJAX filtering, infinite scroll
- **1.0.0** (2025-01-27): Initial release with custom post type and admin features
