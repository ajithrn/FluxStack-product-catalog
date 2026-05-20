# Build & Assets

## Overview

The plugin uses a minimal npm-based build step. Source files stay readable for development; built output is what the plugin actually loads.

## Setup

```bash
cd wp-content/plugins/fs-product-catalog
npm install
npm run build
```

## Commands

| Command | Description |
|---------|-------------|
| `npm run build` | Build all CSS and JS for production |
| `npm run build:css` | Build CSS only |
| `npm run build:js` | Build JS only |
| `npm run watch` | Watch and rebuild on source file changes |

## How It Works

Source files → concatenated → minified → `assets/dist/`

```
assets/css/frontend-common.css  ─┐
assets/css/frontend-archive.css  ├─→ assets/dist/frontend.min.css
assets/css/frontend-single.css  ─┘

assets/js/frontend-archive.js  ─┐
assets/js/frontend-single.js   ─┴─→ assets/dist/frontend.min.js

assets/css/admin.css           ─┐
assets/css/admin-settings.css  ─┴─→ assets/dist/admin.min.css

assets/js/admin-settings.js    ───→ assets/dist/admin-settings.min.js
```

## Output

| File | Loaded on |
|------|-----------|
| `dist/frontend.min.css` | All product pages (single + archive + search) |
| `dist/frontend.min.js` | All product pages |
| `dist/admin.min.css` | Product admin pages + settings |
| `dist/admin-settings.min.js` | Settings page only |

## Tools

- **[clean-css-cli](https://github.com/clean-css/clean-css-cli)** — CSS minification
- **[terser](https://github.com/terser/terser)** — JS minification
- **[chokidar-cli](https://github.com/open-cli-tools/chokidar-cli)** — File watcher

No webpack, vite, or bundler config needed.

## CSS Architecture

### Source Files

| File | Purpose |
|------|---------|
| `frontend-common.css` | Variables, shared components (sidebar, grid, cards, breadcrumbs) |
| `frontend-archive.css` | Archive-specific (layout, results bar, sort, pagination, load more) |
| `frontend-single.css` | Single product (gallery, lightbox, specs, info, related products) |
| `admin.css` | Admin list table styling |
| `admin-settings.css` | Settings page UI (tabs, toggles, forms, toast) |

### CSS Variables

All values use custom properties defined in `frontend-common.css`:

```css
--fs-primary, --fs-text, --fs-bg, --fs-border
--fs-gap, --fs-gap-sm, --fs-gap-lg
--fs-font-size, --fs-h1 through --fs-h6
--fs-sidebar-width, --fs-border-radius
--fs-transition, --fs-shadow
```

Themes override these in their own `:root` block.

### Naming Convention

BEM-style: `.fs-block__element--modifier`

Examples:
- `.fs-product-card` → `.fs-product-card-title`
- `.fs-filter-group` → `.fs-filter-item--hidden`
- `.fs-settings-field__toggle-slider`

## JS Architecture

### Frontend Modules

Both JS files use an IIFE with module objects:

**frontend-archive.js:**
- `Filters` — AJAX filtering, search debounce, active filters display
- `LoadMore` — Load more button + optional infinite scroll
- `Sidebar` — Show more/less, collapsible sections

**frontend-single.js:**
- `Gallery` — Lightbox, thumbnail switching
- `Tabs` — Specification tab switching
- `ResponsiveTables` — Table scroll toolbar
- `Sidebar` — Show more/less, collapsible sections

### Admin JS

**admin-settings.js:**
- `Tabs` — Settings tab switching
- `Save` — AJAX save with Ctrl+S shortcut
- `ConditionalFields` — Show/hide fields based on other field values

### Conventions

- `const`/`let` only (no `var`)
- Vanilla JS + fetch API (no jQuery on frontend)
- jQuery only in admin where WordPress provides it
- All fetch calls have error handling
- User input debounced (500ms for search)
