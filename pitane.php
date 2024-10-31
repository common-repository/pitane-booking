<?php
/*
Plugin Name: Pitane booking
Plugin URI: https://agendapakket.nl/toepassingen/wordpress/
Description: Pitane Booking Shortcode [pitane_plugin]
Version: 1.2
Author: Pitane B.V.
Author URI: https://www.pitane.nl/
Tags: pitane, booking, boeken, taxi
Text Domain: pitanebooking
Domain Path: /languages
*/

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
}

/**
 * Define Plugin URL and Directory Path
 */
define('WP_PITANE_PLUGIN_URL', plugins_url('/', __FILE__)); // Define Plugin URL
define('WP_PITANE_PLUGIN_DIR', plugin_dir_path(__FILE__)); // Define Plugin Directory Path

define('PITANEBOOKING', 'pitane_booking');

function pitanebooking_error_handler($severity, $message, $filename, $lineno) 
{
    if (str_contains(strtolower($filename), 'pitane') && !str_contains(strtolower($message), 'session_start')) 
    {     
        pitanebooking_logToDatabase($message . ' => LINE: ' . $lineno . ' fileName: ' . $filename, "PHP_ERROR");
    }
}

function pitanebooking_logToDatabase($message, $type="PLUGIN_MESSAGE")
{
    try
    {
          global $wpdb;
          $table_name = "PitaneBooking_logs";
          $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                                    `id` int(10) NOT NULL AUTO_INCREMENT,
                                    `date` VARCHAR(100) NOT NULL,
                                    `message` TEXT NOT NULL,
                                    `type` VARCHAR(100) NOT NULL,
                                    PRIMARY KEY (`id`)
                                    );" ;

          $wpdb->query($sql);
          $wpdb->insert($table_name, array(
                           'date' => date('Y-m-d H:i:s'), 'message' => $message, 'type' => $type));
    }
    catch (Exception $e)
    {
        // Do nothing
    }
}

function pitanebooking_plugin_load_plugin_textdomain()
{
    $domain = 'pitanebooking';
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

    // wp-content/languages/pitanebooking/pitanebooking-en_US.mo
    load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

    // wp-content/plugins/pitanebooking/languages/pitanebooking-en_US.mo
    load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

function pitanebooking_get_option($value)
{
    return sanitize_text_field(get_option($value));
}

class PitaneBooking
{
    private $DEFAULT_URL = null;
    private $DEFAULT_PORT = null;
    private $API_LOGGING = false;

    private $IDENTIFICATION = 'WORDPRESS';

    function __construct()
    {
        if (!session_id())
        {
            session_start();
        }

        $this->API_LOGGING = defined('WP_DEBUG') && WP_DEBUG === true;
           
        set_error_handler('pitanebooking_error_handler');

        include WP_PITANE_PLUGIN_DIR . 'includes/core/html/helpers/emailHelper.php';

        $this->DEFAULT_URL = pitanebooking_get_option('pitane_api_url');

        if (empty($this->DEFAULT_URL))
        {
            $this->DEFAULT_URL = 'https://api.pitane.dev';
        }

        $this->DEFAULT_PORT = pitanebooking_get_option('pitane_api_port');

        if (empty($this->DEFAULT_PORT))
        {
            $this->DEFAULT_PORT = 443;
        }

        // enqueue translations
        add_action('init', 'pitanebooking_plugin_load_plugin_textdomain');
        
        add_action('wp_enqueue_scripts', array(
            $this,
            'pitane_plugin_scripts'
        ) , 20);

        // admin enqueue scripts
        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_pitane_plugin_scripts'
        ) , 20);

        include_once(WP_PITANE_PLUGIN_DIR . 'includes/pitane_plugin_options.php');

        // include files
        add_action('init', array(
            $this,
            'pitane_plugin_shortcode_include_files'
        ));

        add_action('wp_head', array(
            $this,
            'global_variable_css_add'
        ) , 1);

        add_action('wp_ajax_nopriv_service_booking_form_submit', array(
            $this,
            'service_booking_form_submit_handler'
        ));
        
        add_action('wp_ajax_service_booking_form_submit', array(
            $this,
            'service_booking_form_submit_handler'
        ));
    }

    function hasShortcode($posts)
    {
        if (empty($posts))
        {
            return false;
        }

        $found = false;

        foreach ($posts as $post) 
        {
            // check the post content for the short code
            $found = has_shortcode($post->post_content, 'pitane_plugin');
            if ($found)
            {
                // stop the search
                break;
            }
        }
        return $found;
    }

    private function getApiUrl($method)
    {
        if (empty(pitanebooking_get_option('pitane_api_key')))
        {
            pitanebooking_logToDatabase("Cannot create api request, the pitane key is not configured", "ERROR");
            return;             
        }

        if (empty(pitanebooking_get_option('pitane_api_key')))
        {
            pitanebooking_logToDatabase("Cannot create api request, the pitane key is not configured", "ERROR");
            return;             
        }

        $key = pitanebooking_get_option('pitane_api_key');
        $url = rtrim($this->DEFAULT_URL, '/');
         
        $serviceSpecification = "/pitane/rest/TPV3/Service/" . $key . '/json';
        return $url . ':' . $this->DEFAULT_PORT . $serviceSpecification . '/'. $method;
    }

    private function getSelectedVehicle($selectedVehicle)
    {
        if ($selectedVehicle == 1)
        {
            $selectedVehicle = 'T';
        }
        else if ($selectedVehicle == 2)
        {
            $selectedVehicle = 'B';
        }
        else if ($selectedVehicle == 3)
        {
            $selectedVehicle = 'R';
        }
        else
        {
            $selectedVehicle = 'T';
        }
        return $selectedVehicle;
    }

    function CalculateTripDistance($pickupPlace, $destinationPlace)
    {
        if (empty($pickupPlace) || empty($destinationPlace))
        {
            pitanebooking_logToDatabase('Cannot calculate trip distance, pickup or destination places are empty');
            return null;
        }

        if (empty($pickupPlace["gps"]) || empty($destinationPlace["gps"]))
        {
            pitanebooking_logToDatabase('Invalid gps coordinates found => pickup: ' . $pickupPlace["gps"] . ' destination: ' . $destinationPlace["gps"]);
            return null;
        }

        $url = $this->getApiUrl('pitaneRoutingDistancebyLatLong');
        $data = array(
            'oph_latitude' => round($pickupPlace["gps"]["latitude"],7),
            'oph_longitude' => round($pickupPlace["gps"]["longitude"],7),
            'best_latitude' => round($destinationPlace["gps"]["latitude"],7),
            'best_longitude' => round($destinationPlace["gps"]["longitude"],7),
        );
 
        $response = $this->apiRequestPost($url, $data);
        return $response;
    }

    function CalculateTripAmount($pickupPlace, $destinationPlace, $carType, $passengerAmount, $wheelchairAmount, $isRetour = false)
    {
        $carType = $this->sanitize($carType);
        $passengerAmount = $this->sanitize($passengerAmount);
        $wheelchairAmount = $this->sanitize($wheelchairAmount);

        $url = $this->getApiUrl('pitaneCalculateTripAmount');
        $selectedVehicle = $this->getSelectedVehicle($carType);

        // Retrieve the total kilometers
        $distance = $this->CalculateTripDistance($pickupPlace, $destinationPlace);

        if (empty($distance->pitanedistance))
        {
            pitanebooking_logToDatabase('The distance response object is empty, cannot continue');
            return null;
        }

        $travelTime = $distance->pitanedistance[0]->traveltime;
        $distance = $distance->pitanedistance[0]->distance;

        if ($isRetour)
        {
            // Show retour values
            $_SESSION['travelTimeRetour'] = $this->sanitize($travelTime);
            $_SESSION['travelDistanceRetour'] = $this->sanitize($distance);
        }
        else
        {
            $_SESSION['travelTime'] = $this->sanitize($travelTime);
            $_SESSION['travelDistance'] = $this->sanitize($distance);
        }

        $data = array(
            'trip_update' => '0',
            'best_nummer' => esc_attr($destinationPlace['streetNumber']),
            'best_postcode' => esc_attr($destinationPlace['postalCode']),
            'oph_nummer' => esc_attr($pickupPlace['streetNumber']),
            'oph_postcode' => esc_attr($pickupPlace['postalCode']),
            'bedrag' => '0.00',
            'tarief' => pitanebooking_get_option("tariff$selectedVehicle"),
            'ritnummer' => '0',
            'reistijd_onbeladen' => "$travelTime",
            'reistijd_beladen' => "$travelTime",
            'reistijd' => "$travelTime",
            'kilometers_onbeladen' => "$distance",
            'kilometers_beladen' => "$distance",
            'kilometers' => "$distance",
            'sv' => pitanebooking_get_option('rei_vor_id'),
            'sw' => $selectedVehicle,
            'lopers' => $passengerAmount,
            'rollers' => $wheelchairAmount,
        );
        
        $getParameters = http_build_query($data);
        $response = $this->apiRequestGet($url . '?' . $getParameters);
        return $response;
    }

   function sanitize($input) {
        if( is_string($input) ){
            $input = sanitize_text_field($input);
        }
        elseif ( is_array($input) ){
            foreach ( $input as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = $this->sanitize($value);
                }
                else {
                    $value = sanitize_text_field( $value );
                }
            }
        }
        return $input;
    }

    private function apiRequestPost($url, $data, $skipPostSanitize = false)
    {
        if (!$skipPostSanitize)
        {
            $data = $this->sanitize($data);
        }

        $args = array(
            'method' => 'POST',
            'headers'  => array(
                'Content-type: application/json'
            ),
            'body' => json_encode($data),
        );

        if ($this->API_LOGGING === true)
        {
            pitanebooking_logToDatabase("REQUEST: " . $url . ' => ' . print_r($args, true), "API_REQUEST");
        }

        $originalResponse  = wp_remote_post($url, $args);
        if (is_wp_error($originalResponse)) 
        {
            var_dump($originalResponse, $url);
            return false;
        }

        $response = $this->createResponse($originalResponse);

        // Strip variables that we dont need to show in the frontend
        $response = $this->stripResponse($response);
        $response = $this->validateResponse($response);

        return $response;
    }

    private function stripResponse($response)
    {
        if ($response != null)
        {
            // Strip variables that we dont need to show in the frontend
            unset($response->ip);
            unset($response->output);
            unset($response->key);
            unset($response->records);
            unset($response->request);
            unset($response->parameters);
            unset($response->method);
            unset($response->servertime);
            unset($response->serverseconds);        
        }   
        return $response;
    }

    private function getErrorMessage($response)
    {
        if ($response === NULL) 
        {
            return 'EMPTY RESPONSE!';
        }

        if (!empty($response->transaction) && $response->transaction[0]->transactionstatus == "500") 
        {
            pitanebooking_logToDatabase("RESPONSE: " . print_r($response, true), "API_RESPONSE_ERROR");
            return 'The api returned an error, check logs';
        }

        if (!empty($response->error))
        {
            pitanebooking_logToDatabase("RESPONSE: " . print_r($response, true), "API_RESPONSE_ERROR");
            return $response->error[0]->errormessage;
        }
        return "UNKNOWN API ERROR";
    }

    private function validateResponse($response)
    {
        // If we got an error or an empty response, send our custom response
        if ($response === NULL || !empty($response->error) && $response->error !== null || !empty($response->transaction) && $response->transaction[0]->transactionstatus == "500")
        {
            $returnResponse = new stdClass();
            $returnResponse->message = $this->getErrorMessage($response);
            return $returnResponse;
        }
        return $response;
    }

    private function apiRequestGet($url)
    {
        if ($this->API_LOGGING === true)
        {
            pitanebooking_logToDatabase("REQUEST: " . $url, "API_REQUEST");
        }

        $originalResponse  = wp_remote_get($url);

        if (is_wp_error($originalResponse)) 
        {
            var_dump($originalResponse, $url);
            return false;
        }

        $response = $this->createResponse($originalResponse);

        // Strip variables that we dont need to show in the frontend
        $response = $this->stripResponse($response);
        $response = $this->validateResponse($response);

        return $response;
    }

    private function convertToApiDate($date)
    {
        $date = str_replace('/', '-', $date);
        $date = str_replace(' ', 'T', $date);
        return $date;
    }

    private function getCountryCodeFromCountry($country)
    {
        if (strlen($country) < 4)
        {
            return $country;
        }

        require_once WP_PITANE_PLUGIN_DIR .'includes\countrycodes.php';
        if (strtolower($country) == 'nederland' || strtolower($country) == 'netherlands' || strtolower($country) == 'the netherlands')
        {
            return "NL";
        }
         
        if (strtolower($country) == 'belgie' || strtolower($country) == 'belgium')
        {
            return "BE";
        }
        
        $iso_code = array_search(strtolower($country), array_map('strtolower', $countrycodes));
        return $iso_code;
    }

    private function pitaneSendTripInsert($isRetour = false)
    {
        $url = $this->getApiUrl('pitaneSendTripInsert');
        $selectedVehicle = $this->getSelectedVehicle($this->sanitize($_SESSION['carType']));
        $selectedTariff = pitanebooking_get_option("tariff" . $selectedVehicle);

        $bookingDate = $isRetour ? ($this->convertToApiDate($this->sanitize($_SESSION['returnDate'])) . ' ' . $this->sanitize($_SESSION['returnTime'])) : ($this->convertToApiDate($this->sanitize($_SESSION['tripDate']) . ' ' . $this->sanitize($_SESSION['pickupTime'])));
        if (!$bookingDate)
        {
            return;
        }

        $pickupPlace = $isRetour ? $this->sanitize($_SESSION['destinationPlace']) : $this->sanitize($_SESSION['pickupPlace']);

        if (!empty($pickupPlace))
        {
            $pickupStreetNumber = $this->sanitize($pickupPlace['streetNumber']);
            $pickupStreet = $this->sanitize($pickupPlace['street']);
            $pickupLocality = $this->sanitize($pickupPlace['locality']);
            $pickupArea = $this->sanitize($pickupPlace['area']);
            $pickupPostalCode = $this->sanitize($pickupPlace['postalCode']);
            $pickupCountry = $this->sanitize($pickupPlace['country']);
            $pickupCountryCode = $this->sanitize($pickupPlace['countryCode']);
        }
        else
        {
            $pickupStreet = $isRetour ? $this->sanitize($_SESSION['destinationAddress']) : $this->sanitize($_SESSION['pickupAddress']);
            $pickupStreetNumber = '';
            $pickupLocality = '';
            $pickupArea = '';
            $pickupPostalCode = '';
            $pickupCountry = '';
            $pickupCountryCode = '';
        }

        $destinationPlace = $isRetour ? $this->sanitize($_SESSION['pickupPlace']) : $this->sanitize($_SESSION['destinationPlace']);
        if (!empty($destinationPlace))
        {
            $destinationStreetNumber = $this->sanitize($destinationPlace['streetNumber']);
            $destinationStreet = $this->sanitize($destinationPlace['street']);
            $destinationLocality = $this->sanitize($destinationPlace['locality']);
            $destinationArea = $this->sanitize($destinationPlace['area']);
            $destinationPostalCode = $this->sanitize($destinationPlace['postalCode']);
            $destinationCountry = $this->sanitize($destinationPlace['country']);
            $destinationCountryCode = $this->sanitize($destinationPlace['countryCode']);
        }
        else
        {
            $destinationStreet = $isRetour ? $this->sanitize($_SESSION['pickupAddress']) : $this->sanitize($_SESSION['destinationAddress']);
            $destinationStreetNumber = '';
            $destinationLocality = '';
            $destinationArea = '';
            $destinationPostalCode = '';
            $destinationCountry = '';
            $destinationCountryCode = '';
        }

        $pickupCountryCode = empty($pickupCountryCode) ? $this->getCountryCodeFromCountry($pickupCountry) : $pickupCountryCode;
        $destinationCountryCode = empty($destinationCountryCode) ? $this->getCountryCodeFromCountry($destinationCountry) : $destinationCountryCode;

        $filter = pitanebooking_get_option('filter');
        if (!$filter)
        {
            $filter = '';
        }

        $data = array(
            'pla_reiziger' => pitanebooking_get_option('rei_id'),
            'pla_datum' => $bookingDate,
            'pla_status' => $_SESSION['paymentType'] != "4" ? 'W' : 'P',
            'pla_tijdstip' => $bookingDate,
            'pla_tijdstip_locatie' => $bookingDate,
            'pla_ophstraat' => $pickupStreet,
            'pla_ophnummer' => $pickupStreetNumber,
            'pla_ophpostcode' => $pickupPostalCode,
            'pla_ophstad' => $pickupLocality,
            'pla_oph_land' => $pickupCountryCode,
            'pla_oph_syn' => $pickupStreet . ' ' . $pickupStreetNumber . ' ' . $pickupPostalCode . ' ' . $pickupLocality . ' ' . $pickupCountryCode,
            'pla_besstraat' => $destinationStreet,
            'pla_bestnummer' => $destinationStreetNumber,
            'pla_bestpostcode' => $destinationPostalCode,
            'pla_beststad' => $destinationLocality,
            'pla_best_land' => $destinationCountryCode,
            'pla_bes_syn' => $destinationStreet . ' ' . $destinationStreetNumber . ' ' . $destinationPostalCode . ' ' . $destinationLocality . ' ' . $destinationCountryCode,
            'pla_naam_reiziger' => $this->sanitize($_SESSION['bookingName']),
            'pla_telefoon' => $this->sanitize($_SESSION['phone']),
            'pla_email' => $this->sanitize($_SESSION['email']),
            'pla_lopers' => $this->sanitize($_SESSION['personAmount']),
            'pla_rollers' => $this->sanitize($_SESSION['wheelchairs']),
            'pla_rolstoel' => !empty($_SESSION['wheelchairs']) && ((int)$_SESSION['wheelchairs'] > 0) ? '1' : '0',
            'pla_tarief_bedrijf' => $selectedTariff,
            'pla_filter' => $filter,
            'pla_minuten' => $isRetour ? $this->sanitize($_SESSION['travelTimeRetour']) : $this->sanitize($_SESSION['travelTime']),
            'pla_km_sqr' => $isRetour ? $this->sanitize($_SESSION['travelDistanceRetour']) : $this->sanitize($_SESSION['travelDistance']),
            'pla_bedrag' => '' . ($isRetour ? $this->sanitize($_SESSION['tripCalculationRetour']->data[0]->pla_bedrag) : $this->sanitize($_SESSION['tripCalculation']->data[0]->pla_bedrag)) . '',
            'pla_rc' => '0',
            'pla_betaling' => '0',
            'pla_tekst' => $this->sanitize($_SESSION['extra']),
            'pla_oph_latitude' => !empty($pickupPlace["gps"])  ? round($pickupPlace["gps"]["latitude"],7) : '0.0',
            'pla_oph_longitude' => !empty($pickupPlace["gps"]) ? round($pickupPlace["gps"]["longitude"],7) : '0.0',
            'pla_best_latitude' => !empty($destinationPlace["gps"]) ? round($destinationPlace["gps"]["latitude"],7) : '0.0',
            'pla_best_longitude' => !empty($destinationPlace["gps"]) ? round($destinationPlace["gps"]["longitude"],7) : '0.0',
            'pla_soortvervoer' => pitanebooking_get_option('rei_vor_id'),
            'pla_soortwagen' => $selectedVehicle,
            'pla_iata' => 'XXX',
            'pla_bron' => $this->IDENTIFICATION,
            'pla_vluchttijd' => '1970-01-01T00:00:00',
            //'pla_pm_nummer' => rand(10000, 100000),
            'timestamp' => str_replace(' ', 'T', date('Y-m-d H:i:s')),
        );
    
        $response = $this->apiRequestPost($url, $data);     
        return $response;
    }

    private function createResponse($request)
    {
        if (is_wp_error($request))
        {
            pitanebooking_logToDatabase("RESPONSE: " . print_r($request, true), "API_RESPONSE_ERROR");
            return false; // error in request
        }

        if ($this->API_LOGGING === true)
        {
            pitanebooking_logToDatabase("RESPONSE: " . print_r($request, true), "API_RESPONSE");
        }

        $body = wp_remote_retrieve_body($request);

        if ($this->API_LOGGING === true)
        {
            pitanebooking_logToDatabase("RESPONSE BODY: " . $body, "API_RESPONSE");
        }

        $data = json_decode($body);
        $data = $this->sanitize($data);

        return $data;
    }

    function getPaymentType($paymentTypePost)
    {
        if ($paymentTypePost == 'Pay at driver')
        {
            // Pay at driver
            return "0";
        }

        if ($paymentTypePost == 'Pay online')
        {
            // online
            return "4";
        }
    }

    function sendBookingEmail($to, $subject, $body, $cc = '', $bcc = '')
    {
        if (!empty($to) && !empty($subject) && !empty($body))
        {
            if (!str_contains($to, '@')) 
            { 
                pitanebooking_logToDatabase("Could not send email: Invalid email address detected!");
                return;
            }

            $url = $this->getApiUrl('pitaneSendEmailMessage');
            $data = array(
                'to' => $to,
                'cc' => $cc,
                'bcc' => $bcc,
                'subject' => $subject,
                'body' => $body,
            );
            $response = $this->apiRequestPost($url, $data, true);
        }
        else
        {
            pitanebooking_logToDatabase("Could not send email: To, Subject or Body is empty!");
        }
    }

    function updateTripRetourId($mainTripId, $retourTripId)
    {
        $url = $this->getApiUrl('pitaneSendTripInsert');
        $data = array(
            'pla_id' => $mainTripId,
            'pla_id_retour' => $retourTripId,
            'pla_bron' => $this->IDENTIFICATION,
        );

        $response = $this->apiRequestPost($url, $data);
        return $response;
    }

    function unsetSessionVariables()
    {
        unset($_SESSION['tripId']);
        unset($_SESSION['bookingName']);
        unset($_SESSION['carType']);
        unset($_SESSION['destinationAddress']);
        unset($_SESSION['email']);
        unset($_SESSION['extra']);
        unset($_SESSION['personAmount']);
        unset($_SESSION['wheelchairs']);
        unset($_SESSION['phone']);
        unset($_SESSION['pickupAddress']);
        unset($_SESSION['pickupTime']);
        unset($_SESSION['returnDate']);
        unset($_SESSION['returnOption']);
        unset($_SESSION['returnTime']);
        unset($_SESSION['tripDate']);
        unset($_SESSION['step']);
        unset($_SESSION['paymentType']);
        unset($_SESSION['tripCalculationCalculated']);
        unset($_SESSION['tripCalculationCalculatedRetour']);
    }

    function service_booking_form_submit_handler()
    {
        try
        {
            if ($_SESSION['step'] == 0)
            {
                // booking start
                // unset all variables
                $this->unsetSessionVariables();
            }

            $_SESSION['tripId'] = null;
            $_SESSION['bookingName'] = $this->sanitize($_POST['booking-name']);
            $_SESSION['carType'] = $this->sanitize($_POST['car-type']);
            $_SESSION['destinationAddress'] = $this->sanitize($_POST['destination-address']);
            $_SESSION['email'] = $this->sanitize($_POST['email']);
            $_SESSION['extra'] = $this->sanitize($_POST['extra-information']);
            $_SESSION['personAmount'] = $this->sanitize($_POST['number-of-person']);
            $_SESSION['wheelchairs'] = '0';            
            $_SESSION['phone'] = $this->sanitize($_POST['phone-number']);
            $_SESSION['pickupAddress'] = $this->sanitize($_POST['pick-up-address']);
            $_SESSION['pickupTime'] = $this->sanitize($_POST['pick-up-time']) . ":00";
            $_SESSION['returnDate'] = $this->sanitize($_POST['return-date']);
            $_SESSION['returnOption'] = $this->sanitize($_POST['return-option']);
            $_SESSION['returnTime'] = $this->sanitize($_POST['return-time']). ":00";
            $_SESSION['tripDate'] = $this->sanitize($_POST['trip-date']);
            $_SESSION['step'] = $this->sanitize($_POST['step']);
            $_SESSION['paymentType'] = $this->getPaymentType($this->sanitize($_POST['paymentType']));

            // Check if the email address is an actual emailAddress
            if (!empty($_SESSION['email']))
            {
                $_SESSION['email'] = sanitize_email($_SESSION['email']);
                if (empty($_SESSION['email']))
                {
                    pitanebooking_logToDatabase('The given email is invalid, cannot proceed with booking');
                    return;
                }
            }

            // Check if the phonenumber is an actual phoneNumber
            if (!empty($_SESSION['phone']))
            {
                if (preg_match('/^[0-9A-Za-z\s\-]+$/', $_SESSION['phone']))
                {
                    // phone number seems to be OK
                }
                else
                {
                    pitanebooking_logToDatabase('The given phonenumber is invalid, cannot proceed with booking');
                    $_SESSION['phone'] = null;
                }
            }

            if ($_SESSION['step'] == 1)
            {
                // trip details
            }

            if ($_SESSION['step'] == 2)
            {
                // contact details
            }

            if ($_SESSION['step'] == 3)
            {
                // booking final

                $isRetour = $_SESSION['returnOption'] == 'yes';

                $response = $this->pitaneSendTripInsert();
                if (!empty($response->transaction) && $response->transaction[0]->transactionstatus == "201")
                {
                    $mainTripId = $response->planning[0]->pla_id;
                    $mainTripGuid = $response->planning[0]->pla_guid;

                    pitanebooking_logToDatabase($this->sanitize($_SESSION['bookingName']) . ' booked trip: ' . $response->planning[0]->pla_id);

                    if (!$isRetour)
                    {
                        // show response for single trip                        
                        $response = array(
                            'tripNumber' => wp_kses_post($response->planning[0]->pla_id),
                            'tripNumberGuid' => wp_kses_post($response->planning[0]->pla_guid),
                            'phone' => wp_kses_post($_SESSION['phone']),
                            'email' => wp_kses_post($_SESSION['email']),
                        );

                        // check if we need to add the gate12 booking url
                        if ($_SESSION['paymentType'] == "4" && !empty(pitanebooking_get_option('gate12_guid')))
                        {
                            $response['gate12BookingUrl'] = $this->sanitize('https://gate12.eu/PayThroughURL?id=' . pitanebooking_get_option('gate12_guid') . '&Trippayment=');
                        } 

                        if ($_SESSION['paymentType'] == "4")
                        {
                            // Pay online
                            $currentLocale = get_locale();
                            if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
                            {
                                $title = $this->sanitize("Uw rit werd gereserveerd onder nummer $mainTripId");
                            }
                            else
                            {
                                $title = $this->sanitize("Your trip has been reserved under number $mainTripId");   
                            }

                            $body = pitanebooking_emailHelper::generateBookingPayOnlineTemplate(
                                $this->sanitize($mainTripId),
                                $this->sanitize($_SESSION['tripDate']),
                                $this->sanitize($_SESSION['pickupTime']),
                                $this->sanitize($_SESSION['pickupAddress']),
                                $this->sanitize($_SESSION['destinationAddress']),
                                $this->sanitize($_SESSION['bookingName']),
                                $this->sanitize($_SESSION['email']),
                                $this->sanitize($_SESSION['phone']),
                                $this->sanitize($_SESSION['tripCalculationCalculated']),
                                $this->sanitize($response['gate12BookingUrl'] . $mainTripGuid),
                                $this->sanitize(pitanebooking_get_option('companyname'))
                            );
                        }
                        else
                        {
                            // Pay at driver
                            $currentLocale = get_locale();
                            if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
                            {
                                $title = $this->sanitize("Uw rit werd geboekt onder nummer $mainTripId");
                            }
                            else
                            {
                                $title = $this->sanitize("Your trip was booked under number $mainTripId $mainTripId");
                            }

                            $body = pitanebooking_emailHelper::generateBookingPayAtDriverTemplate(
                             $this->sanitize($mainTripId),
                                $this->sanitize($_SESSION['tripDate']),
                                $this->sanitize($_SESSION['pickupTime']),
                                $this->sanitize($_SESSION['pickupAddress']),
                                $this->sanitize($_SESSION['destinationAddress']),
                                $this->sanitize($_SESSION['bookingName']),
                                $this->sanitize($_SESSION['email']),
                                $this->sanitize($_SESSION['phone']),
                                $this->sanitize($_SESSION['tripCalculationCalculated']),
                                $this->sanitize(pitanebooking_get_option('companyname'))
                            );
                        }
                        $this->sendBookingEmail($_SESSION['email'], $title, $body);
                        die(wp_send_json_success($response));
                    }
                    else 
                    {
                        // Create the retour booking
                        $response = $this->pitaneSendTripInsert(true);

                        if (!empty($response->transaction) && $response->transaction[0]->transactionstatus == "201")
                        {
                            $retourTripId = $response->planning[0]->pla_id;
                            $retourTripGuid = $response->planning[0]->pla_guid;

                            // show response for single AND retour trip
                            pitanebooking_logToDatabase($this->sanitize($_SESSION['bookingName']) .' booked retour trip: ' . $retourTripId);
                            $response = array(
                                'tripNumber' => wp_kses_post($mainTripId),
                                'tripNumberGuid' => wp_kses_post($mainTripGuid),
                                'tripRetour' => wp_kses_post($retourTripId),
                                'tripNumberGuidRetour' => wp_kses_post($retourTripGuid),
                                'phone' => wp_kses_post($_SESSION['phone']),
                                'email' => wp_kses_post($_SESSION['email']),
                            );

                            $this->updateTripRetourId($mainTripId, $retourTripId);

                            // check if we need to add the gate12 booking url
                            if ($_SESSION['paymentType'] == "4" && !empty(pitanebooking_get_option('gate12_guid')))
                            {
                                $response['gate12BookingUrl'] = $this->sanitize('https://gate12.eu/PayThroughURL?id=' . pitanebooking_get_option('gate12_guid') . '&Trippayment=');
                            } 

                            if ($_SESSION['paymentType'] == "4")
                            {
                                // Pay online (retour)
                                $currentLocale = get_locale();
                                if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
                                {
                                    $title = $this->sanitize("Uw ritten werden gereserveerd onder de nummers $mainTripId & $retourTripId");
                                }
                                else
                                {
                                    $title = $this->sanitize("Your trips have been reserved under the numbers $mainTripId & $retourTripId");   
                                }

                                $body = pitanebooking_emailHelper::generateBookingRetourPayOnlineTemplate(
                                    $this->sanitize($mainTripId),
                                    $this->sanitize($_SESSION['tripDate']),
                                    $this->sanitize($_SESSION['pickupTime']),
                                    $this->sanitize($_SESSION['pickupAddress']),
                                    $this->sanitize($_SESSION['destinationAddress']),
                                    $this->sanitize($retourTripId),
                                    $this->sanitize($_SESSION['returnDate']),
                                    $this->sanitize($_SESSION['returnTime']),

                                    $this->sanitize($_SESSION['bookingName']),
                                    $this->sanitize($_SESSION['email']),
                                    $this->sanitize($_SESSION['phone']),
                                    $this->sanitize($_SESSION['tripCalculationCalculated']),
                                    $this->sanitize($_SESSION['tripCalculationCalculatedRetour']),
                                    $this->sanitize($response['gate12BookingUrl'] . $mainTripGuid . '&retour=' . $retourTripGuid),
                                    $this->sanitize(pitanebooking_get_option('companyname'))
                                );
                            }
                            else
                            {
                                $currentLocale = get_locale();
                                if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
                                {
                                    $title = $this->sanitize("Uw ritten werden geboekt onder de nummers $mainTripId & $retourTripId");
                                }
                                else
                                {
                                    $title = $this->sanitize("Your trips were booked under the numbers $mainTripId & $retourTripId");
                                }

                                // Pay at driver (retour)
                                $body = pitanebooking_emailHelper::generateBookingRetourPayAtDriverTemplate(
                                    $this->sanitize($mainTripId),
                                    $this->sanitize($_SESSION['tripDate']),
                                    $this->sanitize($_SESSION['pickupTime']),
                                    $this->sanitize($_SESSION['pickupAddress']),
                                    $this->sanitize($_SESSION['destinationAddress']),

                                    $this->sanitize($retourTripId),
                                    $this->sanitize($_SESSION['returnDate']),
                                    $this->sanitize($_SESSION['returnTime']),

                                    $this->sanitize($_SESSION['bookingName']),
                                    $this->sanitize($_SESSION['email']),
                                    $this->sanitize($_SESSION['phone']),
                                    $this->sanitize($_SESSION['tripCalculationCalculated']),
                                    $this->sanitize($_SESSION['tripCalculationCalculatedRetour']),
                                    $this->sanitize(pitanebooking_get_option('companyname'))
                                );
                            }
                            $this->sendBookingEmail($_SESSION['email'], $title, $body);
                            die(wp_send_json_success($response));
                        }
                        else
                        {
                            die(wp_send_json_error($response));
                        }
                    }
                }
                else
                {
                    die(wp_send_json_error($response));
                }
            }
            
            if (isset($_POST["calculateTripAmount"]) && $_POST["calculateTripAmount"] !== null)
            {
                $isRetour = $_SESSION['returnOption'] == 'yes';

                $pickupPlace = $this->sanitize($_POST['pickupPlace']);
                $destinationPlace = $this->sanitize($_POST['destinationPlace']);

                // Calculate the trip tariff
                $response = $this->CalculateTripAmount($pickupPlace, $destinationPlace, $_SESSION['carType'], $_SESSION['personAmount'], 0);

                $_SESSION['tripCalculation'] = $response;

                if ($isRetour)
                {
                    // Calculate the trip retour tariff
                    $response = $this->CalculateTripAmount($destinationPlace, $pickupPlace, $_SESSION['carType'], $_SESSION['personAmount'], 0, true);
                    $_SESSION['tripCalculationRetour'] = $response;
                }

                $_SESSION['pickupPlace'] = $pickupPlace;
                $_SESSION['destinationPlace'] = $destinationPlace;

                if ($_SESSION['tripCalculation'] !== null && $_SESSION['tripCalculation']->transaction !== null)
                {
                    $_SESSION['tripCalculation']->data[0]->pla_bedrag = round($_SESSION['tripCalculation']->data[0]->pla_bedrag, 2);
                    $pitaneTripTariff = $_SESSION['tripCalculation']->data[0]->pla_bedrag;
                    $_SESSION['tripCalculationCalculated'] = sprintf("%0.2f",$pitaneTripTariff);

                    $response = array(
                        'trip_amount' => sprintf("%0.2f",$pitaneTripTariff),
                    );

                    if ($isRetour)
                    {
                         $_SESSION['tripCalculationRetour']->data[0]->pla_bedrag = round($_SESSION['tripCalculationRetour']->data[0]->pla_bedrag, 2);
                         $pitaneTripTariffRetour = $_SESSION['tripCalculationRetour']->data[0]->pla_bedrag;
                         $response['trip_amount_retour'] = sprintf("%0.2f",$pitaneTripTariffRetour);
                         $_SESSION['tripCalculationCalculatedRetour'] = sprintf("%0.2f",$pitaneTripTariffRetour);
                    }
                    die(wp_send_json_success($response));
                }
                else
                {
                    die(wp_send_json_error($response));
                }
            }

            if ($_SESSION['step'] > 2)
            {
                // Check if the amount is valid
                if (!$this->hasValidTripAmount())
                {
                    die(wp_send_json_error('Invalid trip amount detected! Please restart booking'));
                }
            }

            wp_send_json_success();
        }
        catch (Exception $e)
        {
            wp_send_json_error($response);
        }
    }

    function hasValidTripAmount()
    {
        try
        {
            return $_SESSION['tripCalculation'] !== null && $_SESSION['tripCalculation']->data[0]->pla_bedrag > 0;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    function global_variable_css_add()
    {
?>
        <style>
            /* :root{
                --main-color: #58C6F2;
                --text-main-color: #FFFFFF;
                --button-color: #58C6F2;
                --background-color: #000000;
                --widget-text-color: #58C6F2;
                --sucess-text-color: #20db00;
                --error-text-color: #ff3a3a;
                --text-font: 'Montserrat', sans-serif;
            } */
            :root{
                --main-color: <?php $mainColor = (!empty(pitanebooking_get_option('pitane_main_color'))) ? pitanebooking_get_option('pitane_main_color') : '#FFFFFF'; echo esc_attr( $mainColor );  ?>;
                --text-main-color: <?php $textMainColor = (!empty(pitanebooking_get_option('pitane_text_main_color'))) ? pitanebooking_get_option('pitane_text_main_color') : '#FFFFFF'; echo esc_attr( $textMainColor ); ?>;
                --button-color: <?php $buttonColor =  (!empty(pitanebooking_get_option('pitane_button_color'))) ? pitanebooking_get_option('pitane_button_color') : '#58C6F2';  echo esc_attr( $buttonColor ); ?>;                    
                --background-color: <?php $backgroundColor = (!empty(pitanebooking_get_option('pitane_background_color'))) ? pitanebooking_get_option('pitane_background_color') : '#000000'; echo esc_attr( $backgroundColor ); ?>;
                --widget-text-color: <?php $widgetTextColor = (!empty(pitanebooking_get_option('pitane_widget_text_color'))) ? pitanebooking_get_option('pitane_widget_text_color') : '#58C6F2';  echo esc_attr( $widgetTextColor );  ?>;
                --sucess-text-color: <?php $successTextColor = (!empty(pitanebooking_get_option('pitane_success_text_color'))) ? pitanebooking_get_option('pitane_success_text_color') : '#20db00'; echo esc_attr( $successTextColor );  ?>;
                --error-text-color: <?php $errorTextColor =  (!empty(pitanebooking_get_option('pitane_error_text_color'))) ? pitanebooking_get_option('pitane_error_text_color') : '#ff3a3a'; echo esc_attr( $errorTextColor ); ?>;
                --text-font: 'Montserrat', sans-serif;
            }
        </style>
        <?php
    }

    /**
     * Enqueue scripts and styles.
     *
     */
    function pitane_plugin_scripts()
    {
        global $posts;
        // Check if we need to include the plugin settings
        if (!$this->hasShortcode($posts))
        {
            return;
        }

        // enqueue css
        wp_enqueue_style('pitane-shortcode-style', WP_PITANE_PLUGIN_URL . 'assets/css/pitane-plugin-shortcode.css', time());
        wp_enqueue_style('pitane-font-awesome', WP_PITANE_PLUGIN_URL . 'assets/libs/font-awesome/6.1.1/css/all.min.css', time());

        wp_enqueue_script('jquery-effects-core');
        wp_enqueue_script('jquery-effects-slide');

        if (is_front_page()) 
        {    
            wp_enqueue_style('bootstrap', get_template_directory_uri() . 'css/bootstrap.min.css');
            wp_enqueue_style('style', get_stylesheet_uri());
        }

        wp_enqueue_script('pitane-shortcode-js', WP_PITANE_PLUGIN_URL . 'assets/js/pitane-plugin-shortcode.js', array(
            'jquery'
        ) , '', true);

        wp_localize_script('pitane-shortcode-js', 'booking_object', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));

        $google_api_key = pitanebooking_get_option('google_api_key');
        wp_enqueue_script( 'maps-api', 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $google_api_key . '&callback=initMap', array(), '1', true);        
    }

    /**
     * Enqueue scripts and styles.
     *
     */
    function admin_pitane_plugin_scripts()
    {

        // enqueue js
        wp_register_script('pitane-admin-shortcode-js', WP_PITANE_PLUGIN_URL . 'assets/js/admin-pitane-plugin-shortcode.js', array(
            'jquery',
            'wp-color-picker'
        ) , false, true);
    }

    /**
     * Require files.
     *
     * Register shortcodes.
     *
     */
    function pitane_plugin_shortcode_include_files()
    {
        /* Shortcode file */
        require_once WP_PITANE_PLUGIN_DIR . 'includes/core/pitane-plugin.php';
    }
}

// Call Main class
new PitaneBooking();
