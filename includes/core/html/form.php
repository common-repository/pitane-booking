<script>
    // Google Autocomplete fields
    var pickupAutocomplete = null;
    var destinationAutocomplete = null;    
</script>

<div class="pitane-booking">
    <div class="pitane-form-section">
        <div class="container">
            <div class="pitane-booking-form">
                <div class="pitaneLoader" id="loading_spinner" style="display: none;">
                    <img src="<?php echo esc_url(WP_PITANE_PLUGIN_URL . 'assets/images/pitaneloader.gif');?>" title="Loading" alt="PitaneLoader"  width="150px" height="150px"; />
                </div>
                <form action="" id="pitane-form">
                    <div class="step-one-form">
                        <div class="pitane-booking-form-header">                            
                            <h2><?php esc_attr_e( 'Calculate and book', 'pitanebooking' ); ?></h2>
                            <?php if (!esc_attr(get_option("pitane_api_key"))) 
                            {
                                ?>
                                <label style="margin-left: 40px; color:red;"> <?php echo esc_attr_e( 'Pitane Booking not configured', 'pitanebooking' ) ?></label>
                                <?php
                            } 
                            ?>
                        </div>
                        <div class="pitane-booking-form-body">
                            <div class="pitane-info-title">
                                <p><?php esc_attr_e( '1. Desired route', 'pitanebooking' ); ?></p>
                            </div>
                            <div class="pitane-form-detail search-detail">
                                <div class="justify-content-between">
                                    <div>
                                        <div class="pitane-detail-group">
                                            <label for="pick-up-address"><?php esc_attr_e( 'Pick-up', 'pitanebooking' ) ?></label>
                                            <div class="pitane-form-input">
                                                <span class="pitane-icon pitane-icon-left">
                                                    <i class="fa-solid fa-car"></i>
                                                </span>
                                                <input type="text" name="pick-up-address" id="pick-up-address" class="service_location" placeholder="<?php esc_attr_e( 'Please enter your pick-up address', 'pitanebooking' ) ?>">
                                                <span class="pitane-icon pitane-icon-right" id="destination-close">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </span>
                                            </div>
                                            <span class="error pick-up-text" style="display:none;"><?php esc_attr_e( 'Please enter your pick-up address', 'pitanebooking' );?></span>
                                        </div>
                                    </div>
                                    <div>
                                <div class="pitane-detail-group">
                                    <label for="destination-address"><?php esc_attr_e( 'Destination', 'pitanebooking' );?></label>
                                    <div class="pitane-form-input">
                                        <div class="pitane-icon pitane-icon-left">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </div>
                                        <input type="text" name="destination-address" id="destination-address"  class="service_location" placeholder="<?php esc_attr_e( 'Please enter your destination address', 'pitanebooking' );?>">
                                        <div class="pitane-icon pitane-icon-right" id="destination-close">
                                            <i class="fa-solid fa-xmark"></i>
                                        </div>
                                    </div>
                                    <span class="error destination-text" style="display:none;"><?php esc_attr_e( 'Please enter your destination address', 'pitanebooking' );?></span>
                                    </div>
                                </div>
                            </div>
                                <div class="pitane-detail-group">
                                    <div class="justify-content-between">
                                        <div>
                                            <label for="number-of-person"><?php esc_attr_e( 'Number of persons', 'pitanebooking' );?></label>
                                            <div class="pitane-form-input">
                                                <select id="number-of-person" name="number-of-person">
                                                    <option value="1">1 <?php esc_attr_e( 'person', 'pitanebooking' );?></option>
                                                    <option value="2">2 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="3">3 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="4">4 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="5">5 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="6">6 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="7">7 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                    <option value="8">8 <?php esc_attr_e( 'persons', 'pitanebooking' );?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="car-type"><?php esc_attr_e( 'Car type', 'pitanebooking' );?></label>
                                            <div class="pitane-form-input">
                                                <select id="car-type" name="car-type">
                                                <option class="paxT" value="1"><?php esc_attr_e( 'Taxi (max. 4 persons)', 'pitanebooking' );?></option>
                                                    <option class="paxB" value="2"><?php esc_attr_e( 'Bus (max. 8 persons)', 'pitanebooking' );?></option>
                                                    <option class="paxR" value="3"><?php esc_attr_e( 'Wheelchair bus (max. 8 persons)', 'pitanebooking' );?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="desire-route-detail mt-6">
                                <button type="button" class="pitane-form pitane-booking-form-btnNext" id="form-step1" data="1" data-action="step-two-form"><?php esc_attr_e( 'Calculate price', 'pitanebooking' );?></button>
                            </div>
                            <div class="powered-by">
                                <p>Powered by Pitane Mobility</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-two-form" style="display: none;">
                        <div class="pitane-booking-form-header">                            
                            <h2><?php esc_attr_e( 'Calculate and book', 'pitanebooking' );?></h2>
                        </div>
                        <div class="pitane-booking-form-body">
                            <div class="pitane-info-title">
                                <p><?php esc_attr_e( '2. Trip details', 'pitanebooking' );?></p>
                            </div>
                            <div class="pitane-form-detail search-detail">
                                <div class="pitane-detail-group">
                                    <label><?php esc_attr_e( 'Pick-up', 'pitanebooking' );?></label>
                                    <div class="pitane-form-input">
                                        <p class="result pick-up-text"></p>
                                    </div>
                                </div>
                                <div class="pitane-detail-group">
                                    <label><?php esc_attr_e( 'To', 'pitanebooking' );?></label>
                                    <div class="pitane-form-input">
                                        <p class="result destination-text"></p>
                                    </div>
                                </div>
                                <div class="pitane-detail-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="trip-date"><?php esc_attr_e( 'Date', 'pitanebooking' );?></label>
                                            <input type="date" class="datePicker" name="trip-date" id="trip-date" placeholder="dd-mm-yyyy" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="pick-up-time"><?php esc_attr_e( 'Pick-up time', 'pitanebooking' );?></label>
                                            <div class="pitane-form-input">
                                                <input type="time" name="pick-up-time" id="pick-up-time" value="<?php echo date("H:i", current_time('timestamp') + 600); ?>" min="<?php echo date('H:i') ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="return-option"><?php esc_attr_e( 'Return', 'pitanebooking' );?></label>
                                            <div class="pitane-form-input">
                                                <select id="return-option" name="return-option">
                                                    <option value="yes"><?php esc_attr_e( 'Yes', 'pitanebooking' );?></option>
                                                    <option value="no" selected><?php esc_attr_e( 'No', 'pitanebooking' );?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="pitane-detail-group">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <div class="return-class">
                                                <label for="return-date"><?php esc_attr_e( 'Return date', 'pitanebooking' );?></label>
                                                <input type="date" name="return-date" class="datePicker" id="return-date" placeholder="dd-mm-yyyy" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="return-class">
                                                <label for="return-time"><?php esc_attr_e( 'Return time', 'pitanebooking' );?></label>
                                                <div class="pitane-form-input">
                                                    <input type="time" name="return-time" id="return-time"  value="<?php echo date("H:i", current_time('timestamp') + 600); ?>" min="<?php echo date('H:i') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="pitane-trip-amount">
                                                <i class="fa-solid fa-euro-sign"></i>
                                                <div class="trip-total">
                                                    <p><?php esc_attr_e( 'Trip amount', 'pitanebooking' );?></p>
                                                    <h3 class='trip-amount'>00,00</h3>
                                                    <div class="trip-total-retour" style="display: none; font-size: 12px;">
                                                        <i class="fa-solid fa-euro-sign"></i>
                                                        <?php esc_attr_e( 'Retour amount', 'pitanebooking' );?>&nbsp;&nbsp;&nbsp;<b class='trip-amount-retour'>00,00</b>
                                                    </div>
                                                </div>                                                        
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="trip-detail submit-cta-main mt-5">
                                <button type="button" class="pitane-form pitane-booking-form-btnPrevious prev" id="prev2" data="2" data-action="step-one-form"><i class="fa-solid fa-chevron-left"></i></button>
                                <button type="button" class="pitane-form pitane-booking-form-btnNext" id="next2" data="2" data-action="step-three-form"><?php esc_attr_e( 'Continue', 'pitanebooking' );?></button>
                            </div>
                            <div class="powered-by">
                                <p>Powered by Pitane Mobility</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-three-form" style="display: none;">
                        <div class="pitane-booking-form-header">                            
                            <h2><?php esc_attr_e( 'Calculate and book', 'pitanebooking' );?></h2>
                        </div>
                        <div class="pitane-booking-form-body">
                            <div class="pitane-info-title">
                                <p><?php esc_attr_e( '3. Contact details', 'pitanebooking' ); ?></p>
                            </div>
                            <div class="pitane-form-detail search-detail">
                                <div class="pitane-detail-group contact-detail-group">
                                    <div class="contact-input">
                                        <label class="m-0" for="booking-name"><?php esc_attr_e( 'Your name', 'pitanebooking' );?></label>
                                        <input type="text" name="booking-name" id="booking-name">
                                        <label class="label1 lbl_name" style="display: none;"><?php esc_attr_e( 'Please enter your name', 'pitanebooking' );?></label>
                                    </div>
                                    <div class="contact-input">
                                        <label class="m-0" for="phone-number"><?php esc_attr_e( 'Phone number', 'pitanebooking' );?></label>
                                        <input type="text" name="phone-number" id="phone-number" pattern="^\+?\d{10,13}$" onkeypress="return /[0-9+]/i.test(event.key)" required>
                                        <label class="label1 lbl_phone" style="display: none;"><?php esc_attr_e( 'Please enter your phone number', 'pitanebooking' );?></label>
                                        <label class="label1 lbl_pattern_phone" style="display: none;"><?php esc_attr_e( 'Please enter valid phone number', 'pitanebooking' );?></label>
                                    </div>
                                    <div class="contact-input">
                                        <label class="m-0" for="email"><?php esc_attr_e( 'Email address', 'pitanebooking' );?></label>
                                        <input type="text" name="email" id="email"  pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$" required>
                                        <label class="label1 lbl_email" style="display: none;"><?php esc_attr_e( 'Please enter e-mail address', 'pitanebooking' );?></label>
                                        <label class="label1 lbl_pattern_email" style="display: none;"><?php esc_attr_e( 'Please enter valid e-mail address', 'pitanebooking' );?></label>
                                    </div>
                                    <div class="contact-input contact-textarea">
                                        <label class="m-0" for="extra-information"><?php esc_attr_e( 'Extra information', 'pitanebooking' );?></label>
                                        <textarea type="text" name="extra-information" id="extra-information" rows="4"></textarea>
                                    </div>
                                    <div class="contact-input">
                                        <label class="m-0" for="paymentType"><?php esc_attr_e( 'Payment method', 'pitanebooking' );?></label>
                                        <select id="paymentType" name="paymentType">
                                            <option value="Pay at driver"><?php esc_attr_e( 'Pay at driver', 'pitanebooking' );?></option>
                                            <option class="payOnline" value="Pay online"><?php esc_attr_e( 'Pay online', 'pitanebooking' );?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="trip-detail submit-cta-main mt-5">
                                <button type="button" class="pitane-form pitane-booking-form-btnPrevious prev" id="prev3" data="3" data-action="step-two-form"><i class="fa-solid fa-chevron-left"></i></button>
                                <button type="button" class="pitane-form pitane-booking-form-btnNext" id="next3" data="3" data-action="step-four-form"><?php esc_attr_e( 'Book now', 'pitanebooking' );?></button>
                            </div>
                            <div class="powered-by">
                                <p>Powered by Pitane Mobility</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-four-form" style="display: none;">
                        <div class="pitane-booking-form-header">                            
                            <h2><?php esc_attr_e( 'Calculate and book', 'pitanebooking' ); ?></h2>
                        </div>
                        <div class="pitane-booking-form-body">
                            <div class="pitane-info-title">
                                <p class='tripBooked'><?php esc_attr_e( 'Your trip was booked with trip number', 'pitanebooking' ); ?> <b class="pla-id"></b></p>
                                <p style="display:none" class='tripBookedRetour'><?php esc_attr_e( 'Your trips were booked under trip numbers', 'pitanebooking' ); ?> <b class="pla-id"></b></p>
                                <p style="display:none" class='tripReserved'><?php esc_attr_e( 'Your trip was reserved with trip number', 'pitanebooking' ); ?> <b class="pla-id"></b></p>
                                <p style="display:none" class='tripReservedRetour'><?php esc_attr_e( 'Your trips were reserved under trip numbers', 'pitanebooking' ); ?> <b class="pla-id"></b></p>
                                <p><?php esc_attr_e( 'We will pick you up on', 'pitanebooking' ); ?> <b id="pick-input-date"></b> <?php esc_attr_e( 'at', 'pitanebooking' ); ?> <b id="pick-input-time"></b> <?php esc_attr_e( 'at', 'pitanebooking' ); ?> <br>
                                <b class="result pick-up-text" id="pick-up-input-address"></b></p>
                                <p><a style="display: none;" class="gate12_booking_url"></a>
                                <p><?php esc_attr_e( 'We will bring you to the following destination', 'pitanebooking' ); ?><br><b class="result destination-text" id="destination-input-address"></b></p>
                                <p><?php esc_attr_e( 'If we have additional questions we will call you by phone', 'pitanebooking' ); ?> <br><b id="phone-input-number"></b></p>
                                <p><?php esc_attr_e( 'We will send you an email with all information regarding this booking. You ', 'pitanebooking' ); ?> <b id="phone-input-email"></b>.</p>
                                <p style="display:none" class='tripReserved_instructions'><?php esc_attr_e( 'Please pay your reservation online in order to complete your booking', 'pitanebooking' ); ?></p>
                            </div>
                            <div class="trip-detail submit-cta-main mt-6">
                                <button type="button" class="pitane-form pitane-booking-form-btnNext" id="next4" data="4" data-action="step-one-form"><?php esc_attr_e( 'New Booking', 'pitanebooking' ); ?></button>
                                <button type="button" class="pitane-form pitane-booking-form-btnNext" id="gate12Booking" style="display:none"><?php esc_attr_e( 'New Booking', 'pitanebooking' ); ?></button>
                            </div>
                            <div class="powered-by">
                                <p>Powered by Pitane Mobility</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        function initMap() 
        {
            var countries = '<?php echo esc_attr(get_option('google_countries')); ?>';
            if (countries)
            {
                countries =  countries.split(',').map(element => element.trim());
            }

            document.querySelectorAll('.service_location').forEach(function (postal) 
            {
                // Google autocomplete parameters
                var options = 
                {
                   fields: ["address_components", "geometry"],                 
                   componentRestrictions: 
                   { 
                        country: countries
                   },
                }

                if (postal.id == 'pick-up-address')
                {
                    pickupAutocomplete = new google.maps.places.Autocomplete(postal, options);
                }

                if (postal.id == 'destination-address')
                {
                    destinationAutocomplete = new google.maps.places.Autocomplete(postal, options);
                }
            });    

            if (Array.isArray(countries))    
            {
                console.log('Google maps loaded with countries restricted to [' + countries + ']');
            }
        }
    </script>