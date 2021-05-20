/*global Drupal: false, jQuery: false */
/*jslint devel: true, browser: true, maxerr: 50, indent: 2 */

(function ($) {
  Drupal.behaviors.empowersbc = {
    attach: function (context, settings) {

      var all_status_arr = [];
      var present_status_arr = [];
      var difference = [];
      var anonymousUser = $("body").hasClass("not-logged-in");

      //@temporary fix : Back Button on Contact us form  
      jQuery(".webform-previous").val("Back");

      $("#timedoutleads,#unrealizedlead,#canceled,#completed").hide();
      $("#loanclosed,#loandeclined,#pre-qualificationdenied").hide();

      jQuery("#portal-status input:checkbox").each(function () {
        if ($(this).is(':checked')) {
          all_status_arr.push($(this).val());
        }
      });

      jQuery("#container > div").each(function () {
        var present_status_id = $(this).attr("id");
        if ($.inArray(present_status_id, all_status_arr) >= 0) {
          present_status_arr.push(present_status_id);
        }
      });

      jQuery.grep(all_status_arr, function (el) {
        if (jQuery.inArray(el, present_status_arr) == -1) difference.push(el);
      });

      var newHTML = [];
      $.each(difference, function (index, value) {
        var status_name = $("#portal-status input[value='" + value + "']").attr('name');
        newHTML.push('<div id="' + value + '">No record available for ' + status_name + '</div>');
      });

      $("#lead_status").show().html(newHTML.join(""));

      jQuery("#portal-status :checkbox").click(function (event) {
        var status_id = $(this).val();
        var status_name = $(this).attr('name');

        if ($(this).is(":checked")) {
          if (!jQuery("#" + status_id).length) {
            newHTML.push('<div id="' + status_id + '">No record available for ' + status_name + '</div>');
            $("#lead_status").show().html(newHTML.join(""));
          }
          $("#" + status_id).show();
        } else {
          $("#" + status_id).hide();
        }
      });

      //jQuery("#accept-lead").on('click', function (e) {
      jQuery("#lead .row").on('click', '#accept-lead', function (e) {

        //Check If preliminary Estimate is Fillout before accepting New LEAD.
        var is_pe_present = $(this).parent().attr("data");
       /* if (is_pe_present == 0) {
          alert("You must complete the Preliminary Estimate before accepting this lead");
          return;
        } */
        $accept_lead = confirm("Are you sure to accept this lead");

        //If accept lead
        if ($accept_lead) {
          jQuery(this).parent().find('#p-status').text('Confirmed Lead');
          $homeOwnerId = $(this).attr('cid1');
          $contractorId = $(this).attr('orgid');
          $relId = $(this).attr('relid');

          //$(this).load("accept/lead/" + $relId + "/" + $contractorId);
          //window.location.reload();
          $.ajax({
            url: "accept/lead/" + $relId + "/" + $contractorId,
            success: function (data) {
              if (data.status == 'success') {
                window.location.reload();
              }
            }
          });

        }
      });

      jQuery("#lead .row").on('click', '#donot-accept-lead', function (e) {
        $accept_lead = confirm("Are you sure to not to accept this lead");

        //If Don't accept lead
        if ($accept_lead) {
          jQuery(this).parent().find('#p-status').text('Confirmed Lead');
          $homeOwnerId = $(this).attr('cid1');
          $contractorId = $(this).attr('orgid');
          //$(this).load("donot/accept/lead/" + $homeOwnerId + "/" + $contractorId);
          //window.location.reload();
          $.ajax({
            url: "donot/accept/lead/" + $homeOwnerId + "/" + $contractorId,
            success: function (data) {
              if (data.status == 'success') {
                window.location.reload();
              }
            }
          });
        }
      });


      $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        return results[1] || 0;
      }

      //Loan Detail form - Submit pre installation review form
      jQuery(".pre-loan-form-submit").unbind().bind('click', function (e) {
        e.preventDefault();
        $contact_id = $.urlParam('cid1');

        $.ajax({
          url: "submit/prereview/" + $contact_id,
          success: function (data) {
            if (data.status == 'success') {
              //Change the pre-qualification status to 'pending' on click of this button
              jQuery("#edit-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg11-custom-290").val('pending');
              jQuery('.webform-submit').click();
            }
          }
        });
      });

      //Loan Detail form - Submit pre installation review form
      jQuery(".post-loan-form-submit").unbind().bind('click', function (e) {
        e.preventDefault();
        $contact_id = $.urlParam('cid1');
        $.ajax({
          url: "submit/postreview/" + $contact_id,
          success: function (data) {
            if (data.status == 'success') {
              //Change the pre-qualification status to 'pending' on click of this button
              jQuery("#edit-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg11-custom-293").val('pending');
              jQuery('.webform-submit').click();
            }
          }
        });
      });

      //submit Loan Detail form by onChange event
      //Pre-qualification status field
      jQuery("#edit-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg11-custom-290").on('change', function () {
        jQuery('.webform-submit').click();

      });

      // Loan Application status field
      jQuery("#edit-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg11-custom-293").on('change', function () {
        jQuery('.webform-submit').click();
      });

      /////////////////////// Homeowner Portal /////////////////////////////
      jQuery("input.dollar-amount").formatCurrency({symbol: ''});
      jQuery("#formula-component-sub_total").formatCurrency({symbol: ''});
      var sub_total = jQuery("#formula-component-sub_total").text();
      jQuery("#formula-component-sub_total").text( parseInt(sub_total).toLocaleString(undefined, {minimumFractionDigits: 2}) );

      //Make the Desktop Bid disable for HOMEOWNER
      if (( Drupal.settings.user != null) && (Drupal.settings.user.user_role == 'homeowner')) {
       jQuery("#webform-client-form-415 :input").prop("disabled", true);
       jQuery("#webform-client-form-415 :input.back-btn").prop("disabled", false);
      }

      //Disable the my-quote form
      jQuery("#webform-client-form-416 :input").prop("disabled", true);
      jQuery("#webform-client-form-416 :input.back-btn").prop("disabled", false);


      //OnClick of Popup of Contractor Listing
      jQuery(".top-info a").click(function(){
        var url = jQuery(this).attr('href');
        window.open(url, '_blank');
      });

      //Homowner Portal : Home image Icon tooltip
      $('#homeowner-portal-home, #node-419 .contractor-listing-table').tooltip({
        position: {
          my: "center bottom-20",
          at: "center top",
          collision: "flipfit",
          using: function (position, feedback) {
            $(this).css(position);
            $("<div>")
              .addClass("arrow")
              .addClass(feedback.vertical)
              .addClass(feedback.horizontal)
              .appendTo(this);
          }
        },
        content: function() {
          return $(this).attr('title');
        }
      });

      //Step1 : Hide RED Text once message opened
      jQuery(".estimation_message #step_1").click(function (e) {
        $variable_name = 'message_check';
        $contractors_id = $(this).attr("data-cid");
        $homeowner_id  = $(this).attr("cid");
        $relationship_id  = $(this).attr("rid");

        $.ajax({
          url: "update/variable/"+ $homeowner_id + "/" + $contractors_id + "/" + $relationship_id,
          success: function (data) {
            if (data.status == 'success') {
              if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.redirect_url.length != 0)) {
                var url = Drupal.settings.homeowner.redirect_url;
              } else {
                var url = '/homeowner-portal';
              }
              window.location.href = url;

            }
          }
        });
        return false;

      });

      //Set the Pre-Qualification status : Pending on click on Loan Apply
      jQuery("#apply-loan").click(function (e) {
        var pq_status = $("#pq_status").text();

        e.preventDefault();
        $homeOwnerId = $(this).attr('cid1');
        $statusName = 'custom_290';
        $statusValue = 'pending';
        $apply_loan = confirm("Are you sure to apply for loan");
        if ($apply_loan) {
          $.ajax({
            url: "loan/status/" + $statusName + "/" + $statusValue + "/" + $homeOwnerId,
            success: function (data) {
              if (data.status == 'success') {
                window.location.reload();
              }
            }
          });
          return false;
        }
      });

      //Choose Contractor and Upgrade step : Select the contractors
      jQuery(".participant-contractor-list #accordion").accordion({
        header: "span",
        collapsible: true,
        active: false
      });

      //Marked as checked : for already selected contractors
      jQuery("#contractor-table").find(".existing-record").find("input[type='checkbox']").prop('checked', true);
      jQuery("#contractor-table").find(".existing-record").find("input[type='checkbox']").attr('disabled', 'disabled');

      jQuery("#select_contractor").unbind().bind('click', function (e) {
        e.preventDefault();
        var favorite = [];
        var cid = [];

        //Get the selected Contractors
        //$.each($("input[name='contactors_list']:checked"), function(){
        $("input[name='contactors_list']:checked").not("[disabled]").each(function () {
          //var cname = $(this).parent().siblings('.view-contractors-label').text();
          var cname = $(this).parent().siblings('.view-contractors-label').find(".display-name").text();
          var contact_id_string = $(this).val();
          var contact_id = contact_id_string.replace('contractor-', '');
          favorite.push(cname);
          cid.push(contact_id);
        });

        //Check for empty values of array
        if (cid.length == 0) {
          alert("Select at-least one contractor");
          return false;
        }

        selected_contractors = confirm("Your selected contractors are -: " + favorite.join(", ") + "\n Click on OK button to proceed further");
        if (selected_contractors) {
          $.ajax({
            url: "select/contractors",
            type: 'POST',
            dataType: 'json',
            data: 'js=1&cid=' + cid,
            success: function (data) {
              if (data.status == 'success') {
                alert("New leads has been created with selected contractors");
              }
              window.location.reload();
            }
          });
          return false;
        }

      });


      //My Quote / Preliminary Estimate Form
      function _empowersbc_totalvalues_sum() {
        var total_values = 0;
        jQuery(".total_values input[type='text']").each(function() {
          total_values += parseInt(jQuery(this).val());
        });
        //SUM OF ALL Total VALUES
        jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-sub-total-total").val(total_values.toFixed(2));
        return total_values;
      }

      //Function to get the Sum of all points values as per checkbox checked
      function _empowersbc_pointvalues_sum() {
        var total_point_values = 0;
        jQuery(".point_values input[type='text']").each(function() {
          if(jQuery(this).closest('tr').find(".estimated-total").find('input').prop('checked') == true)
            total_point_values += parseInt(jQuery(this).val());
          //SUM OF ALL POINTS VALUES
          jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-total-sub-points-value-total").val(total_point_values);
        });
      }

      // Call Summation function on LOAD : Calculate the PE form summation FOR checked Estimated Values ONLY
      _empowersbc_pointvalues_sum();
      _empowersbc_totalvalues_sum();

      // Allow Only one checkbox to select for Heating, Air-Sealing and Water Heater
      jQuery(".estimated-total input[type='checkbox']").click(function (e) {

        //Check For Heating Class
        if(jQuery(this).closest('td').hasClass('heating-gas')) {
          if(jQuery('.heating-gas input:checked').length > 1) {
            alert("You cannot select both Gas Furnace ≥ 95% Efficient and Gas Wall Heater ≥ 70% Efficient");
            jQuery(".heating-gas input[type='checkbox']").attr('checked', false);
            jQuery(this).attr('checked', true);
          }
        }
        //Check For Air-sealing-improvement Class
        if(jQuery(this).closest('td').hasClass('air-sealing-improvement')) {
          if(jQuery('.air-sealing-improvement input:checked').length > 1) {
            alert("You cannot select both 30% or Better Improvement and 15% or Better Improvement");
            jQuery(".air-sealing-improvement input[type='checkbox']").attr('checked', false);
            jQuery(this).attr('checked', true);
          }
        }
        //Check for Heating
        if(jQuery(this).closest('td').hasClass('water-heating')) {
          if(jQuery('.water-heating input:checked').length > 1) {
            alert("You can select any one option either 'Tankless Water Heater (on demand) ≥ 0.82 EF' OR 'Gas Storage Water Heater ≥ 0.7 EF' OR 'Gas Storage Water Heater ≥ 0.67 EF' OR 'Push Button On Demand Hot Water Recirculation Pump' ");
            jQuery(".water-heating input[type='checkbox']").attr('checked', false);
            jQuery(this).attr('checked', true);
          }
        }
        _empowersbc_pointvalues_sum();
      });

      //Hide the Checkboxes to Homewoner If it's not checked
      if (( Drupal.settings.user != null) && (Drupal.settings.user.user_role == 'homeowner')) {
        jQuery("#desktop-bid-container input.form-checkbox").each(function () {
          var is_checked = jQuery(this).is(':checked');
          if (!is_checked) {
            jQuery(this).hide();
            jQuery(this).closest('tr').hide();
          }
        });
      }


      // PE Form change : DON't allow the user to add comma seperated  value for number
      var timer;var timer2;
      var timeout  = 1000;
      var timeout2 = 2000;
      jQuery(".total_values .form-number").keyup(function () {
        clearTimeout(timer);
        var _this = $(this);
        var estimated_rebate = jQuery(this).val();
        if( estimated_rebate ) {
          timer = setTimeout(function() {
            var estimated_rebate_replaced = parseInt(estimated_rebate.replace(/,/g, ''), 10);
            _this.val(estimated_rebate_replaced);
          }, timeout);
        }
        //Do the summation after removing the comma from number
        timer2 = setTimeout(function() {
          _empowersbc_totalvalues_sum();
        }, timeout2);
      });


      // Summation on Sub Total Values
      jQuery(".total_values input[type='text']").keyup(function () {
        var total_values = 0;
        var total_values_final = 0;
        jQuery(".total_values input[type='text']").each(function() {
           if( !jQuery(this).val() || 0 === jQuery(this).val().length ){
             jQuery(this).val(0); 
           }

          total_values += parseInt(jQuery(this).val());
        });

        // SUM OF ALL Total VALUES
        jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-sub-total-total").val(total_values.toFixed(2));
      });

      // Calculate the loan amount
      function _empowersbc_pmt_formula($interest, $months, $loan) {
        var remaining_balance_amount = 0;
        // As per Ticket:#9692404, If Loan is greater than 30K then use only 30K
        if($loan > 30000) {
          remaining_balance_amount = $loan - 30000;
          $loan = 30000;
        }
        jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-476").val(parseFloat(remaining_balance_amount).toFixed(2));

        $months = $months;
        $interest = $interest / 1200;
        var amount = $interest * -$loan * Math.pow((1 + $interest), $months) / (1 - Math.pow((1 + $interest), $months));
        jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-412").val(parseFloat(amount).toFixed(2));
      }

      var rebate_txt = "For the purposes of this estimate, rebate amounts are capped at $6,500";
      jQuery("#rebate-val").hover(
        function () {
          jQuery(this).append("<div id='low-income' class='low-income-popup2'>" + rebate_txt + " </div>");
        }, function () {
          jQuery(this).find("div:last").remove();
      });

      //Rebate Value Validation
      function _empowersbc_rebate_value_validation($rebate_value) {
        //If the Rebate value > $6500, Then set $6500
        if(parseFloat($rebate_value).toFixed(2) > 6500) {
          $rebate_value = 6500;
        }
         jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").val(parseFloat($rebate_value).toFixed(2));

      }

      var months = jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-month-for-estimated-empower-monthly-payment").val();

      // Default interest rate set as heighest 5.9%
      var loan_interest = 5.9;
      if (Drupal.settings.estimated_payment != null && Drupal.settings.estimated_payment.interest_rate.length != 0) {
        loan_interest = Drupal.settings.estimated_payment.interest_rate;
      }

      //Estimated monthly payment calculation based on Upgrade Type
      jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-404").on('change', function () {
        //var sub_total = jQuery("#formula-component-sub_total").text();
         var sub_total = _empowersbc_totalvalues_sum();

        //var sub_point_value_total = jQuery("#formula-component-sub_points_value_total").text();
        var sub_point_value_total = jQuery("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-total-sub-points-value-total").val();
        var advance_upgrade_value = 0;

        $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").prop("readonly", true);
        $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").val(parseFloat(advance_upgrade_value).toFixed(2));

        var upgrade_type = $(this).val();

        // If Upgrade Type = 'None' Then Estimated Rebate Value = 0.
        if (upgrade_type == "none") {
          var advance_upgrade_value = 0;
          //Rebate Value Validation
          _empowersbc_rebate_value_validation(advance_upgrade_value);
          var estimated_total_cost = parseFloat(sub_total) - parseFloat(advance_upgrade_value);
          var final_estimated_total_cost = estimated_total_cost.toFixed(2);

          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-408").val(final_estimated_total_cost);
          _empowersbc_pmt_formula(loan_interest, months, final_estimated_total_cost);
        }


        // If Upgrade Type = 'Home Upgrade' Then Estimated Rebate Value = 10 * Sub Point Values Totol
        if (upgrade_type == "HomeUpgrade") {
          var advance_upgrade_value = 10 * sub_point_value_total;
 
          //Rebate Value Validation
          _empowersbc_rebate_value_validation(advance_upgrade_value);

          //$("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").val(parseFloat(advance_upgrade_value).toFixed(2));
          var estimated_total_cost = parseFloat(sub_total) - parseFloat(advance_upgrade_value);
          var final_estimated_total_cost = estimated_total_cost.toFixed(2);

          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-408").val(final_estimated_total_cost);
          _empowersbc_pmt_formula(loan_interest, months, final_estimated_total_cost);
        }

        // If Upgrade Type = 'Advanced Home Upgrade' Then Estimated Rebate Value = 15% Sub Totol
        if (upgrade_type == "AdvancedHomeUpgrade") {
          var advance_upgrade_value = 0.15 * sub_total;

          //Rebate Value Validation
          _empowersbc_rebate_value_validation(advance_upgrade_value);

          //$("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").val(parseFloat(advance_upgrade_value).toFixed(2));

          var estimated_total_cost = parseFloat(sub_total) - parseFloat(advance_upgrade_value);
          var final_estimated_total_cost = estimated_total_cost.toFixed(2);

          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-408").val(final_estimated_total_cost);
          _empowersbc_pmt_formula(loan_interest, months, final_estimated_total_cost);
        }

        // If Upgrade Type = 'SimpleStart' Then Allow user to manual enter for  Estimated Rebate Value
        if (upgrade_type == "SimpleStart") {
          //Allow the user to enter the Estimated Rebate value
          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").prop("readonly", false);
          var estimated_rebate = $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").val();

          //Rebate Value Validation
          //_empowersbc_rebate_value_validation(estimated_rebate);

          var estimated_total_cost = parseFloat(sub_total) - parseFloat(estimated_rebate);
          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-408").val(estimated_total_cost.toFixed(2));

          $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-405").keyup(function () {
            var estimated_rebate = 0;
            estimated_rebate = $(this).val();

            if(estimated_rebate >= 6500) {
              estimated_rebate = 6500;
            }
            $(this).val(estimated_rebate);

            //Rebate Value Validation
            //_empowersbc_rebate_value_validation(estimated_rebate);

            var estimated_total_cost = parseFloat(sub_total) - parseFloat(estimated_rebate);
            var final_estimated_total_cost = estimated_total_cost.toFixed(2);
            $("#edit-submitted-civicrm-2-contact-1-fieldset-fieldset-civicrm-2-contact-1-relationship-custom-408").val(final_estimated_total_cost);
            _empowersbc_pmt_formula(loan_interest, months, final_estimated_total_cost);
          });
        }
      });

      jQuery('.chkbx').css("pointer-events", "none");
      //1. If User is login : Check True
      if (!anonymousUser) {
        jQuery('#hw-login').prop('checked', true);
      }

      //2. If EC Activity present for LoggedIn Homeowner : Check True
      if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.energy_coach_activity_id != null) && (Drupal.settings.homeowner.energy_coach_activity_id != 0)) {
        jQuery('#hw-visit').prop('checked', true);
      } else {
        //Else Allow the Homeowner to Fillup the FREE ECV form
      }

      //3. If Attended the past event : Check True
      if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.is_past_event != null) && (Drupal.settings.homeowner.is_past_event != 0)) {
        jQuery('#hw-event').prop('checked', true);
      }

      //4. If DOY  Activity present for LoggedIn Homeowner : Check True
      if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.diy_activity_id != null) && (Drupal.settings.homeowner.diy_activity_id.length != 0)) {
        jQuery('#hw-doit').prop('checked', true);
      }

      //5. Request Energy-saving Giveaway
      if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.energy_save_activity_id != null) && (Drupal.settings.homeowner.energy_save_activity_id.length != 0)) {
        jQuery('#hw-giveaway').prop('checked', true);
        //jQuery('#diyc').css("pointer-events", "none");
      }

      //6. Contact Free Low-income popups
      if (( Drupal.settings.homeowner != null) && (Drupal.settings.homeowner.low_income_weatherisation != null) && (Drupal.settings.homeowner.low_income_weatherisation.length != 0)) {
        jQuery('#hw-income').prop('checked', true);
        //jQuery('#diyc').css("pointer-events", "none");
      }

      var text1 = "Energy and Savings Assistance Programs provide no-cost weatherization services to low-income households who meet the CARE income guidelines. Services provided include attic insulation, energy efficient refrigerators, energy efficient furnaces, weather-stripping, caulking, low-flow showerheads, water heater blankets, and door and building envelope repairs which reduce air infiltration";
      var text2 = "CAC is dedicated to energy efficiency and conservation through education, home weatherization, gas appliance safety testing, and helping low-income families pay their utility bills. Their programs offer various appliance upgrades, insulation, lighting upgrades, weather-stripping, window and door replacements and water conservation measures";
      var text3 = "GRID Alternatives is a certified non-profit organization that brings together community partners, volunteers, and job trainees to implement solar power and energy efficiency for low-income families";

      $(".connect-me .form-checkboxes label").append("<span class='img-icons' id='info'></span>");

      //var form_label = ".form-item-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg31-custom-319";
      //@todo change the id 319 to 318 on Prod
      var form_label = ".form-item-submitted-civicrm-1-contact-1-fieldset-fieldset-civicrm-1-contact-1-cg31-custom-318";

      jQuery(form_label + "-1 label").hover(
        function () {
          jQuery(this).append("<div id='low-income' class='low-income-popup'>" + text1 + " </div>");
        }, function () {
          jQuery(this).find("div:last").remove();
        });
      jQuery(form_label + "-2 label").hover(
        function () {
          jQuery(this).append("<div id='low-income' class='low-income-popup'>" + text2 + " </div>");
        }, function () {
          jQuery(this).find("div:last").remove();
        });
      jQuery(form_label + "-3 label").hover(
        function () {
          jQuery(this).append("<div id='low-income' class='low-income-popup'>" + text3 + " </div>");
        }, function () {
          jQuery(this).find("div:last").remove();
        });
    }
  };
}(jQuery));

