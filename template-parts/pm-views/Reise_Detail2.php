<?php

global $PMTravelShop;

use Pressmind\HelperFunctions;
use Pressmind\Search\CheapestPrice;
use Pressmind\Travelshop\PriceHandler;
use Pressmind\Travelshop\RouteHelper;
use Pressmind\Travelshop\Template;

/**
 * @var array $data
 */

/**
 * @var Pressmind\ORM\Object\MediaObject $mo
 */
$mo = $data['media_object'];


/**
 * @var Custom\MediaType\Reise $moc
 */
$moc = $mo->getDataForLanguage(TS_LANGUAGE_CODE);


$args = [];
$args['pictures'] = [];
foreach($moc->bilder_default as $item){
    if($item->disabled){
        continue;
    }
    $args['pictures'][] = [
        'caption' => $item->caption,
        'copyright' => $item->copyright,
        'url_detail' => $item->getUri('detail', null, 'base'),
        'url_detail_gallery' => $item->getUri('detail_gallery', null, 'base'),
        'url_thumbnail' => $item->getUri('detail_thumb', null, 'square'),
    ];
}


$args['id_media_object'] = $mo->id;
$args['media_object'] = $mo;
$args['id_object_type'] = $mo->id_object_type;
$args['booking_on_request'] = $mo->touristic_base->booking_on_request; // deprecated
$args['booking_type'] = $mo->booking_type;
$args['code'] = $mo->code;
$args['name'] = $mo->name;
$args['headline'] = strip_tags((string)$moc->headline_default);
$args['subline'] = strip_tags((string)$moc->subline_default);
$args['usps'] = $moc->usp_default;
$args['services_included'] = $moc->leistungen_default;
$args['intro'] = $moc->einleitung_default;

/**
 * Set the Cheapest Price, based on the current search parameters
 * @todo add idd and idbp to the cheapest price filter
 */

$CheapestPriceFilter = new CheapestPrice();
$valid_params = [];
if (empty($_GET['pm-dr']) === false) {
    $dateRange = BuildSearch::extractDaterange($_GET['pm-dr']);
    if ($dateRange !== false) {
        $valid_params['pm-dr'] = $_GET['pm-dr'];
        $CheapestPriceFilter->date_from = $dateRange[0];
        //$CheapestPriceFilter->date_to = $dateRange[1];
    }
}
if (empty($_GET['pm-du']) === false) {
    $durationRange = BuildSearch::extractDurationRange($_GET['pm-du']);
    if ($durationRange !== false) {
        $valid_params['pm-du'] = $_GET['pm-du'];
        $CheapestPriceFilter->duration_from = $durationRange[0];
        $CheapestPriceFilter->duration_to = $durationRange[1];
    }
}
if (empty($_GET['pm-pr']) === false) {
    $priceRange = BuildSearch::extractPriceRange($_GET['pm-pr']);
    if ($priceRange !== false) {
        $valid_params['pm-pr'] = $_GET['pm-pr'];
        $CheapestPriceFilter->price_from = $priceRange[0];
        $CheapestPriceFilter->price_to = $priceRange[1];
    }
}

if (empty($_GET['pm-tr']) === false) {
    $transport_types = BuildSearch::extractTransportTypes($_GET['pm-tr']);
    if(!empty($transport_types)){
        $valid_params['pm-tr'] = $_GET['pm-tr'];
        $CheapestPriceFilter->transport_types = $transport_types;
    }
}

if($mo->id_object_type != TS_DAYTRIPS_PRODUCTS){
    //$CheapestPriceFilter->occupancies = [2];
}
$args['cheapest_price'] = $mo->getCheapestPrice($CheapestPriceFilter);

$args['url'] = $mo->getPrettyUrl(TS_LANGUAGE_CODE).(!empty($valid_params) ? '?'.http_build_query($valid_params) : '');

$args['destination'] = !empty($moc->zielgebiet_default[0]->item->name) ? $moc->zielgebiet_default[0]->item->name : null;
$args['travel_type'] = !empty($moc->reiseart_default[0]->item->name) ? $moc->reiseart_default[0]->item->name : null;

// @todo: wirft fehler, bricht javascript
//echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/gtm-detail-datalayer.php', $args);

$args['map_markers'] = [];
foreach ($args['media_object']->getItinerarySteps() as $step) {
    foreach ($step->geopoints as $geopoint) {
        if (!empty($geopoint->lat) && !empty($geopoint->lng)) {
            $args['map_markers'][] = [
                'title' => strip_tags((string)$geopoint->title),
                'lat' => $geopoint->lat,
                'lng' => $geopoint->lng
            ];

        }
    }
}
if(!empty($moc->karte_default[0]) && is_object($moc->karte_default[0])){
    $args['map_url_detail'] = $moc->karte_default[0]->getUri('detail');
    $args['map_url_detail_thumb'] = $moc->karte_default[0]->getUri('detail_thumb');
}
/**
 * Add decriptions blocks
 * <code>
 * $args['descriptions'][] = [
 *          'headline' => '',
 *          'items' => [[
 *              'name' => '',
 * '            text' => '',
 * '            icons' => '',
 * '            pictures' => [
 *              [
 *                  'caption' => '',
 *                  'copyright' => null,
 *                  'url_detail' => null,
 *                  'url_teaser' => null,
 *              ]
 *          ]
 *          ]
 *      ]
 * ];
 * </code>
 *
 */

$args['descriptions'] = [];
$args['descriptions'][] = [
    'headline' => null,
    'type' => 'teaser',
    'items' => [[
        'name' => !empty(strip_tags((string)$moc->beschreibung_headline_default)) ? strip_tags((string)$moc->beschreibung_headline_default) : 'Beschreibung',
        'text' => preg_replace(['/<span[^>]*?class="Head_1"[^>]*>(.*?)<\/span>/', '/\<ul\>/'], ['<h5>$1</h5>', '<ul class="checked-list">'], (string)$moc->beschreibung_text_default),
        'icons' => null,
    ]

    ]
];

if($mo->isCached()){
    $cacheinfo = $mo->getCacheInfo();
    $cachetime = new DateTime($cacheinfo['date']);
    $args['is_cached_since'] = $cachetime->format('d.m.Y H:i');
}

if(!empty($mo->booking_packages[0]->created)){
    $args['booking_package_created_date'] = $mo->booking_packages[0]->created->format('d.m.Y H:i');
}
/**
 * List Housings/Hotels from object link
 */

if(!empty($moc->unterkunftsverknuepfung_default) && is_array($moc->unterkunftsverknuepfung_default)){
    $housings = [];
    foreach ($moc->unterkunftsverknuepfung_default as $key => $link) {
        $linked_mo = new \Pressmind\ORM\Object\MediaObject($link->id_media_object_link, true);
        // if the linked object is not available (in most cases it must be public)
        if (empty($linked_mo->id)) {
            continue;
        }
        /**
         * this is for better code complementation in lovely phpstorm and other ide's
         * @var $linked_moc \Custom\MediaType\Unterkunft
         */
        $linked_moc = $linked_mo->getDataForLanguage(TS_LANGUAGE_CODE);
        $tmp = [];
        $tmp['name'] = strip_tags((string)$linked_mo->name);
        // draw hotel stars if available
        if(!empty($linked_moc->sterne_default[0]->item->name) && intval($linked_moc->sterne_default[0]->item->name) > 0){
            $tmp['icons'] = str_repeat('<svg><use xmlns:xlink="http://www.w3.org/1999/xlink" href="'.get_stylesheet_directory_uri().'/assets/img/phosphor-sprite.svg#star-filled"></use></svg>',
                intval($linked_moc->sterne_default[0]->item->name));
        }
        $tmp['text'] = $linked_moc->beschreibung_text_default;
        $tmp['pictures']  = [];
        foreach($linked_moc->bilder_default as $item){
            if($item->disabled){
                continue;
            }
            $tmp['pictures'][] = [
                'caption' => $item->caption,
                'copyright' => $item->copyright,
                'url_detail' => $item->getUri('detail'),
                'url_teaser' => $item->getUri('teaser'),
            ];
        }
        $housings[] = $tmp;
    }

    if(count($housings) > 0){
        $args['descriptions'][] = [
            'headline' => count($housings) == 1 ? 'Unterkunft' : 'Unterkünfte',
            'type' => 'accordion',
            'items' => $housings
        ];
    }
}
/**
 * List Textbausteine/Textbricks from object links
 */

if(!empty($moc->unterkunftsverknuepfung_default) && is_array($moc->unterkunftsverknuepfung_default)){
    $textbricks = [];
    foreach ($moc->textbaustein_default as $key => $link) {
        $linked_mo = new \Pressmind\ORM\Object\MediaObject($link->id_media_object_link, true);
        // if the linked object is not available (in most cases it must be public)
        if (empty($linked_mo->id)) {
            continue;
        }
        /**
         * this is for better code complementation in lovely phpstorm and other ide's
         * @var $linked_moc \Custom\MediaType\Textbaustein
         */
        $linked_moc = $linked_mo->getDataForLanguage(TS_LANGUAGE_CODE);
        $tmp = [];
        $tmp['name'] = strip_tags((string)$linked_mo->name);
        $tmp['text'] = $linked_moc->text_default;
        $textbricks[] = $tmp;
    }

    if(count($textbricks) > 0){
        $args['descriptions'][] = [
            'headline' => count($textbricks) == 1 ? 'Textbaustein' : 'Textbausteine',
            'type' => 'accordion',
            'items' => $textbricks
        ];
    }
}

/**
 * Example for Tables
 */
$tables = $moc->tabelle_default;
if(is_array($tables) && count($tables) > 0){
    $table_html = $tables[0]->asHTML('table table-hover', true);
    /**
     * if you don't need a html table, try this:
     * table_as_array = $tables[0]->get();
     */
    if(!empty($table_html)){
        $args['descriptions'][] = [
            'headline' => '',
            'type' => 'accordion',
            'items' => [[
                'name' => 'Route',
                'text' => $table_html
            ]]
        ];
    }
}

/**
 * Example for Key Value Fields
 */
$key_value_tables = $moc->key_value_default;
if(is_array($key_value_tables) && count($key_value_tables) > 0){
    $key_value_table_html = $moc->key_value_default[0]->asHTML('table table-hover', true, [
            [
                'value' => 'headline 1',
                'class' => 'red'
            ],
            [
                'value' => 'headline 2',
                'class' => 'red'
            ],
            [
                'value' => 'headline 3',
                'class' => 'red'
            ],
            [
                'value' => 'headline 4',
                'class' => 'red'
            ]
            ,[
                'value' => 'headline 5',
                'class' => 'red'
            ]

        ]
    );
    /**
     * if you don't need a html table, try this:
     * table_as_array = $tables[0]->get();
     */
    if(!empty($key_value_table_html)){
        $args['descriptions'][] = [
            'headline' => '',
            'type' => 'accordion',
            'items' => [[
                'name' => 'Key Value Table Beispiel',
                'text' => $key_value_table_html
            ]]
        ];
    }
}

/**
 * Example for file downloads
 */
$args['downloads'] = [];
if(is_array($moc->upload_default)){
    foreach($moc->upload_default as $file){
        $args['downloads'][] = [
                'description' => $file->description,
                'url' => $file->getUri(),
                'file_name' => $file->file_name,
                'size' => $file->file_size,
        ];
    }
}

/**
 * Breadcrumb
 */
$args['breadcrumb'] = [];
$args['destination_attributes'] = [];
$args['travel_type_attributes'] = [];
$tmp = new stdClass();
$tmp->name = 'Startseite';
$tmp->url = site_url();
$args['breadcrumb'][] = $tmp;
$breadcrumb_search_url = site_url() . '/' . RouteHelper::get_url_by_object_type($mo->id_object_type) . '/';
if (is_array($moc->reiseart_default)) {
    foreach ($moc->reiseart_default as $item) {
        $reiseart = $item->toStdClass();
        $tmp = new stdClass();
        $tmp->name = $reiseart->item->name;
        $tmp->url = $breadcrumb_search_url . '?pm-c[reiseart_default]=' . $reiseart->item->id;
        $args['breadcrumb'][] = $tmp;
        $args['travel_type_attributes'] = $tmp;
    }
}
if (is_array($moc->zielgebiet_default)) {
    foreach ($moc->zielgebiet_default as $item) {
        $zielgebiet = $item->toStdClass();
        $tmp = new stdClass();
        $tmp->name = $zielgebiet->item->name;
        $tmp->url = $breadcrumb_search_url . '?pm-c[zielgebiet_default]=' . $zielgebiet->item->id;
        $args['breadcrumb'][] = $tmp;
        $args['destination_attributes'] = $tmp;
    }
}
$tmp = new stdClass();
$tmp->name = strip_tags((string)$moc->headline_default);
$tmp->url = null;
$args['breadcrumb'][] = $tmp;

// This is an example to join mediaobject by a tree item
// if (!empty($moc->zielgebiet_default[0]->item->id)) {
//     $search = new Pressmind\Search(
//         [
//             \Pressmind\Search\Condition\Visibility::create([30]),
//             \Pressmind\Search\Condition\Category::create('zielgebiet_default', [$moc->zielgebiet_default[0]->item->id]),
//             \Pressmind\Search\Condition\ObjectType::create(1199)
//         ],
//         [
//              'start' => 0,
//              'length' => 1
//         ],
//     );
//
//     $search->setPaginator(Pressmind\Search\Paginator::create(12, 0));
//     $results = $search->getResults(true);
//     foreach ($results as $linkedObject) {
//         /**
//          * @var \Custom\MediaType\Zielgebiete $linkedObjectContent
//          */
//         $linkedObjectContent = $linkedObject->getDataForLanguage();
//         echo "<br>";
//         echo $linkedObjectContent->beschreibung_default."<br>";
//         echo $linkedObject->getId()."<br>";
//         echo $linkedObject->name."<br>";
//     }
//
// }





?>
<div class="content-main content-main--detail" id="content-main">
    <article class="detail-article">
        <div class="detail-section detail-section-topbar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <?php
                        // = = = > load the breadcrumb  < = = =
                        the_breadcrumb(null, null, $args['breadcrumb']);
                        ?>
                    </div>
                    <div class="col-auto">
                        <?php
                        // = = = > simple share button < = = =
                        $share_args = [
                            'title' => 'Reisetipp',
                            'type' => 'Reise',
                            'name' => '',
                            'text' => 'Ich empfehle die Reise',
                            'buttons' => [
                                'facebook' => true,
                                'facebook-messenger' => true,
                                'twitter' => true,
                                'whatsapp' => true,
                                'telegram' => true,
                                'mail' => true,
                                'copy' => true,
                            ]
                        ];

                        $share_object = [
                            'title' => $args['headline'],
                            'image' => $args['pictures'][0]
                        ];
                        echo Template::render(APPLICATION_PATH . '/template-parts/micro-templates/link-sharing.php', ['share_options' => $share_args, 'object' => $share_object]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <section class="detail-section detail-section-header">
            <div class="container">
                <div class="row">
                    <div class="col-12 col-xl-8">
                        <?php
                        // = = = > detail header < = = =
                        $args['galleryOverlayCount'] = 0;
                        echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/detail-head.php', $args);
                        ?>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="detail-header-info">
                            <?php
                                echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/detail-head-info-top.php', $args);

//                                echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/detail-head-info-bottom.php', $args);
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                // = = = > load booking offers modal window < = = =
//                $args_modal = [];
//                $args_modal['title'] = 'Angebot wählen';
//                $args_modal['id_modal'] = $args['id_modal_price_box'];
//                $args['hide_options'] = false;
//                $args_modal['content'] = '<div id="booking-filter"></div><div id="booking-offers"></div>';
//                echo Template::render(APPLICATION_PATH . '/template-parts/layout-blocks/modalscreen.php', $args_modal);
                ?>
            </div>
        </section>


        <section class="detail-section detail-section-content">
            <div class="container">
                <div class="row flex-column-reverse flex-lg-row">
                    <div class="col-12 col-lg-7 col-xl-8">
                        <div class="detail-info-section detail-info-section--intro">
                            <h2><?php echo $args['headline']; ?></h2>
                            <?php if (!empty($args['subline'])) { ?>
                                <p><strong><?php echo $args['subline']; ?></strong></p>
                            <?php } ?>
                            <?php if (!empty($args['usps'])) { ?>
                                <?php echo checklist_formatter($args['usps'], true); ?>
                            <?php } ?>
                            <?php if (!empty($args['intro']) && $args['cheapest_price']->duration > 1) { ?>
                                <p><?php echo $args['intro']; ?></p>
                            <?php } ?>
                        </div>
                        <?php
                        // = = = > itinerary < = = =
                        echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/itinerary.php', $args);

                        // = = = > load common description blocks < = = =
                        if ( !empty($args['descriptions']) ) {
                            echo "<div class='detail-info-section detail-info-section--descriptions'>";
                                echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/description-block.php', $args);
                            echo "</div>";
                        }

                        // = = = > File Downloads < = = =
                        //                    echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/file-download.php', $args);

                        echo "<div class='detail-info-line'>";
                        echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/info-line.php', $args);
                        echo "</div>";

                        ?>
                    </div>
                    <div class="detail-sidebar col-12 col-lg-5 col-xl-4">
                        <div class="detail-booking-entrypoint">
                            <?php
                            // = = = > load the price box < = = =
                            $id_price_box_modal = uniqid();
                            $args['id_modal_price_box'] = $id_price_box_modal;
                            $args['view'] = (isset($_GET) && isset($_GET['view'])) ? $_GET['view'] : 'calendar'; // @todo: steuerung nach anzahl terminen, view "rows", default "calendar"
                            echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/booking-entrypoint.php', $args);

                            // = = = > load the on request row (only shown if the full product is on request < = = =
                            echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/booking-on-request-box.php', $args);
                            ?>
                        </div>

                        <?php
                        // Load services box
                        echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/services-box.php', $args);

                        // Load contact box
                        load_template(get_template_directory().'/template-parts/pm-views/detail-blocks/contact-box.php', false, $args);

                        // Load trust box
                        $args_trust = ['name' => $args['headline']];
                        load_template_transient(get_template_directory().'/template-parts/pm-views/detail-blocks/trust-box.php', false, $args_trust, 0);
                        ?>


                        <div class="detail-sidebar">
                            <?php
                            /*
                            // = = = > load google maps image < = = =
                            echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/gmaps-box.php', $args);

                            // = = = > load static map image (NOT GOOGLE, just an image) < = = =
                            echo Template::render(APPLICATION_PATH . '/template-parts/pm-views/detail-blocks/map-box.php', $args);
                            if(!empty($args['services_included'])) { ?>
                                <div class="detail-services">
                                    <h2>Leistungen</h2>
                                    <?php echo $args['services_included']; ?>
                                </div>
                            <?php } ?>
                            <?php
                            // = = = > load contact box < = = =
                            load_template(get_template_directory().'/template-parts/pm-views/detail-blocks/contact-box.php', false, $args);
                            // = = = > load contact box < = = =
                            $args_trust = ['name' => $args['headline']];
                            load_template_transient(get_template_directory().'/template-parts/pm-views/detail-blocks/trust-box.php', false, $args_trust, 0);
                            */
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php
        // = = = > load sticky footer bar < = = =
        echo Template::render(APPLICATION_PATH.'/template-parts/pm-views/detail-blocks/mobile-bar.php', $args);
        ?>
    </article>
    <div class="detail-section detail-section-crossselling">
        <div class="container">
            <?php
            // = = = > load similiar products < = = =
            $args_similiar = [
                'headline' => 'Kunden buchten auch:',
                'text' => 'Travel is the movement of people between relatively distant geographical locations, and can involve travel by foot, bicycle, automobile, train, boat, bus, airplane, or other means, with or without luggage, and can be one way or round trip.',
                'search' => [
                    'pm-li' => '0,4',
                    'pm-o' => 'rand',
                    'pm-ot' => TS_TOUR_PRODUCTS
                ]
            ];
            echo Template::render(APPLICATION_PATH.'/template-parts/layout-blocks/product-teaser.php', $args_similiar);
            ?>
        </div>
    </div>
</div>
