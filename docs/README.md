# Developer Guide

This document gives a short overview of the internal structure of the plugin.

## Directory Layout

- `admin/` – view templates used in the admin area
- `assets/` – stylesheets, JavaScript and static assets
- `includes/` – PHP classes and AJAX handlers
- `samples/` – example files for the import/export feature
- `languages/` – translation template (`aorp.pot`)

## Custom Post Types

- `aorp_menu_item` – food entries of the menu
- `aorp_drink_item` – drink entries of the menu
- `aorp_ingredient` – ingredient reference posts

The custom taxonomies are `aorp_menu_category` and `aorp_drink_category`.

## AJAX Endpoints

The plugin exposes several AJAX actions for asynchronous administration:

- `aorp_add_item`, `aorp_update_item`, `aorp_delete_item`
- `aorp_add_drink_item`, `aorp_update_drink_item`, `aorp_delete_drink_item`
- `aorp_undo_delete_item`, `aorp_undo_delete_drink_item`

All handlers are implemented in `includes/ajax-handler.php` and return JSON.

## Hooks & Shortcodes

Important hooks register post types, taxonomies and enqueue scripts. The most
visible shortcodes are `[speisekarte]`, `[getraenkekarte]`,
`[aio_ingredients_legend]`, `[restaurant_lightswitcher]` and `[aio_leaflet_map]`.

The number of columns displayed by the food and drink menus can be configured
from the plugin settings page located under the "AIO-Restaurant" menu in the WordPress administration.

## Translations and Samples

Translations are managed with `languages/aorp.pot`. Example import files for CSV,
JSON and YAML are stored in `samples/`. They can be used as a template for bulk
imports.
