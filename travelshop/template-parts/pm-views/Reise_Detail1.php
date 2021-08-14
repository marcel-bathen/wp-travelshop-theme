<?php

global $PMTravelShop;

use Pressmind\HelperFunctions;
use Pressmind\Search\CheapestPrice;

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


/**
 * Set the Cheapest Price, based on the current search parameters
 */

$CheapestPriceFilter = new CheapestPrice();
if (empty($_GET['pm-dr']) === false) {
    $dateRange = BuildSearch::extractDaterange($_GET['pm-dr']);
    if ($dateRange !== false) {
        $CheapestPriceFilter->date_from = $dateRange[0];
        $CheapestPriceFilter->date_to = $dateRange[1];
    }
}

if (empty($_GET['pm-du']) === false) {
    $durationRange = BuildSearch::extractDurationRange($_GET['pm-du']);
    if ($durationRange !== false) {
        $CheapestPriceFilter->duration_from = $durationRange[0];
        $CheapestPriceFilter->duration_to = $durationRange[1];
    }
}


if (empty($_GET['pm-pr']) === false) {
    $priceRange = BuildSearch::extractPriceRange($_GET['pm-pr']);
    if ($priceRange !== false) {
        $CheapestPriceFilter->price_from = $priceRange[0];
        $CheapestPriceFilter->price_to = $priceRange[1];
    }
}

//@todo add idd and idbp to the cheapest price

$CheapestPriceFilter->occupancies = [2];

$cheapest_price = $mo->getCheapestPrice($CheapestPriceFilter);

?>
<div class="content-main">
    <article>
        <?php

            $breadcrumb = array();
            $tmp = new stdClass();
            $tmp->name = 'Startseite';
            $tmp->url = site_url();
            $breadcrumb[] = $tmp;


            $breadcrumb_search_url = site_url() . '/' . $PMTravelShop->RouteProcessor->get_url_by_object_type($mo->id_object_type) . '/';
            if (is_array($moc->reiseart_default)) {
                foreach ($moc->reiseart_default as $item) {
                    $reiseart = $item->toStdClass();
                    $tmp = new stdClass();
                    $tmp->name = $reiseart->item->name;
                    $tmp->url = $breadcrumb_search_url . '?pm-c[reiseart_default]=' . $reiseart->item->id;
                    $breadcrumb[] = $tmp;
                }
            }
            if (is_array($moc->zielgebiet_default)) {
                foreach ($moc->zielgebiet_default as $item) {
                    $zielgebiet = $item->toStdClass();
                    $tmp = new stdClass();
                    $tmp->name = $zielgebiet->item->name;
                    $tmp->url = $breadcrumb_search_url . '?pm-c[zielgebiet_default]=' . $zielgebiet->item->id;;
                    $breadcrumb[] = $tmp;
                }
            }


            $tmp = new stdClass();
            $tmp->name = strip_tags($moc->headline_default);
            $tmp->url = null;
            $breadcrumb[] = $tmp;

            the_breadcrumb(null, null, $breadcrumb);
            ?>
        <div class="container">
            <section class="content-block content-block-detail-header">
                <p>
                    <?php
                        $badges = array();
                        if (is_array($moc->reiseart_default)) {
                            foreach ($moc->reiseart_default as $mocart_default_item) {
                                $mocart = $mocart_default_item->toStdClass();
                                $badges[] = $mocart->item->name;
                            }
                        }

                        if (is_array($moc->zielgebiet_default)) {
                            foreach ($moc->zielgebiet_default as $k => $zielgebiet_default_item) {
                                $zielgebiet = $zielgebiet_default_item->toStdClass();
                                $badges[] = $zielgebiet->item->name;
                            }
                        }
                        if (!empty($badges)) {
                            echo '<span class="badge badge-secondary">' . implode('</span> <span class="badge badge-secondary">', $badges) . '</span>';
                        }
                        ?>
                </p>
                <h1><?php echo strip_tags($mo->name); ?></h1>
                <p class="small"><?php echo strip_tags($moc->subline_default); ?></p>
            </section>
            <section class="content-block content-block-detail-info-grid">
                <div class="row">
                    <div class="col-12 col-lg-9">
                        <?php
                            // Imagebrowser
                            load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/image-browser.php', false, $moc->bilder_default);
                            ?>
                        <!-- Price Box underneath the image gallery for mobile devices-->
                        <div class="d-block d-lg-none">
                            <?php
                                // pricepox
                                load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/price-box.php', false, array_merge($data, array('cheapest_price' => $cheapest_price)));
                                ?>
                        </div>
                        <div class="detail-reise-content">
                            <p>
                                <?php echo $moc->einleitung_default; ?>
                            </p>
                        </div>
                        <?php
                            // tabs
                            load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/tabs.php', false, array('moc' => $moc, 'mo' => $mo, 'cheapest_price' => $cheapest_price));
                            ?>
                    </div>
                    <div class="d-none d-lg-block col-lg-3">
                        <!-- Price Box beside the image gallery for desktop devices-->
                        <div class="sticky-container">
                            <?php
                                // pricepox
                                load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/price-box.php', false, array_merge($data, array('cheapest_price' => $cheapest_price)));
                                ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <?php
            // Detail Booking
            load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/booking-offers.php', false, array_merge($data, array('cheapest_price' => $cheapest_price)));
            ?>
</div>
</div>
<?php
// Gallery
load_template(get_template_directory() . '/template-parts/pm-views/detail-blocks/gallery.php', false, $moc->bilder_default);