<?php

class pitanebooking_emailHelper
{
	static function pitaneBooking_sanitize($input) {
        if( is_string($input) ){
            $input = sanitize_text_field($input);
        }
        elseif ( is_array($input) ){
            foreach ( $input as $key => &$value ) {
                if ( is_array( $value ) ) {
                    $value = pitanebooking_emailHelper::pitaneBooking_sanitize($value);
                }
                else {
                    $value = sanitize_text_field( $value );
                }
            }
        }
        return $input;
    }

	public static function generateBookingPayOnlineTemplate($tripId, $tripDate, $tripTime, $pickup, $destination, $bookingName, $bookingEmail, $bookingPhone, $bookingCosts, $paymentUrl, $companyName)
	{
		//Sanitize the input data
		$tripId = pitanebooking_emailHelper::pitaneBooking_sanitize($tripId);
		$tripDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripDate);
		$tripTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripTime);
		$pickup = pitanebooking_emailHelper::pitaneBooking_sanitize($pickup);
		$destination = pitanebooking_emailHelper::pitaneBooking_sanitize($destination);
		$bookingName = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingName);
		$bookingEmail = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingEmail);
		$bookingPhone = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingPhone);
		$bookingCosts = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCosts);
		$companyName = pitanebooking_emailHelper::pitaneBooking_sanitize($companyName);

		$tripDate = date("d-m-Y", strtotime($tripDate));
		$tripTime = date("H:i", strtotime($tripTime));

		if ($companyName == null)
		{
			$companyName = "";
		}

		$currentLocale = get_locale();
		if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
		{
			return "<p>Geachte heer/ mevrouw, <br /> <br />Bedankt voor uw reservering, wij hebben deze in goede orde ontvangen. <br /> <br />Uw rit werd gereserveerd onder ritnummer: <strong>$tripId</strong>.<br /><br />Datum en tijdstip:<br /><strong>$tripDate</strong> om <strong>$tripTime</strong><br /><br />Ophaaladres:<br /><strong>$pickup<br /></strong><br />Bestemmingsadres:<br /><strong>$destination<br /></strong><strong><br /></strong>De rit werd gereserveerd op naam van <strong>$bookingName</strong>. Wanneer er vragen zijn omtrent uw reservering zullen wij deze communiceren per e-mail aan <strong>$bookingEmail</strong> of nemen wij telefonisch contact op via nummer <strong>$bookingPhone</strong>.<br /><br />De kosten voor de heenrit bedragen &euro; <strong>$bookingCosts</strong>. Gelieve de rit online te betalen om uw reservering om te zetten tot een definitieve boeking.<br /><br />U kunt uw rit betalen via de volgende <a href='$paymentUrl'>link</a><br /><br />Wanneer er verdere vragen zijn gelieve contact op te nemen.<br /><br />Met vriendelijke groet,<br /><br /><strong>$companyName</strong></p>";
		}
		else
		{
			return "<p>Dear Sir/Madam, <br /> <br />Thank you for your reservation, we have received it in good order. <br /> <br />Your trip has been reserved under trip number: <strong>$tripId</strong>.<br /><br />Date and time:<br /><strong>$tripDate</strong> at <strong>$tripTime</strong><br /><br />Pickup address:<br /><strong>$pickup<br /></strong><br />Destination address:<br /><strong> $destination<br /></strong><strong><br /></strong>The trip was reserved in the name of <strong>$bookingName</strong>. If there are any questions regarding your reservation, we will communicate them via e-mail to <strong>$bookingEmail</strong>. We can also contact you via telephone on the number <strong>$bookingPhone</strong>.<br /><br />The costs for the outward journey are &euro; <strong>$bookingCosts</strong>. Please pay for the trip online to convert your reservation into a final booking.<br /><br />You can pay for your trip via the following <a href='$paymentUrl'>link</a><br /><br />If you have any further questions, please get in touch.<br /><br />Sincerely,<br /><br /><strong>$companyName</strong></p>";
		}
	}

	public static function generateBookingRetourPayOnlineTemplate($tripId, $tripDate, $tripTime, $pickup, $destination, $tripIdRetour, $tripRetourDate, $tripRetourTime, $bookingName, $bookingEmail, $bookingPhone, $bookingCosts, $bookingCostsRetour, $paymentUrl, $companyName)
	{
		//Sanitize the input data
		$tripId = pitanebooking_emailHelper::pitaneBooking_sanitize($tripId);
		$tripDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripDate);
		$tripTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripTime);
		$pickup = pitanebooking_emailHelper::pitaneBooking_sanitize($pickup);
		$destination = pitanebooking_emailHelper::pitaneBooking_sanitize($destination);
		$tripIdRetour = pitanebooking_emailHelper::pitaneBooking_sanitize($tripIdRetour);
		$tripRetourDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripRetourDate);
		$tripRetourTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripRetourTime);
		$bookingName = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingName);
		$bookingEmail = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingEmail);
		$bookingPhone = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingPhone);
		$bookingCosts = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCosts);
		$bookingCostsRetour = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCostsRetour);
		$companyName = pitanebooking_emailHelper::pitaneBooking_sanitize($companyName);

		$tripDate = date("d-m-Y", strtotime($tripDate));
		$tripTime = date("H:i", strtotime($tripTime));

		$tripRetourDate = date("d-m-Y", strtotime($tripRetourDate));
		$tripRetourTime = date("H:i", strtotime($tripRetourTime));

		if ($companyName == null)
		{
			$companyName = "";
		}

		$currentLocale = get_locale();
		if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
		{
			return "<p>Geachte heer/ mevrouw, <br /> <br />Bedankt voor uw reservering, wij hebben deze in goede orde ontvangen. <br /> <br />Uw rit werd gereserveerd onder ritnummer: <strong>$tripId</strong>.<br /><br />Datum en tijdstip:<br /><strong>$tripDate</strong> om <strong>$tripTime</strong><br /><br />Ophaaladres:<br /><strong>$pickup<br /></strong><br />Bestemmingsadres:<br /><strong>$destination<br /><br /></strong>U koos ervoor direct uw retour te reserveren.<br /><br />Uw retour rit werd gereserveerd onder ritnummer: <strong>$tripIdRetour</strong>.<br /><br />Datum en tijdstip retour:<br /><strong>$tripRetourDate</strong> om <strong>$tripRetourTime</strong><br /><br />Ophaaladres:<br /><strong>$destination<br /></strong><br />Bestemmingsadres:<br /><strong>$pickup</strong><br /><strong><br /></strong>De rit werd gereserveerd op naam van <strong>$bookingName</strong>. Wanneer er vragen zijn omtrent uw reservering zullen wij deze communiceren per e-mail aan <strong>$bookingEmail</strong> of nemen wij telefonisch contact op via nummer <strong>$bookingPhone</strong>.<br /><br />De kosten voor de heenrit bedragen &euro; <strong>$bookingCosts</strong> voor de retour is dit &euro; <strong>$bookingCostsRetour</strong>. Gelieve de ritten online te betalen om uw reservering om te zetten tot een definitieve boeking.<br /><br />U kunt uw ritten betalen via de volgende <a href='$paymentUrl'>link</a><br /><br />Wanneer er verdere vragen zijn gelieve contact op te nemen.<br /><br />Met vriendelijke groet,<br /><br /><strong>$companyName</strong></p>";
		}
		else
		{
			return "<p>Dear Sir/Madam, <br /> <br />Thank you for your reservation, we have received it in good order. <br /> <br />Your trip has been reserved under trip number: <strong>$tripId</strong>.<br /><br />Date and time:<br /><strong>$tripDate</strong> at <strong>$tripTime</strong><br /><br />Pickup address:<br /><strong>$pickup<br /></strong><br />Destination address:<br /><strong> $destination<br /><br /></strong>You chose to book your return immediately.<br /><br />Your return trip was reserved under the trip number: <strong>$tripIdRetour</strong>.<br /><br />Return date and time:<br /><strong>$tripRetourDate</strong> at <strong>$tripRetourTime</strong><br /><br />Pick up address:<br /><strong>$destination<br /></strong><br />Destination address:<br /><strong>$pickup</strong><br /><strong><br /></strong>The trip has been reserved in the name of <strong>$bookingName</strong>. If there are any questions regarding your reservation, we will communicate them by e-mail to <strong>$bookingEmail</strong>. We can also contact you via telephone on the number <strong>$bookingPhone</strong>.<br /><br />The costs for the outward journey are &euro; <strong>$bookingCosts</strong> for the return this is &euro; <strong>$bookingCostsRetour</strong>. Please pay for the trips online to convert your reservation into a final booking.<br /><br />You can pay for your trips via the following <a href='$paymentUrl'>link</a><br /><br />If you have any further questions, please get in touch.<br /><br />Sincerely,<br /><br /><strong>$companyName</strong></p>";
		}
	}

	public static function generateBookingPayAtDriverTemplate($tripId, $tripDate, $tripTime, $pickup, $destination, $bookingName, $bookingEmail, $bookingPhone, $bookingCosts, $companyName)
	{
		//Sanitize the input data
		$tripId = pitanebooking_emailHelper::pitaneBooking_sanitize($tripId);
		$tripDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripDate);
		$tripTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripTime);
		$pickup = pitanebooking_emailHelper::pitaneBooking_sanitize($pickup);
		$destination = pitanebooking_emailHelper::pitaneBooking_sanitize($destination);
		$bookingName = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingName);
		$bookingEmail = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingEmail);
		$bookingPhone = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingPhone);
		$bookingCosts = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCosts);
		$companyName = pitanebooking_emailHelper::pitaneBooking_sanitize($companyName);

		$tripDate = date("d-m-Y", strtotime($tripDate));
		$tripTime = date("H:i", strtotime($tripTime));

		if ($companyName == null)
		{
			$companyName = "";
		}

		$currentLocale = get_locale();
		if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
		{
			return "<p>Geachte heer/ mevrouw, <br /> <br />Bedankt voor uw boeking, wij hebben deze in goede orde ontvangen. <br /> <br />Uw rit werd geboekt onder ritnummer: <strong>$tripId</strong>.<br /><br />Datum en tijdstip:<br /><strong>$tripDate</strong> om <strong>$tripTime</strong><br /><br />Ophaaladres:<br /><strong>$pickup<br /></strong><br />Bestemmingsadres:<br /><strong>$destination<br /><br /></strong>De rit werd geboekt op naam van <strong>$bookingName</strong>. Wanneer er vragen zijn omtrent uw boeking zullen wij deze communiceren per e-mail aan <strong>$bookingEmail</strong> of nemen wij telefonisch contact op via nummer <strong>$bookingPhone</strong>.<br /><br />De kosten voor deze boeking bedragen &euro; <strong>$bookingCosts</strong> en dienen te worden betaald bij de chauffeur.<br /><br />Wanneer er verdere vragen zijn gelieve contact op te nemen.<br /><br />Met vriendelijke groet,<br /><br /><strong>$companyName</strong></p>";
		}
		else
		{
			return "<p>Dear Sir/Madam, <br /> <br />Thank you for your booking, we have received it in good order. <br /> <br />Your trip was booked under trip number: <strong>$tripId</strong>.<br /><br />Date and time:<br /><strong>$tripDate</strong> at <strong>$tripTime</strong><br /><br />Pickup address:<br /><strong>$pickup<br /></strong><br />Destination address:<br /><strong> $destination<br /><br /></strong>The trip was booked in the name of <strong>$bookingName</strong>. If there are any questions regarding your booking, we will communicate them by e-mail to <strong>$bookingEmail</strong>. We can also contact you via telephone at the number <strong>$bookingPhone</strong>.<br /><br />The cost for this booking is &euro; <strong>$bookingCosts</strong> and are to be paid to the driver.<br /><br />If there are any further questions please get in touch.<br /><br />Sincerely,<br /><br /><strong>$companyName</strong></p>";
		}
	}

	public static function generateBookingRetourPayAtDriverTemplate($tripId, $tripDate, $tripTime, $pickup, $destination, $tripIdRetour, $tripRetourDate, $tripRetourTime, $bookingName, $bookingEmail, $bookingPhone, $bookingCosts, $bookingCostsRetour, $companyName)
	{
		//Sanitize the input data
		$tripId = pitanebooking_emailHelper::pitaneBooking_sanitize($tripId);
		$tripDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripDate);
		$tripTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripTime);
		$pickup = pitanebooking_emailHelper::pitaneBooking_sanitize($pickup);
		$destination = pitanebooking_emailHelper::pitaneBooking_sanitize($destination);
		$tripIdRetour = pitanebooking_emailHelper::pitaneBooking_sanitize($tripIdRetour);
		$tripRetourDate = pitanebooking_emailHelper::pitaneBooking_sanitize($tripRetourDate);
		$tripRetourTime = pitanebooking_emailHelper::pitaneBooking_sanitize($tripRetourTime);
		$bookingName = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingName);
		$bookingEmail = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingEmail);
		$bookingPhone = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingPhone);
		$bookingCosts = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCosts);
		$bookingCostsRetour = pitanebooking_emailHelper::pitaneBooking_sanitize($bookingCostsRetour);
		$companyName = pitanebooking_emailHelper::pitaneBooking_sanitize($companyName);

		$tripDate = date("d-m-Y", strtotime($tripDate));
		$tripTime = date("H:i", strtotime($tripTime));

		$tripRetourDate = date("d-m-Y", strtotime($tripRetourDate));
		$tripRetourTime = date("H:i", strtotime($tripRetourTime));

		if ($companyName == null)
		{
			$companyName = "";
		}

		$currentLocale = get_locale();
		if ($currentLocale == 'nl_NL' || $currentLocale == 'nl_BE')
		{
			return "<p>Geachte heer/ mevrouw, <br /> <br />Bedankt voor uw boeking, wij hebben deze in goede orde ontvangen. <br /> <br />Uw rit werd geboekt onder ritnummer: <strong>$tripId</strong>.<br /><br />Datum en tijdstip:<br /><strong>$tripDate</strong> om <strong>$tripTime</strong><br /><br />Ophaaladres:<br /><strong>$pickup<br /></strong><br />Bestemmingsadres:<br /><strong>$destination<br /><br /></strong>U koos ervoor direct uw retour te boeken.<br /><br />Uw retour rit werd geboekt onder ritnummer: <strong>$tripIdRetour</strong>.<br /><br />Datum en tijdstip retour:<br /><strong>$tripRetourDate</strong> om <strong>$tripRetourTime</strong><br /><br />Ophaaladres:<br /><strong>$destination<br /></strong><br />Bestemmingsadres:<br /><strong>$pickup</strong><br /><strong><br /></strong>De rit werd geboekt op naam van <strong>$bookingName</strong>. Wanneer er vragen zijn omtrent uw boeking zullen wij deze communiceren per e-mail aan <strong>$bookingEmail</strong> of nemen wij telefonisch contact op via nummer <strong>$bookingPhone</strong>.<br /><br />De kosten voor de heenrit bedragen &euro; <strong>$bookingCosts</strong> voor de retour is dit &euro; <strong>$bookingCostsRetour</strong>. De kosten dienen te worden betaald bij de chauffeur.<br /><br />Wanneer er verdere vragen zijn gelieve contact op te nemen.<br /><br />Met vriendelijke groet,<br /><br /><strong>$companyName</strong></p>";
		}
		else
		{
			return "<p>Dear Sir/Madam, <br /> <br />Thank you for your booking, we have received it in good order. <br /> <br />Your trip was booked under trip number: <strong>$tripId</strong>.<br /><br />Date and time:<br /><strong>$tripDate</strong> at <strong>$tripTime</strong><br /><br />Pickup address:<br /><strong>$pickup<br /></strong><br />Destination address:<br /><strong> $destination<br /><br /></strong>You chose to book your return directly.<br /><br />Your return trip was booked under the trip number: <strong>$tripIdRetour</strong>.<br /><br />Return date and time:<br /><strong>$tripRetourDate</strong> at <strong>$tripRetourTime</strong><br /><br />Pick up address:<br /><strong>$destination<br /></strong><br />Destination address:<br /><strong>$pickup</strong><br /><strong><br /></strong>The trip has been booked in the name of <strong>$bookingName</strong>. If there are any questions regarding your booking, we will communicate them by e-mail to <strong>$bookingEmail</strong>. We can also contact you via telephone at the number <strong>$bookingPhone</strong>.<br /><br />The costs for the outward journey are &euro; <strong>$bookingCosts</strong> for the return this is &euro; <strong>$bookingCostsRetour</strong>. The charges must be paid to the driver.<br /><br />If there are any further questions, please contact us.<br /><br />Sincerely,<br /><br /><strong>$companyName</strong></p>";	
		}
	}
}

?>
