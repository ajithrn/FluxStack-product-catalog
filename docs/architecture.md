# Architecture

## Overview

The plugin follows a static-class pattern with WordPress hooks. Each class handles a single responsibility and is initialized via `::init()` from the main plugin file.

## Class Map

| Class | File | Responsibility |
|-------|------|----------------|
| `FS_Product_Catalog` | `fs-product-catalog.php` | Main plugin bootstrap, dependency check, component init |
| `FS_Product_CPT` | `includes/class-fs-product-cpt.php` | Custom post type registration, admin columns |
| `FS_Product_Taxonomies` | `includes/class-fs-product-taxonomies.php` | Taxonomy registration, image support |
| `FS_Product_ACF` | `includes/class-fs-product-acf.php` | ACF field groups, JSON save point |
| `FS_Product_Template_Loader` | `includes/class-fs-product-template-loader.php` | Template hierarchy, theme overrides |
| `FS_Product_Frontend` | `includes/class-fs-product-frontend.php` | Asset loading, body classes, query modification, schema, LCP preload |
| `FS_Product_Ajax` | `includes/class-fs-product-ajax.php` | AJAX filter/load-more handlers |
| `FS_Product_Settings` | `includes/class-fs-product-settings.php` | Admin settings page, option management, filter integration |
| `FS_Product_REST_API` | `includes/class-fs-product-rest-api.php` | REST API endpoints |
| `FS_Product_Import_Export` | `includes/class-fs-product-import-export.php` | CSV import/export |

## Initialization Flow

```
fs-product-catalog.php
  → FS_Product_Catalog::get_instance() (singleton)
    → load_dependencies() — require all class files
    → init_hooks()
      → plugins_loaded → init_components()
        → Each class::init() registers its own hooks
```

## Data Flow

### Frontend Page Load

```
template_include filter
  → FS_Product_Template_Loader::template_loader()
    → Checks theme override → falls back to plugin template
      → Template calls FS_Product_Template_Loader::get_template_part()
        → Renders parts (sidebar, cards, gallery, etc.)
```

### AJAX Filtering

```
User checks filter → JS Filters.applyFilters()
  → POST to admin-ajax.php (action: fs_filter_products)
    → FS_Product_Ajax::filter_products()
      → Builds WP_Query with sanitized params
      → Renders product cards via template part
      → Returns HTML + metadata as JSON
  → JS replaces grid content, updates count
```

### Settings

```
Admin saves settings (AJAX)
  → FS_Product_Settings::ajax_save()
    → Sanitizes all inputs
    → Saves to single wp_option: fs_product_catalog_settings

On frontend load:
  → FS_Product_Settings::register_setting_filters() (priority 5)
    → Feeds DB values into existing apply_filters() hooks
    → Developer filters at priority 10 still override
```

## File Structure

```
fs-product-catalog/
├── fs-product-catalog.php           # Main plugin file
├── uninstall.php                    # Cleanup on delete
├── package.json                     # Build scripts
├── .editorconfig                    # Editor formatting
├── .phpcs.xml.dist                  # PHPCS config
├── includes/                        # PHP classes
├── templates/
│   ├── admin/                       # Admin page templates
│   ├── parts/                       # Template parts
│   │   └── loop/                    # Loop templates (card, pagination, no-products)
│   ├── archive-product.php
│   ├── single-product.php
│   ├── search-products.php
│   └── taxonomy-*.php
├── assets/
│   ├── css/                         # Source CSS (development)
│   ├── js/                          # Source JS (development)
│   └── dist/                        # Built output (production)
├── acf-json/                        # ACF field definitions
├── docs/                            # Documentation
└── languages/                       # Translation files
```

## Template Override System

Themes can override any template by copying it to:

```
{theme}/fs-product-catalog/{template-path}
```

The loader checks theme first, then falls back to plugin. Template parts use the same pattern under `parts/`.

## Settings Architecture

- Single option: `fs_product_catalog_settings` (serialized array)
- Settings feed into existing `apply_filters()` hooks at priority 5
- Developer filters at priority 10 always win
- AJAX save with nonce verification and capability check
- All inputs sanitized with type-appropriate functions

## Caching Strategy

- Sidebar taxonomy queries: cached via object cache (Redis/Memcached) or transients (1hr TTL)
- Auto-bust on term create/edit/delete
- REST API: `Cache-Control` headers (5min products, 10min terms)
- LCP image preloaded via `<link rel="preload">` in `<head>`

## Security Model

Every AJAX/form handler follows:
1. Nonce verification
2. Capability check (admin actions)
3. Input sanitization (type-appropriate)
4. Output escaping in templates
5. Whitelisted values (no open-ended merges)
6. Upper bounds on numeric inputs (e.g. per_page max 100)
