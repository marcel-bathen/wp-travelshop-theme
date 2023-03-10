jQuery(function ($) {

    let bookingEntryTransportType = $('.booking-filter-radio--transport-type input[radio]');
    let bookingEntryAirport = $('.booking-filter-item--airport');
    let bookingEntryAirportInput = bookingEntryAirport.find('input');

    // -- switch travel-type
    bookingEntryTransportType.on('click touch', function(e) {

        var thisType = $(this).val();

        if ( thisType == 'FLUG' ) {
            bookingEntryAirport.removeClass('d-none');
        } else {
            bookingEntryAirport.addClass('d-none');
        }

    });

});