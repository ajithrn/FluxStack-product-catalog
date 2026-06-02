# FluxStack Product Catalog

A custom WordPress product catalog plugin without e-commerce. Products with categories, brands, types, tags, AJAX filtering, and a full admin settings UI.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Advanced Custom Fields PRO
- Node.js 16+ (build step)

## Installation

```bash
cd wp-content/plugins/fs-product-catalog
composer install
npm install && npm run build
```

Then activate the plugin and ensure ACF Pro is active.

## Quick Start

1. Go to **Products** → Add New
2. Add title, content, featured image, and ACF fields
3. Assign categories, brands, types, or tags
4. Configure display at **Products > Settings**
5. View archive at `yoursite.com/product/`

## Settings

All display options are configurable from **Products > Settings**:

- **General** — per page, columns, sort order, pagination mode, breadcrumbs
- **Single Product** — sidebar, related products
- **Archive** — sorting dropdown, filter sidebar
- **Product Card** — visible elements, image ratio
- **Inquiry** — quote request button on single products
- **Quote List** — multi-product quote collection, floating button, form integration
- **Advanced** — REST API toggle

## Documentation

Detailed docs are in the [`docs/`](docs/) folder:

- **[Architecture](docs/architecture.md)** — Plugin structure, classes, data flow
- **[Developer Guide](docs/developer.md)** — Hooks, filters, templates, REST API, import/export
- **[Build & Assets](docs/build.md)** — Build process, CSS/JS architecture
- **[Changelog](CHANGELOG.md)** — Version history

## License

GPL v2 or later

**Author**: [Ajith R N](https://ajithrn.com)
