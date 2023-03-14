jQuery(function ($) {
    let bookingEntry = $('.detail-booking-entrypoint');
    let calendarSlider;
    let calendarSliderIndex = 0;
    let calendarSliderIndexTemp = null;

    // -- todo
    // @todo: rename anchor to offer / in file dateID to offerID

    // -- check if booking entry is loaded, otherwise script not needed

    if ( bookingEntry.length > 0 ) {
        // -- transport type / airport behaviour
        let bookingEntryTransportType = $('.booking-filter-radio--transport-type input[type="radio"]');
        let bookingEntryAirportField = $('.booking-filter-item--airport');

        // -- switch travel-type
        bookingEntryTransportType.on('change', function(e) {
            var thisType = $(this).val();

            if ( thisType == 'FLUG' ) {
                bookingEntryAirportField.removeClass('d-none');
            } else {
                bookingEntryAirportField.addClass('d-none');
            }
        });

        // -- on click calendar
        let bookingEntryCalendar = $('.booking-filter-field--date-range');
        let bookingEntryCalendarRenderTarget = $('#booking-entry-calendar');
        let bookingEntryCalendarServiceUrl = '/wp-content/themes/travelshop/pm-ajax-endpoint.php?action=detail-booking-calendar';

        // -- if click outside calendar
        $(document).click(function(event) {
            var $target = $(event.target);

            // -- daterange
            if ($('.booking-filter-item--date-range').length > 0 && $('.booking-filter-item--date-range').hasClass('open')) {
                if (!$target.hasClass('booking-filter-item--date-range') && $target.parents('.booking-filter-item--date-range').length < 1) {
                    $('.booking-filter-item--date-range').removeClass('open');
                    calendarSliderIndexTemp = null;
                }
            }
        });

        // - click event booking calendar button
        bookingEntryCalendar.on('click touch', function(e) {
            e.preventDefault();

            // -- define some variables needed later
            var getTransportType, getAirport, getDur, getOfferID, getMediaObject = null;

            // -- collect data
            getTransportType = $('.booking-filter-radio--transport-type input[type="radio"]:checked').val();
            getDur = $('.booking-filter-field--duration').val();
            getOfferID = $('.booking-filter-field--offer').val();
            getMediaObject = $('.booking-filter-field--mediaobject').val();

            // -- check transporttype for flight, if yes set airport
            if ( getTransportType === 'FLUG' ) {
                getAirport = bookingEntryAirportField.find('input[type="radio"]:checked').val();
            }

            renderBookingCalendar(getTransportType, getAirport, getDur, getOfferID, getMediaObject);

            // -- close every dropdown
            $('.dropdown, .dropdown-menu').removeClass('show');

            e.stopPropagation();
        });

        /**
         * initiate calendar slider
         * @param dateID
         * @param duration
         */
        function initCalendarSlider() {
            var sliderWrapper = '.booking-entrypoint-calendar-outer';
            var sliderContainer = sliderWrapper + ' .booking-entrypoint-calendar-inner';
            var sliderItems = '.calendar-item';

            if ( $('body').find(sliderWrapper + ' ' + sliderItems).length > 2 ) {
                var storeIndex = parseInt($('.booking-filter-field--slider-index').val())

                if ( storeIndex !== calendarSliderIndex ) {
                    calendarSliderIndex = storeIndex;
                }

                if ( calendarSliderIndex !== calendarSliderIndexTemp && calendarSliderIndexTemp !== null ) {
                    calendarSliderIndex = calendarSliderIndexTemp;
                }

                calendarSlider = tns({
                    container: sliderContainer,
                    items: 2,
                    slideBy: 1,
                    nav: false,
                    mouseDrag: true,
                    loop: false,
                    gutter: 30,
                    disable: true,
                    startIndex: calendarSliderIndex,
                    responsive: {
                        768: {
                           disable: false
                        }
                    },
                    controls: true,
                    controlsContainer: sliderWrapper + ' .slider-controls'
                });

                calendarSlider.events.on('indexChanged', function(){
                    calendarSliderIndexTemp = calendarSlider.getInfo().index;
                })
            }
        }

        /**
         * handling ajax request of calnedar
         * @param request
         */
        function requestHandlerBookingCalendar(request) {

            $.ajax({
                url: bookingEntryCalendarServiceUrl,
                data: request,
                type: 'POST',
                dataType: "html",
                beforeSend: function(xhr) {
                    console.log('before send');
                    // set loading class to calendar
                    bookingEntryCalendarRenderTarget.addClass('is-loading');
                },
                success: function(data) {
                    // remove loading state + render html into target
                    bookingEntryCalendarRenderTarget.html(data);
                    bookingEntryCalendarRenderTarget.removeClass('is-loading');

                    // init calendar slider
                    initCalendarSlider();

                    // show calendar, if not shown already.
                    if ( !$('.booking-filter-item--date-range').hasClass('open') ) {
                        $('.booking-filter-item--date-range').addClass('open');
                    }

                    // init interactive calender things
                    bookingCalendarInteraction();
                }
            })
        }

        /**
         * Refresh booking calendar
         * @param transportType
         * @param airport
         * @param duration
         * @param offerID
         * @param mediaObject
         */
        function refreshBookingCalendar(transportType, airport, duration, offerID, mediaObject) {
            // trigger render option
            renderBookingCalendar(transportType, airport, duration, offerID, mediaObject);
        }

        /**
         * Defines every interaction you can do in calendar
         * + connects calendar to booking entrypoint!
         */
        function bookingCalendarInteraction() {
            var travelDate = bookingEntryCalendarRenderTarget.find('.travel-date a');
            var durationSwitch = bookingEntryCalendarRenderTarget.find('.booking-entrypoint-calender-duration button');

            // handling duration switch
            durationSwitch.on('click touch', function(e) {
                e.preventDefault();

                // -- only if not active one clicked
                if ( !$(this).hasClass('active') ) {
                    // collect data
                    var getAirport = null;
                    var getDur = $(this).data('duration');
                    var getTransportType = $('.booking-filter-radio--transport-type input[type="radio"]:checked').val();
                    var getOfferID = $('.booking-filter-field--offer').val();
                    var getMediaObject = $('.booking-filter-field--mediaobject').val();

                    // -- check transporttype for flight, if yes set airport
                    if ( getTransportType === 'FLUG' ) {
                        getAirport = bookingEntryAirportField.find('input[type="radio"]:checked').val();
                    }

                    // -- reset classes
                    durationSwitch.removeClass('active');

                    // -- set classes
                    $(this).addClass('active');

                    // -- refresh calendar
                    refreshBookingCalendar(getTransportType, getAirport, getDur, getOfferID, getMediaObject);

                }

                e.stopPropagation();
            })

            // handling traveldate preview
            var storeCurrentEnteredTravelDate = null;

            travelDate.on('mouseenter', function(e) {
                if ( !$(this).parent().hasClass('active') ) {
                    var thisTravelDate = $(this);
                    var thisOfferID = thisTravelDate.data('anchor');

                    // store travelDate
                    storeCurrentEnteredTravelDate = $(this);

                    // set active daterange as classes in calendar to preview
                    var thisDaterangeItems = bookingEntryCalendarRenderTarget.find('.travel-date-' + thisOfferID);

                    thisDaterangeItems.addClass('daterange-preview active-duration');
                    thisDaterangeItems.last().addClass('active-duration-last');

                    // bind mouseleave to storedCurrentTraveldate
                    storeCurrentEnteredTravelDate.bind('mouseout', function(e) {
                        var thisDaterangePreviewItems = bookingEntryCalendarRenderTarget.find('.daterange-preview');

                        if ( !$(this).parent().hasClass('active') ) {
                            thisDaterangePreviewItems.removeClass('active-duration');
                            thisDaterangePreviewItems.removeClass('active-duration-last');
                            thisDaterangePreviewItems.removeClass('daterange-preview');
                        } else {
                            // remove preview if is active class
                            thisDaterangePreviewItems.removeClass('daterange-preview');
                        }
                    })
                }
            });

            // unbind mouseout event, not needed anymore
            travelDate.on('mouseout', function(e) {
                $(this).unbind('mouseout');
            });

            // handling active set on traveldate
            travelDate.on('click touch', function(e) {
                e.preventDefault();

                // collect data
                var thisTravelDate = $(this);
                var thisTravelDatePrice = thisTravelDate.data('price-html');
                var thisOfferID = thisTravelDate.data('anchor');
                var thisTravelDateRange = thisTravelDate.data('daterange');
                var thisTravelDateDur = parseInt(thisTravelDate.data('duration'));
                var thisTravelBookingUrl = thisTravelDate.attr('href');

                // reset active date
                bookingEntryCalendarRenderTarget.find('.active-duration').removeClass('active-duration');
                bookingEntryCalendarRenderTarget.find('.active-duration-last').removeClass('active-duration-last');
                bookingEntryCalendarRenderTarget.find('.travel-date.active').removeClass('active');

                // set this active
                thisTravelDate.parent().addClass('active');

                // set active daterange as classes in calendar
                var thisDaterangeItems = bookingEntryCalendarRenderTarget.find('.travel-date-' + thisOfferID);

                thisDaterangeItems.addClass('active-duration');
                thisDaterangeItems.last().addClass('active-duration-last');

                // set dateID to booking entrypoint form
                // set daterange to booking entrypoint form
                $('.booking-filter-field--offer-id').val(thisOfferID);
                $('.booking-filter-field--duration').val(thisTravelDateDur);
                $('.booking-filter-item--date-range .booking-filter-field--text').text(thisTravelDateRange);
                $('.booking-filter-field--bookingurl').val(thisTravelBookingUrl);

                // -- set offer id to booking button
                $('.detail-booking-entrypoint .booking-btn').attr('data-offer-id', thisOfferID);
                $('.detail-booking-entrypoint .booking-btn').attr('href', thisTravelBookingUrl);

                // check if slider is initialized, if yes, get slide index store in variable.
                if ( bookingEntryCalendarRenderTarget.find('.booking-entrypoint-calendar-inner').hasClass('tns-slider') ) {
                    // get slide index of item
                    var calendarSliderInfo = calendarSlider.getInfo();
                    var calendarSliderCurrentIndex = calendarSliderInfo.index;

                    $('.booking-filter-field--slider-index').val(calendarSliderCurrentIndex);
                }

                // refresh price
                if ( thisTravelDatePrice !== '' ) {
                    $('.booking-action-row .price-box-discount').html(thisTravelDatePrice);
                }

                e.stopPropagation();
            })

            // -- close daterange
            $('.booking-calendar-close').on('click touch', function(e) {
                e.preventDefault();

                calendarSliderIndexTemp = null;
                $(this).parents('.booking-filter-item--date-range').removeClass('open');

                e.stopPropagation();
            })
        }

        /**
         * Render booking calendar
         * @param transportType
         * @param airport
         * @param duration
         * @param offerID
         * @param mediaObject
         */
        function renderBookingCalendar(transportType, airport, duration, offerID, mediaObject) {
            // -- request array
            var calendarRequest = {
                'pm-tr': transportType,
                'airport': airport,
                'pm-du': duration,
                'offer-id': offerID,
                'media_object_id': mediaObject
            };

            // -- handle ajax request
            requestHandlerBookingCalendar(calendarRequest);
        }
    }
});