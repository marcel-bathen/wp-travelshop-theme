<?php
/**
 * Setup PHP Environment
 */

if(defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY === true) {
    error_reporting(-1);
    ini_set('display_errors', 'On');
    ini_set('max_execution_time', 600);
}

/**
 * the web-path to the wordpress installation, do not set wordpress function like site_url() here.
 * because of the pm-ajax-endpoint.php which is running without a WordPress Bootstrap
 * during installation (install.php) this value will be set to wordpress's site_url()
 */

define('SITE_URL', '');

// Activate multilanguage support
// one big change is the routing, if multilanguage, all routes have the language code as prefix:
// domain.de/de/reisen/schoene-reise/ instead of domain.de/reisen/schoene-reise/
// also we have alternate language html header
define('MULTILANGUAGE_SITE', false);

if(MULTILANGUAGE_SITE === true){
    // Support for WPML multilanguage, if no lang is set, we set a default:
    if(defined('ICL_LANGUAGE_CODE')){

        /* if the language codes are different between wmpl and pressmind, you can build a map like this
        $language_map = ['de-de' => 'de', 'en-gb' => 'en'];
        define('TS_LANGUAGE_CODE', $language_map[ICL_LANGUAGE_CODE]);
        */

        define('TS_LANGUAGE_CODE', ICL_LANGUAGE_CODE);
    }else{
        define('TS_LANGUAGE_CODE', 'de');
    }
}else{
    define('TS_LANGUAGE_CODE', null);
}
/**
 * we' have to renormalize the generic pressmind entities,
 * so it would be a easier handling in this shop context
 * @todo at this moment you have to configure this manually!! pressmind change request #130757
 *          the theme supports only TS_TOUR_PRODUCTS at this moment

 */
define('TS_TOUR_PRODUCTS', 607);
define('TS_HOTEL_PRODUCTS', null);
define('TS_HOLIDAYHOMES_PRODUCTS', null);
define('TS_DAYTRIPS_PRODUCTS', null);
define('TS_DESTINATIONS', null);

/**
 * If your using the blogfeature it's recommend to enable this two auto generated pagetypes
 * if you dont use the blog feature set this pages to false, to avoid unvalid pages in the search engine indexes
 */
define('BLOG_ENABLE_AUTHORPAGE', true);
define('BLOG_ENABLE_CATEGORYPAGE', true);

/**
 * Default visibility level
 * 30 = public
 * 10 = nobody
 * In some development cases it's required to add a other visibility than public (30)
 */
define('TS_VISIBILTY', [30]);


/**
 * TTL of the Object Caching
 */
define('TS_OBJECT_CACHE_TTL', 60);


/**
 * the possible category tree item search fields
 * used in this files:
 *  /template-parts/pm-search/search-bar.php
 *  /template-parts/pm-search/search-bar-plain.php
 * .. to draw the primary search bars.
 *
 */

define('TS_SEARCH', [
    [ 'id_tree' => 1207, 'fieldname' => 'zielgebiet_default', 'name' => 'Zielgebiet', 'condition_type' => 'c'],
    [ 'id_tree' => 1206, 'fieldname' => 'reiseart_default', 'name' => 'Reiseart', 'condition_type' => 'c'],
]);

/**
 * the possible category tree item filters
 * used in /template-parts/pm-search/filter-vertical.php to draw the filter list.
 */
define('TS_FILTERS', [
    [ 'id_tree' => 1207, 'fieldname' => 'zielgebiet_default', 'name' => 'Zielgebiet', 'condition_type' => 'c'],
    [ 'id_tree' => 1206, 'fieldname' => 'reiseart_default', 'name' => 'Reiseart', 'condition_type' => 'c'],
    [ 'id_tree' => 2655, 'fieldname' => 'befoerderung_default', 'name' => 'Beförderung', 'condition_type' => 'c'],
    [ 'id_tree' => 1204, 'fieldname' => 'saison_default', 'name' => 'Saison', 'condition_type' => 'c'],
    // Example of a category tree from a sub object
    [ 'id_tree' => 1205, 'fieldname' => 'sterne_default', 'name' => 'Hotelkategorie', 'condition_type' => 'cl'],
]);

/**
 * Price Format (number_format() is used for rendering)
 */

define('TS_PRICE_DECIMAL_SEPARATOR', ',');
define('TS_PRICE_THOUSANDS_SEPARATOR', '.');
define('TS_PRICE_DECIMALS', 0);

/**
 * Url to the pressmind IB3 if used
 */
define('TS_IBE3_BASE_URL', 'https://demo.pressmind-ibe.net/');

/**
 * Setup Redis,
 */

if(!defined('PM_REDIS_HOST')){
    define('PM_REDIS_HOST', '127.0.0.1');
}

if(!defined('PM_REDIS_PORT')){
    define('PM_REDIS_PORT', '6379');
}

/**
 * Pagebuilder support
 * set to 'beaverbuilder' to load theme specific bb-modules
 * leave empty if no pagebuilder is not used.
 * at this moment we support only beaverbuilder
 */
define('PAGEBUILDER', 'beaverbuilder');


/**
 * Google MAPS API Key
 */
define('TS_GOOGLEMAPS_API', '');