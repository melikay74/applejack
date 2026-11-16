# Applejack Ridge Farm: horse database

Custom WordPress plugin for applejackridgefarm.com. It adds a Horses section to the dashboard, a page for every horse, and (later) the search with filters.

## What's here

| Path | What it is |
| --- | --- |
| `applejack-horses/` | The plugin. This folder is what gets uploaded to the live site. |
| `applejack-horses/applejack-horses.php` | Entry point. Loads the files below. |
| `applejack-horses/includes/post-type.php` | Registers the Horse record type and its lists (page category, breed, color, discipline). |
| `applejack-horses/includes/fields.php` | Points Secure Custom Fields (SCF) at `acf-json/`, and sets the first gallery photo as the featured image. |
| `applejack-horses/acf-json/` | The horse form's field definitions. |
| `applejack-horses/includes/helpers.php` | Functions the templates call to read a horse's data. |
| `applejack-horses/includes/templates.php` | Tells WordPress to use our template and stylesheet on horse pages. |
| `applejack-horses/includes/search.php` | The `[horse_search]` shortcode: reads the visitor's choices and finds the matching horses. |
| `applejack-horses/templates/single-horse.php` | The page for one horse. |
| `applejack-horses/templates/horse-search.php` | The search form and results grid. |
| `applejack-horses/templates/horse-card.php` | One horse in the results. |
| `applejack-horses/assets/css/horse.css` | Styles for the horse page. |
| `applejack-horses/assets/css/horse-search.css` | Styles for the search form, grid and card. |
| `bin/wp` | Runs WP-CLI against the local site. |

## Local site

The site runs in the Local app as `applejack` at http://applejack.local. The plugin folder is linked into it, so edits here show up on the local site immediately:

    ~/Local Sites/applejack/app/public/wp-content/plugins/applejack-horses  ->  this repo's applejack-horses/

The plugin needs the Secure Custom Fields plugin to be active.

`bin/wp` works while the site is started in Local:

    bin/wp plugin list
    bin/wp post list --post_type=horse

It has this machine's Local paths written into it. If Local updates its PHP version or the site is recreated, update the paths at the top of the file.

## Putting the search on a page

In Elementor, drag in the Shortcode widget and paste one of these:

    [horse_search]
    [horse_search category="new-foals" orderby="newest"]
    [horse_search category="available-horses"]
    [horse_search category="notables" filters="sex,breed,color,discipline"]

- `category`: `new-foals`, `available-horses` or `notables`. Leave it out for every horse.
- `orderby`: `name` (default) or `newest`.
- `filters`: any of `sex, year, breed, color, height, discipline, status`, or `none`. Leave it out for all seven.
- `search`: `no` hides the search-by-name box.

A filter only offers values that a horse on that page actually has.

## Changing the form fields

Either edit `acf-json/group_ajr_horse_details.json` directly, or in the dashboard go to SCF > Field Groups > Sync available, sync "Horse Details", then edit it there. Saving writes the change back to the JSON file.

## Going live

Code and field definitions go up to the live site. The local database never does.

1. Install and activate Secure Custom Fields on the live site.
2. Zip the `applejack-horses` folder and upload it under Plugins > Add New > Upload Plugin, then activate it.
3. Enter horses on the live site.
