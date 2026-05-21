# Architecture

## Overview

The plugin follows a static-class pattern with WordPress hooks. Each class handles a single responsibility and is initialized via `::init()` from the main plugin file.

## Class Map

| Class | File | Responsibility |
|-------|------|----------------|
| `FS_Product_Catalog` | `fs-product-catalog.php` | Main plugin bootstrap, dependency check, component init |
| `FSProductCatalog\CPT` | `includes/class-fs-product-cpt.php` | Custom post type registration, admin columns |
| `FSProductCatalog\Taxonomies` | `includes/class-fs-product-taxonomies.php` | Taxonomy registration, image support |
| `FSProductCatalog\ACF` | `includes/class-fs-product-acf.php` | ACF field groups, JSON save point |
| `FSProductCatalog\TemplateLoader` | `includes/class-fs-product-template-loader.php` | Template hierarchy, theme overrides |
| `FSProductCatalog\Frontend` | `includes/class-fs-product-frontend.php` | Asset loading, body classes, query modification, schema, LCP preload, inquiry rendering |
| `FSProductCatalog\Ajax` | `includes/class-fs-product-ajax.php` | AJAX filter/load-more handlers |
| `FSProductCatalog\Settings` | `includes/class-fs-product-settings.php` | Admin settings page, option management, filter integration |
| `FSProductCatalog\RestAPI` | `includes/class-fs-product-rest-api.php` | REST API endpoints |
| `FSProductCatalog\ImportExport` | `includes/class-fs-product-import-export.php` | CSV import/export |

## Initialization Flow

```
fs-product-catalog.php
  → require vendor/autoload.php (Composer classmap)
  → FS_Product_Catalog::get_instance() (singleton)
    → init_hooks()
      → plugins_loaded → init_components()
        → CPT::init(), Taxonomies::init(), Frontend::init(), etc.
```

## Data Flow

### Frontend Page Load

```
template_include filter
  → FSProductCatalog\TemplateLoader::template_loader()
    → Checks theme override → falls back to plugin template
      → Template calls \FSProductCatalog\TemplateLoader::get_template_part()
        → Renders parts (sidebar, cards, gallery, etc.)
```

### AJAX Filtering

```
User checks filter → JS Filters.applyFilters()
  → POST to admin-ajax.php (action: fs_filter_products)
    → FSProductCatalog\Ajax::filter_products()
      → Builds WP_Query with sanitized params
      → Renders product cards via template part
      → Returns HTML + metadata as JSON
  → JS replaces grid content, updates count
```

### Settings

```
Admin saves settings (AJAX)
  → FSProductCatalog\Settings::ajax_save()
    → Sanitizes all inputs
    → Saves to single wp_option: fs_product_catalog_settings

On frontend load:
  → FSProductCatalog\Settings::register_setting_filters() (priority 5)
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
│   │   ├── loop/                    # Loop templates (card, pagination, no-products)
│   │   └── product-inquiry.php      # Inquiry/quote section (single product)
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
- Tabs: General, Single Product, Archive, Product Card, Inquiry, Advanced
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
