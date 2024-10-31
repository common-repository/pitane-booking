jQuery(document).ready(function() 
{ 
  jQuery("#pick-up-address").change(function() 
  {
    jQuery('.error.pick-up-text').hide();
  });

  jQuery("#destination-address").change(function() 
  {
    jQuery('.error.destination-text').hide();
  });

  jQuery('select#return-option').on('change', function() {
    var returnOption = jQuery('select#return-option').val();
    if(returnOption == 'no') 
    {
      jQuery('.trip-total-retour').hide()
      jQuery('.payOnline').show()
      jQuery('.return-class').hide()
    }
    else 
    {
      jQuery('.trip-total-retour').show()
      jQuery('.return-class').show()
    }
    processFormAjax(1, 1);
  });

 jQuery('select#number-of-person').on('change', function() {
    var returnOption = jQuery('select#number-of-person').val();
    if (returnOption > '4') 
    {
      var carType = jQuery('select#car-type');
      if (carType.val() == '1')
      {
        carType.val('2');
      }
      jQuery('.paxT').hide()
    }
    else
    {
      jQuery('.paxT').show()
    }
  });

  var returnOption = jQuery('select#return-option').val();
  if(returnOption == 'no') {
    jQuery('.return-class').hide()
  } else {
    jQuery('.return-class').show()
  }
  jQuery('.pitane-icon-right').click(function() {
    jQuery(this).prev().val('');
  });
  jQuery("#phone-number").change(function() {
    jQuery('.lbl_phone').css('display', 'none');
    var phone = jQuery('#phone-number').val();
    var pattern1 = /^\+?\d{10,13}$/;
    if(phone == '') {
      jQuery('.lbl_phone').show();
      jQuery('.lbl_pattern_phone').css('display', 'none');
    } else if(phone.match(pattern1)) {
      jQuery('.lbl_pattern_phone').css('display', 'none');
    } else {
      jQuery('.lbl_pattern_phone').show();
      jQuery('.lbl_phone').css('display', 'none');
    }
  });
  jQuery("#email").change(function() {
    jQuery('.lbl_email').css('display', 'none');
    var email = jQuery('#email').val();
    var pattern1 = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if(email == '') {
      jQuery('.lbl_email').show();
      jQuery('.lbl_pattern_email').css('display', 'none');
    } else if(email.match(pattern1)) {
      jQuery('.lbl_pattern_email').css('display', 'none');
    } else {
      jQuery('.lbl_pattern_email').show();
      jQuery('.lbl_email').css('display', 'none');
    }
  });
  
  jQuery('.pitane-booking-form-btnNext').on('click', function() 
  {
    var i = jQuery(this).attr('data');
    if (i == 1) 
    {
      var hasValidPickupAddress = false;
      var hasValidDestinationAddress = false;
    
      if(jQuery('input#pick-up-address').val()) 
      {
        hasValidPickupAddress = true;
        jQuery('.error.pick-up-text').hide();
      }
      else 
      {
        hasValidPickupAddress = false;
        jQuery('.error.pick-up-text').show();
      }

      if(jQuery('input#destination-address').val()) 
      {
        hasValidDestinationAddress = true;
        jQuery('.error.destination-text').hide();
      }
      else 
      {
        hasValidDestinationAddress = false;
        jQuery('.error.destination-text').show();
      }

      if (hasValidPickupAddress && hasValidDestinationAddress) 
      {
          next(i);
      }
    } 
    else if(i == 2) 
    {
      next(i);
    }
    else if(i == 3) 
    {
      if (jQuery('#email').val() == '' && jQuery('#phone-number').val() == '') 
      {
        jQuery('.lbl_phone').show();
        jQuery('.lbl_email').show();
      } else {
        var email = jQuery('#email').val();
        var pattern1 = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!email.match(pattern1)) {
          jQuery('.lbl_email_pattern').show();
        }
        else
        {
           next(i);
        }
      }
    } 
    else if (i == 4) 
    {
      r = i - 4
      next(r);

      jQuery('.trip-total-retour').hide()
      jQuery('.payOnline').show()
      jQuery('.return-class').hide()

      jQuery('form#pitane-form')[0].reset();
    }

    async function next(i) 
    {    
      if (!await processStep(i))
      {
        console.log("An error occurred cannot process to next step!");
        return false;
      }

      jQuery('#pitane-form > div').css('display', 'none');
      jQuery('#pitane-form > div').eq(i).show("slide", 
      {
        direction: "right"
      }, 400);

      var $target = jQuery("#pitane-form > div");
      
      jQuery('#pitane-form > div').animate(
      {
        scrollTop: $target.offset().top
      }, 400);
      return true;
    }
  })


  jQuery('.prev').on('click', function() {
    var i = jQuery(this).attr('data');
    previous(i);
  });

  function previous(i) 
  {
    r = i - 1;
    i = i - 2;
    jQuery('#pitane-form > div').css('display', 'none');
    jQuery('#pitane-form > div').eq(i).show("slide", {
      direction: "left"
    }, 400);

    var $target = jQuery("#pitane-form > div");
    jQuery('#pitane-form > div').animate({
      scrollTop: $target.offset().top
    }, 400);
  }

  async function processStep(index)
  {
    try
    {
      var hasValidResponse = await processFormAjax(index, index == 1);
      if (index == 3)
      {
          return hasValidResponse;
      }
      else
      {
        return hasValidResponse;
      }
    }
    catch (e) 
    {
      return false;
    }
    finally
    {
      jQuery('#loading_spinner').hide();   
    }
  }

  function extractFromAddress(components, type, longname = true) 
  {    
    if (longname)
    {
        return components.filter((component) => component.types.indexOf(type) === 0).map((item) => item.long_name).pop() || null;
    }
    else
    {
      return components.filter((component) => component.types.indexOf(type) === 0).map((item) => item.short_name).pop() || null;  
    }
  }

  function getPickupPlace()
  {
    const place = pickupAutocomplete.getPlace();
    const address_components = place["address_components"] || [];
    const geometry = place.geometry || [];

    var pickupPlace = 
    {
       "streetNumber": extractFromAddress(address_components, "street_number"),
       "street": extractFromAddress(address_components, "route"),
       "locality": extractFromAddress(address_components, "locality"),
       "area": extractFromAddress(address_components, "administrative_area_level_1"),
       "postalCode": extractFromAddress(address_components, "postal_code"),
       "country": extractFromAddress(address_components, "country"),
       "countryCode": extractFromAddress(address_components, "country", false),
    }

    if (geometry !== null)
    {
         pickupPlace.gps = {};
         pickupPlace.gps.latitude = geometry.location.lat();
         pickupPlace.gps.longitude = geometry.location.lng();
    }
    return pickupPlace;
  }

  function getDestinationPlace()
  {
    const place = destinationAutocomplete.getPlace();
    const address_components = place["address_components"] || [];
    const geometry = place.geometry || [];

    var destinationPlace = 
    {
       "streetNumber": extractFromAddress(address_components, "street_number"),
       "street": extractFromAddress(address_components, "route"),
       "locality": extractFromAddress(address_components, "locality"),
       "area": extractFromAddress(address_components, "administrative_area_level_1"),
       "postalCode": extractFromAddress(address_components, "postal_code"),
       "country": extractFromAddress(address_components, "country"),
       "countryCode": extractFromAddress(address_components, "country", false),
     }

    if (geometry !== null)
    {
         destinationPlace.gps = {};
         destinationPlace.gps.latitude = geometry.location.lat();
         destinationPlace.gps.longitude = geometry.location.lng();
    }
    return destinationPlace;
  }

  function getDateFormatted(dateAsString)
  {
    if (dateAsString == null)
    {
      return;
    }

    var date = new Date(dateAsString);
    var year = date.getFullYear();
    var month = date.getMonth()+1;
    var days = date.getDate();

    if (days < 10) {
      days = '0' + days;
    }

    if (month < 10) {
      month = '0' + month;
    }

    return (days + '-' + month + '-' + year);
  }

  async function processFormAjax(currentStep, calculateTripAmount = false)
  {
      jQuery('.tripBooked').show();
      jQuery('.tripBookedRetour').hide();      
      jQuery('.tripReserved').hide();
      jQuery('.tripReservedRetour').hide();      
      jQuery('.tripReserved_instructions').hide();                  
      jQuery('#gate12Booking').hide();
      jQuery('#next4').show();

      document.getElementById("next2").style.display = "none";

      var formData = jQuery("#pitane-form").serialize();

       formData += "&" + jQuery.param({
            step: currentStep
          });

       var pickupPlace = getPickupPlace();
       var destinationPlace = getDestinationPlace();

       if (calculateTripAmount)
       {
         formData += "&" + jQuery.param({
            calculateTripAmount: "true",
            pickupPlace: pickupPlace,
            destinationPlace: destinationPlace,
          });
       }

        formData += "&" + jQuery.param({
          action: "service_booking_form_submit"
        });

      return new Promise((resolve, reject) => 
      {
        jQuery('#loading_spinner').show();

        var isRetour = jQuery('select#return-option').val() == "yes";

        jQuery.ajax({
              type: "post",
              dataType: "json",
              url: booking_object.ajax_url,
              data: formData,
              success: function(data) 
              {
                if (data.success)
                {
                  if (currentStep == 2)
                  {
                      // do nothing
                  }

                  if (currentStep == 1)
                  {
                      // Update address values
                      jQuery('.result.pick-up-text').text(jQuery('#pick-up-address').val());
                      jQuery('.result.destination-text').text(jQuery('#destination-address').val());
                  }

                  if (currentStep == 3)
                  {
                      // Update the summary values
                      jQuery('#pick-input-date').text(getDateFormatted(jQuery('#trip-date').val()));
                      jQuery('#pick-input-time').text(jQuery('#pick-up-time').val());
                      jQuery('#pick-up-input-address').text(jQuery('#pick-up-address').val());
                      jQuery('#destination-input-address').text(jQuery('#destination-address').val());
                      jQuery('#phone-input-number').text(jQuery('#phone-number').val());

                      if (typeof data.data.gate12BookingUrl !== 'undefined')
                      {
                        jQuery('.tripBooked').hide();
                        jQuery('.tripBookedRetour').hide();
                        jQuery('#next4').hide();
                        jQuery("#gate12Booking").click(function()
                        { 
                          var bookingUrl = (typeof data.data.tripRetour !== 'undefined') ? (data.data.gate12BookingUrl + data.data.tripNumberGuid + '&retour=' + data.data.tripNumberGuidRetour) : (data.data.gate12BookingUrl + data.data.tripNumberGuid);
                          window.open(bookingUrl, "_blank");
                          setTimeout(jQuery('#next4').click(), 5000);
                        });
                        jQuery('#gate12Booking').text('Betalen');
                        jQuery('#gate12Booking').show();

                        jQuery('.tripReserved').hide();
                        jQuery('.tripReservedRetour').hide();

                        if (isRetour)
                        {
                          jQuery('.tripReserved').hide();
                          jQuery('.tripReservedRetour').show();
                        }
                        else
                        {
                          jQuery('.tripReservedRetour').hide();
                          jQuery('.tripReserved').show();
                        }

                        jQuery('.tripReserved_instructions').show();
                      }
                      else
                      {
                        if (isRetour)
                        {
                          jQuery('.tripBooked').hide();
                          jQuery('.tripBookedRetour').show();
                        }
                        else
                        {
                          jQuery('.tripBookedRetour').hide();
                          jQuery('.tripBooked').show();
                        }

                        jQuery('#gate12Booking').hide();
                        jQuery('#next4').show();
                      }
                      jQuery('#phone-input-email').text(jQuery('#email').val());
                      jQuery('.result.pick-up-text').text(jQuery('#pick-up-address').val());
                      jQuery('.result.destination-text').text(jQuery('#destination-address').val());
                  }

                 if (calculateTripAmount)
                 {
                    ToggleVisibility("next2");
                    jQuery('.trip-amount').text(data.data.trip_amount);
                    jQuery('.trip-amount-retour').text(data.data.trip_amount_retour);                      
                 }
                 else if (currentStep == 3)
                 {
                    if (typeof data.data.tripRetour !== 'undefined')
                    {
                      jQuery(".pla-id").text(data.data.tripNumber + '-' + data.data.tripRetour);
                    }
                    else
                    {
                      jQuery(".pla-id").text(data.data.tripNumber);
                    }

                    jQuery("#phone-input-number").text(data.data.phone);
                    jQuery("#phone-input-email").text(data.data.email);                      
                 }
                 jQuery('#loading_spinner').hide();
                 return resolve(true);
                }
                else
                {
                   console.log('An error occurred during the AJAX request');
                   jQuery('#loading_spinner').hide();
                   reject(false);
                }
              },
             error: (response) => 
             {
               jQuery('#loading_spinner').hide();
               reject(response);
            }
          });
      });
  }

  function ToggleVisibility(elementId)
  {
      var x = document.getElementById(elementId);
      if (x.style.display === "none") 
      {
        x.style.display = "block";
      }
      else 
      {
        x.style.display = "none";
      }
  }
});