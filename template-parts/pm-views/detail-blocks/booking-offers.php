<?php
use Pressmind\Search\CheapestPrice;
use Pressmind\Travelshop\PriceHandler;
use Pressmind\Travelshop\Template;

/**
 * <code>
 * $args['cheapest_price']
 * $args['media_object']
 * $args['url']
 * </code>
 * @var array $args
 */

$filter = new CheapestPrice();
//$filter->duration_from = $args['cheapest_price']->duration;
//$filter->duration_to = $args['cheapest_price']->duration;
//$filter->transport_types = $args['cheapest_price']->transport_type;
$filter->occupancies_disable_fallback = true;
/**
 * @var \Pressmind\ORM\Object\CheapestPriceSpeed[] $offers
 */
$offers = $args['media_object']->getCheapestPrices($filter, ['date_departure' => 'ASC', 'price_total' => 'ASC'], [0, 100]);
$durations = [];
$airports_departure = [];
$has_flights = false;
foreach($offers as $key => $offer) {
    $durations[$offer->duration] = true;
    if(preg_match('/FLU/', $offer->transport_type) > 0){
        $has_flights = true;
        if(!emptY($offer->transport_1_airport) && !empty($offer->transport_1_airport_name)){
            $airports_departure[$offer->transport_1_airport] = $offer->transport_1_airport_name;
        }
    }
}

if (!empty($offers)) { ?>
    <div class="booking-filter">
        <select class="form-control duration-select">
            <option value="all" selected>Dauer wählen</option>
            <?php foreach($durations as $key => $value) { ?>
                <option value="<?php echo $key; ?>"> <?php echo $key == 1 ? 'Tagesfahrt' : $key . ' Tage' ?> </option>
            <?php } ?>
        </select>
        <?php if($has_flights){ ?>
            <select class="form-control airport-select">
                <option value="all" selected>Flughafen wählen</option>
                <?php foreach($airports_departure as $key => $value) { ?>
                    <option value="<?php echo $key; ?>"> <?php echo $value;?> </option>
                <?php } ?>
            </select>
        <?php } ?>
    </div>
    <section class="content-block content-block-detail-booking" id="content-block-detail-booking">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content-block-detail-booking-inner">
                        <?php
                        $current_month = null;
                        foreach ($offers as $offer) {

                            if($current_month != $offer->date_departure->format('Y-m')){
                                $current_month = $offer->date_departure->format('Y-m');
                            ?>

                            <div class="booking-row no-gutters row booking-row-head d-none d-lg-flex">
                                <div class="col-12">
                                    <h2><?php
                                        echo Template::render(APPLICATION_PATH . '/template-parts/micro-templates/month-name.php', [
                                                'date' => $offer->date_departure]);
                                        ?></h2>
                                </div>
                            </div>
                            <div class="booking-row no-gutters row booking-row-head d-none d-lg-flex">
                                <div class="col-2">
                                    Dauer
                                </div>
                                <div class="col-3">
                                    <?php
                                    if($offer->duration == 1){
                                        ?>Datum<?php
                                    }else{
                                        ?>Zeitraum<?php
                                    }
                                    ?>
                                </div>
                                <div class="col-3">
                                    Leistung
                                </div>
                                <div class="col-2">
                                    Preis pro Person
                                </div>
                            </div>

                            <?php } ?>
                            <?php


                            $checked = ($args['cheapest_price']->id == $offer->getId());
                            ?>
                            <div data-duration="<?php echo $offer->duration; ?>" data-airport="<?php echo $offer->transport_1_airport; ?>" class="booking-row no-gutters row booking-row-date<?php echo $checked ? ' checked' : ''; ?>">
                                <?php 
                                    echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/checked-icon.php', []);
                                ?>
                                <div class="col-12 col-lg-2">
                                    <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/duration-icon.php', []);?>
                                    <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/duration.php', ['duration' => $offer->duration]);?>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/transport-icon.php', ['transport_type' => $offer->transport_type]);?>
                                    <span class="date">
                                        <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/travel-date-range.php', [
                                                'date_departure' => $offer->date_departure,
                                                'date_arrival' => $offer->date_arrival
                                        ]);?>
                                    </span>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/price-mix-icon.php', ['price_mix' => $offer->price_mix]);?>
                                    <div>
                                        <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/offer-description.php', ['cheapest_price' => $offer]);?>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-2 price-container">
                                    <?php
                                    if (($discount = PriceHandler::getDiscount($offer)) !== false) {
                                         echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/discount.php', [
                                                'cheapest_price' => $offer,
                                                'discount' => $discount,
                                        ]);
                                    } else {
                                        echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/price.php', [
                                            'cheapest_price' => $offer,
                                        ]);
                                    } ?>

                                </div>
                                <div class="col-12 col-lg-2">
                                    <?php // Random Availability
                                    $randint = random_int(1, 20);
                                    ?>
                                    <div class="booking-button-wrap">
                                        <?php echo Template::render(APPLICATION_PATH.'/template-parts/micro-templates/booking-button.php', [
                                                'cheapest_price' => $offer,
                                                'url' => $args['url'],
                                                'disable_id' => false
                                        ]);?>
                                        <?php if($randint < 10) { ?>
                                            <!-- Toggle in badge the class "active" to toggle status with animation -->
                                            <div class="badge status active <?php echo $randint <= 3 ? 'alert' : ''; ?>">Nur noch <?php echo $randint < 10 ? $randint == 1 ? '1 Platz' : $randint . ' Plätze ' : ''; ?> frei</div>
                                        <?php } ?>
                                    </div>
                                </div>

                                <!--
                                <div class="bottom-bar">
                                    <div class="col-12 col-lg-2">
                                        <span>anstatt</span> <strong>649,00 €</strong>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <span>EZZ</span> <strong>100,00 €</strong>
                                    </div>
                                </div>
                            -->
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </section>

<?php } else { ?>
    <section>
        <div class="content-block content-block-detail-booking" id="content-block-detail-booking">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <small>Es konnten keine gültigen Angebote gefunden werden. Bitte wenden Sie sich an
                            unser Service-Center.</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>