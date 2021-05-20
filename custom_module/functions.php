<?php
/*
 * @file
 * Non-hook functions for empower module.
 */

/**
 * Custom function to Get the contact Id from Drupal User Id
 */
function _empowersbc_get_civicrm_contactid($userId) {
  if ( ! civicrm_initialize( ) ) {
    return;
  }
  require_once 'CRM/Core/BAO/UFMatch.php';

  $civicrm_contact = CRM_Core_BAO_UFMatch::getContactId( $userId );
  return $civicrm_contact;
}

/**
 * Custom function to Get the contact details from Civicrm contact Id
 */
function _empowersbc_get_civicrm_contactdetails($contactId) {
  if(!$contactId) {
    return;
  }

  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $contactId,
  );

  $currentEmployer = civicrm_api('Contact', 'getsingle', $params);
  return $currentEmployer;
}

/**
 * Custom function to Get the contact details from Civicrm contact Id
 */
function _empowersbc_get_civicrm_contact_country_details($contactId) {
  if(!$contactId) {
    return;
  }

  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id' => $contactId,
      'return' => "custom_186,county_id,country,postal_code"
  );

  $contact_details = civicrm_api('Contact', 'get', $params);
  $county_details = array();
  if($contact_details['is_error']== 0 && !empty($contact_details['values'])) {
    if(!empty($contact_details['values'][0]['custom_186'])) {
      //Removed county word so as to compatible for existing search
      $get_county_details =  $contact_details['values'][0]['custom_186'];
      $county = str_replace('County', "", $get_county_details);
      $county = trim($county);
      $county_details['custom_186'] =  $contact_details['values'][0]['custom_186'];
      $county_details['postal_code'] =  $contact_details['values'][0]['postal_code'];
      $county_details['country'] =  $contact_details['values'][0]['country'];
      $county_details['county_id'] =  $contact_details['values'][0]['county_id'];

      switch($get_county_details) {
        case "Santa Barbara County" :
          $county_details['name'] = "Santa Barbara County";
          $county_details['phone'] = "[805] 568-3566";
          $county_details['email'] = "emPower SBC@co.santa-barbara.ca.us";
          break;
        case "San Luis Obispo County" :
          $county_details['name'] = "San Luis Obispo County";
          $county_details['phone'] = "[805] 781-5625";
          $county_details['email'] = "empower@co.slo.ca.us";
          break;
        case "Ventura County" :
          $county_details['name'] = "Ventura County";
          $county_details['phone'] = "[805] 654-3834";
          $county_details['email'] = "empower@ventura.org";
          break;
        default:
          $county_details['name'] = "No County Selected";
      }
    }
    return $county_details;
  }

  return '';
}
/**
* Custom function  to Get the Relationship
*/
function _empowersbc_get_relationship($relationshipTypeId, $contact_a, $contact_b){
  if(empty($relationshipTypeId) || empty($contact_a) || empty($contact_b)) {
    return;
  }
  //Check if "Has Project with" relation already exists or Not
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contact_a,
      'contact_id_b' => $contact_b,
      'relationship_type_id' => 13,
      'is_active' => 1,
  );
  $resultGetRelationship = civicrm_api('Relationship', 'get', $params);
  if ( $resultGetRelationship['is_error'] == 0 && !empty( $resultGetRelationship['values']) ) {
    //$output = drupal_json_output(array('status' =>'success', 'relationship' => 'exists'));
    $output = $resultGetRelationship;
    return $output;
  } else {
    return false;
  }
}

/**
 * Custom function to Create Relationship Between Contractors and Homeowner
 */
function _empowersbc_create_relationship($relationshipTypeId, $contact_a, $contact_b){
  if(empty($relationshipTypeId) || empty($contact_a) || empty($contact_b)) {
      return;
  }
  $resultGetRelationship = _empowersbc_get_relationship($relationshipTypeId, $contact_a, $contact_b);
  $output = '';
  if ( $resultGetRelationship['is_error'] == 0 && empty( $resultGetRelationship['values']) ) {

    $current_date = date('Y-m-d');
    $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id_a' => $contact_a,
        'contact_id_b' => $contact_b,
        'relationship_type_id' => $relationshipTypeId,
        'start_date' => $current_date,
        'is_active' => 1
    );
    $resultCreateRelationship = civicrm_api('Relationship', 'create', $params);
    if($resultCreateRelationship['is_error'] == 0 && !empty($resultCreateRelationship['values'])) {
      $output = array("status" => "success", "relationship" => "created");
    }
    return $output;
  }

}

/**
 * Custom function to Check for the valid user relationship Civicrm contact Id
 */
function _empowersbc_valid_user($current_user_id, $webform_user_id ){
  //Get civicrm contact id from current drupal user
  $contact = _empowersbc_get_civicrm_contactid($current_user_id);
  if (! $contact) {
    return false;
  }
  // check relationship between employee and contractor employer
  $params  = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contact,
      'contact_id_b' => $webform_user_id,
      'relationship_type_id' => 4,
      'is_active' => 1,
  );

  $resultRelationshipAB = civicrm_api('Relationship', 'get', $params);

  //if its empty either relation is not preset or $webform_user_id is not organization contact.
  // check Relationship with employer
  if ( $resultRelationshipAB['is_error'] == 0 && ! empty( $resultRelationshipAB['values']) ) {
    return true;
  }
  else if ( $resultRelationshipAB['is_error'] == 0 && empty( $resultRelationshipAB['values']) ) {
    // Get employer data for logged in user
    $params  = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id_a' => $contact,
        'relationship_type_id' => 4,
        'is_active' => 1,
    );

    $resultEmployerDetails = civicrm_api('Relationship', 'get', $params);

    if ( $resultEmployerDetails['is_error'] == 0 && !empty( $resultEmployerDetails['values']) ) {
      $employerId = $resultEmployerDetails['values'][0]['contact_id_b'];
      // check relationship between employer and homeowner
      $params  = array(
          'version' => 3,
          'sequential' => 1,
          'contact_id_a' => $employerId,
          'contact_id_b' => $webform_user_id,
          'is_active' => 1,
      );

      $resultRelationshipHomeOwner = civicrm_api('Relationship', 'get', $params);

      if ( $resultRelationshipHomeOwner['is_error'] == 0 && ! empty( $resultRelationshipHomeOwner['values']) ) {
        return true;
      }
    }
  }
  return false;
}

/**
 * Custom function to get the activity for Given Civicrm contact Id and Activity Type
 */
function _empowersbc_get_activity($contactID, $activity_type) {
  if ($contactID && $activity_type ) {
    $sql = "select a.id from civicrm_activity a
       INNER JOIN civicrm_activity_contact at ON ( a.id = at.activity_id AND at.record_type_id = 3)
       where at.contact_id = {$contactID} AND a.activity_type_id = {$activity_type}
       order by a.id desc limit 1";
       return CRM_Core_DAO::singleValueQuery($sql);
  }

  return '';
}


/**
 * Custom function to get the activity for Given Civicrm contact Id and Activity Type
 */
function _empowersbc_get_activities($contactIDs, $activity_type) {
  $dataArray = array();
  if (!empty($contactIDs) && $activity_type ) {
    $contactIDs = implode(',', $contactIDs);
    $sql = "select a.id, at.contact_id, ctm.ecv_status_474 from civicrm_activity a
       INNER JOIN civicrm_activity_contact at ON ( a.id = at.activity_id AND at.record_type_id = 3)
       LEFT  JOIN civicrm_value_energy_coach_visit_6 ctm ON (ctm.entity_id = a.id)
       where at.contact_id IN ({$contactIDs}) AND a.activity_type_id = {$activity_type}
       group by at.contact_id
       order by a.id desc ";
     $dao = CRM_Core_DAO::executeQuery($sql);
     $dataArray = array();
     while ($dao->fetch()) {
       $dataArray[$dao->contact_id]['id'] = $dao->id;    
       $dataArray[$dao->contact_id]['status'] = $dao->ecv_status_474;
     }
  }

  return $dataArray;
}


/**
 * Custom function to get the activity details/data for Given Civicrm contact Id and Activity Type and Activity Id
 */
function _empowersbc_get_activity_details($contactID, $activity_type, $activityID) {
  if ($contactID && $activity_type && $activityID) {
    $result = civicrm_api3('Activity', 'get', array(
        'activity_type_id' => $activity_type,
        'contact_id' => $contactID,
        "id"=> $activityID
    ));

    if($result['is_error'] == 0 && !empty($result['values'])) {
      $activity_details = $result['values'][$activityID];
      return $activity_details;
    }
  }
  return '';
}

/**
 * Custom function to get the activity Status ( Custom_474)
 */
function _empowersbc_get_ecv_activity_status($contactId, $activityId) {

  if ($contactId && $activityId) {
    $result = civicrm_api3('Activity', 'get', array(
      'activity_type_id' => 41,
      'contact_id' => $contactId,
      "id"=> $activityId
    ));

    $activity_status = '';
    if($result['is_error'] == 0 && !empty($result['values']) ) {
      if(!empty($result['values'][$activityId]) && isset($result['values'][$activityId]['custom_474'])) {
        $activity_status = $result['values'][$activityId]['custom_474'];
        return $activity_status;
      }
    }
  }
  return '';
}

/**
 * Custom function to get the Participent events for Homeowner
 */
function _empowersbc_get_participent_past_events($contactID) {
  if ($contactID) {
     $participent_status =  array("Registered",  "Attended");
     $params = array(
        'sequential' => 1,
        'status_id' => array('IN' => $participent_status),
        'contact_id' => $contactID
    );
    // Civicrm Event API
    $result = civicrm_api3('Participant', 'get', $params);

    $current_date = date('Y-m-d H:i:s');
    $past_events = array();


    if ($result['is_error'] == 0 && !empty($result['values']) ) {
        foreach($result['values'] as $event_data) {
          //Check for the Past Dates of Events
          //if( strtotime($event_data['event_start_date']) < strtotime($current_date) &&
          //    strtotime($event_data['event_end_date']) < strtotime($current_date)
          //) {
              $past_events[] =  $event_data['id'];
          //}
        }
        return $past_events;
    }
  }

  return '';
}

/**
 * Custom function to Check the Valid relationship
 */
function _empowersbc_get_employer_id( ){
  global $user;
  //Get civicrm contact id from current drupal user
  $contact = _empowersbc_get_civicrm_contactid($user->uid);
  if (! $contact) {
    return false;
  }
  // check relationship between employee and contractor employer
  $params  = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contact,
      'relationship_type_id' => 4,
      'is_active' => 1,
  );

  $resultRelationshipAB = civicrm_api('Relationship', 'get', $params);
  if ( $resultRelationshipAB['is_error'] == 0 && ! empty( $resultRelationshipAB['values']) ) {
    return $employerId = $resultRelationshipAB['values'][0]['contact_id_b'];
  }
  return '';
}

/**
 * Custom function to get Postal/zip code for contact
 */
function _get_zip_for_contact($contact_id) {
  $sql = "select ca.postal_code
        from civicrm_address ca
          inner join civicrm_contact cc on ( cc.id = ca.contact_id)
        where cc.id = %1 and cc.is_deleted = 0 and ( ca.postal_code is not NULL and ca.postal_code <> '')
        limit 1";
  $p = array(1 => array($contact_id, 'Integer'));
  return CRM_Core_DAO::singleValueQuery($sql, $p);
}

/**
* Custom function to get Lending branch by valid Zip code
*/
function _get_lending_branch_by_zip($zip) {
    if(empty($zip)) {
      return;
    }

    $lending = array();
    $vccu = array(93013,93014,93429,93117,93110,93111,93116,93117,93118,93117,93103,93105,93103,93108,93150,93101,93102,93103,93105,93108,93109,93120,93121,93130,93140,93160,93190,93199,93013,93108,93067,93108,93013,93106,93107,91377,93010,93011,93012,93001,91320,93015,93016,91361,93023,93020,93021,91319,93022,93024,93030,93031,93032,93033,93034,93035,93036,93040,93041,93042,93043,93044,93003,93004,93060,93061,93063,93062,93064,93065,93094,93099,93066,91358,91359,91360,91362,93002,93005,93006,93007,93009);
    $chcu = array(93463,93427,93117,93463,93454,93434,93455,93455,93436,93440,93436,93455,93458,93460,93454,93427,93463,93436,93437,93436,93254,93438,93254,93457,93464,93456,93446,93420,93421,93422,93423,93424,93402,93407,93410,93410,93407,93409,93453,93428,93451,93430,93461,93432,93433,93483,93420,93421,93435,93446,93446,93446,93402,93412,93442,93443,93446,93444,93445,93475,93451,93446,93447,93420,93433,93448,93449,93453,93452,93401,93402,93403,93405,93406,93407,93408,93409,93410,93412,93451,93452,93453,93453,93461,93448,93449,93408,93401,93402,93403,93405,93406,93407,93408,93409,93410,93412,93453,93465);
    if(in_array($zip, $vccu)){
      $lending['branch'] = "Ventura County Credit Union";
      $lending['url']    = "https://www.vccuonline.net/loans/empowersbc-home-upgrade";
      $lending['loan_form']    = "https://dealers.loanspq.com/VenturaCountyCUSpecialLoans.aspx";
    } else if(in_array($zip, $chcu)) {
      $lending['branch'] = "CoastHills Credit Union";
      $lending['url']    = "http://www.coasthills.coop/loans-credit/personal-loans";
      $lending['loan_form']    = "https://dealers.loanspq.com/CoastHillsSpecialFCULoans.aspx";
    }
    return $lending;
}


/**
 * Custom function to Get the Project/Lead Status of Current user
 */
function _empowersbc_get_project_status($contact, $contractorId=null) {

  if ( ! civicrm_initialize( ) ) {
    return;
  }
  require_once 'CRM/Core/BAO/UFMatch.php';

  if(null == $contractorId){
    $contractorId = _empowersbc_get_employer_id( );
  }

  $projectStatus = '';
  // Get contacts for project has relationship type
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contact,
      'contact_id_b' => $contractorId,
      'relationship_type_id' => 13,
      'is_active' => 1,
  );

  $resultRelationshipAB = civicrm_api('Relationship', 'get', $params);

  if ( $resultRelationshipAB['is_error'] == 0 && ! empty( $resultRelationshipAB['values']) ) {
    $projectStatus = $resultRelationshipAB['values'][0]['custom_289'];
  }
  else if ( $resultRelationshipAB['is_error'] == 0 && empty( $resultRelationshipAB['values']) ) {
    $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id_a' => $contractorId,
        'contact_id_b' => $contact,
        'relationship_type_id' => 13,
        'is_active' => 1,
    );
    $resultRelationshipBA = civicrm_api('Relationship', 'get', $params);
    if ( $resultRelationshipBA['is_error'] == 0 && ! empty( $resultRelationshipBA['values']) ) {
      $projectStatus = $resultRelationshipBA['values'][0]['custom_289'];
    }
  }

  return $projectStatus;
}

/**
 * Custom function to Get the Count for Completed Project for Contractor
 */
function _empowersbc_get_completed_project_counts($contractorId) {
  if(empty($contractorId)) {
    return;
  }

  //Get the Completed Project Records of Contractor
  $params = array(
    'sequential' => 1,
    'contact_id_a' => $contractorId,
    'relationship_type_id' => 13,
    'is_active' => 1,
    'custom_289' => "Completed"
  );
  $result = civicrm_api3('Relationship', 'get', $params);

  $count = 0;
  if ( $result['is_error'] == 0 && ! empty( $result['values']) && !empty($result['count']) ) {
    $count = $result['count'];
  }
  return $count;
}

/***
* Custom function to get the Project Information
*/
function _empowersbc_get_project_information($contactId) {
  if(empty($contactId)) {
    return false;
  }

  /**
   * Custom_130 - Expected kWh saved per year
   * custom_104 - Expected Therms saved per year
   * custom_84 -
   * custom_143 - Project Status (gid:16)
   * custom_154 -
   * custom_144 -
   * custom_216 - Rebate Amount (gid:10)
   * custom_278 -
   * custom_298 -
   */
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $contactId,
      'return' => "custom_130, custom_104, custom_84, custom_143,custom_154,custom_144,custom_216, custom_278,custom_298",
  );

  $get_project_information = '';
  $resultProjectInformation = civicrm_api('Contact', 'get', $params);

  if ( $resultProjectInformation['is_error'] == 0 && ! empty( $resultProjectInformation['values']) ) {
    $get_project_information = $resultProjectInformation['values'][0];
  }
  return $get_project_information;
}

/***
 * Custom function to get the HOmeowner Intersted In Option from Constituent Information Form
 */
function _empowersbc_get_homeowner_interested_in_options($contactId)
{
  if (empty($contactId)) {
    return false;
  }

  //Civicrm Option Group Id for "Interested In" options.
  $optionGroupID = '172';
  $im_intested_in = array();
  $customValueLables = array();
  $get_constituent_information = '';
  $options = civicrm_api('OptionValue', 'get', array('version'=>3, 'option_group_id'=> $optionGroupID, 'option.limit'=>1000));
  if ($options['is_error'] == 0) {
    foreach ($options['values'] as $option) {
      $customValueLables[$option['value']] = $option['label'];
    }
  }

  $params = array(
    'version' => 3,
    'sequential' => 1,
    'id' => $contactId,
    'return' => "custom_277",
  );

  $resultContactInformation = civicrm_api('Contact', 'get', $params);
  if ( $resultContactInformation['is_error'] == 0 && ! empty( $resultContactInformation['values']) ) {
    $get_constituent_information = $resultContactInformation['values'][0];
    foreach( $get_constituent_information['custom_277']  as $selectedOption ) {
      if(array_key_exists($selectedOption, $customValueLables)) {
        $im_intested_in[] =  $customValueLables[$selectedOption];
      }
    }
  }
  return $im_intested_in;
}

/***
 * Custom function to get the Project Completion Report
 */
function _empowersbc_get_project_completed_report($contactId) {
  if(empty($contactId)) {
    return false;
  }
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $contactId,
      'return' => "custom_83,custom_84,custom_87,custom_90,custom_109,custom_111,custom_136,custom_216",
  );

  $get_project_completed_report = '';
  $resultProjectInformation = civicrm_api('Contact', 'get', $params);

  if ( $resultProjectInformation['is_error'] == 0 && ! empty( $resultProjectInformation['values']) ) {
    $get_project_completed_report = $resultProjectInformation['values'][0];
  }
  return $get_project_completed_report;
}

/***
 * Custom function to get the Project Completion Report For Lightning from DiY Checklist Activity
 */
function _empowersbc_get_lightning_report($contactId) {
  if(empty($contactId)) {
    return false;
  }
  $diy_activity_id = $get_diy_activity_details = $lightning_report = '';
  $diy_activity_id = _empowersbc_get_activity($contactId, '70');

  if(!empty($diy_activity_id)) {
    //Get the DIY Activity Details
    $get_diy_activity_details = _empowersbc_get_activity_details($contactId, 70, $diy_activity_id);
    if(!empty($get_diy_activity_details)) {
       if(isset($get_diy_activity_details['custom_313']) && !empty($get_diy_activity_details['custom_313'])) {
          $lightning_report = $get_diy_activity_details['custom_313'];
       }
    }
  }
  return $lightning_report;
}

/**
 *  Custom function to Get the sorted records as per project status mentioed
 */
function _empowersbc_sort_by_load_status ($result, $status_order) {
  if( empty($result)){
    return false;
  }

  usort($result, function ($a, $b) use ($status_order) {
    $pos_a = array_search($a['status'], $status_order);
    $pos_b = array_search($b['status'], $status_order);
    return $pos_a - $pos_b;
  });

  return $result;
}

/**
 * Custom function to get Signed Bid
 */
function _empowersbc_get_signed_bids($contactId, $contractorId) {
  if(empty($contactId) || empty($contractorId)) {
    return false;
  }

  $signed_bid_details = '';
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contractorId,
      'contact_id_b' => $contactId,
      'relationship_type_id' => 13,
      'is_active' => 1,
      'return' => "custom_304"
  );
  $resultRelationshipBA = civicrm_api('Relationship', 'get', $params);
  if ( $resultRelationshipBA['is_error'] == 0 && ! empty( $resultRelationshipBA['values']) ) {
    $signed_bid_details = $resultRelationshipBA['values'];
  }
  return $signed_bid_details;
}

/**
 * Custom function to get Preliminary Estimate Details of Homeowner
 */
function _empowersbc_get_preliminary_estimates($contactId, $contractorId) {
  if(empty($contactId) || empty($contractorId)) {
    return false;
  }

  $preliminary_estimate_details = array();
  //Return values : Upgrade Type, Estimated Rebate Value,Estimated Total Cost & Estimated emPower Monthly Payment & Is Viewed field
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contractorId,
      'contact_id_b' => $contactId,
      'relationship_type_id' => 13,
      'is_active' => 1,
      'return' => "id, custom_404,custom_405, custom_408, custom_412, custom_475"
  );

  $resultRelationshipBA = civicrm_api('Relationship', 'get', $params);
  if ( $resultRelationshipBA['is_error'] == 0 && ! empty( $resultRelationshipBA['values']) ) {
    $preliminary_estimate_details = $resultRelationshipBA['values'];
  }

  return $preliminary_estimate_details;
}

/**
 * Custom function to Get the Project updated alert count on Contractor Portal
 */
function _empowersbc_get_project_upated_alter($last_status_updated_date=null){
  $alert_counts = '';

  if(!empty($last_status_updated_date)){
    $today = date('Y-m-d');
    //Get the Date diff from Last updated date till today
    $diff = round(abs(strtotime($today)-strtotime($last_status_updated_date))/86400);

    $color = '#2F8000';
    if($diff <= 30){
      //Green Color
      $color = '#2F8000';
    } else if($diff > 30 && $diff <= 45){
      //yellow Color
      $color = '#f9ab03';
    } else if($diff >= 45){
      //Red Color
      $color = '#FF0000';
    }
    $alert_counts = "<span style='color:". $color ."'>(".  $diff. ")</span>";
  }

  return $alert_counts;
}


/**
 * Custom function to get Loan Details values
 */
function _empowersbc_get_loan_details($contactId) {
  if(empty($contactId)) {
    return false;
  }
  $params = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $contactId,
      'return' => "custom_278, custom_290, custom_291, custom_292, custom_293, custom_294, custom_295, custom_296, custom_297, custom_305, "
  );

  $pre_qualification_status = '';
  $resultPqStatus = civicrm_api('Contact', 'get', $params);

  if ( $resultPqStatus['is_error'] == 0 && ! empty( $resultPqStatus['values']) ) {
    $pre_qualification_status = $resultPqStatus['values'][0];
  }

  return $pre_qualification_status;
}

/**
 * Custom function to get Loan Details values
 */
function _empowersbc_set_loan_status($statusName, $statusValue, $contactId) {
  if ( ! civicrm_initialize( ) ) {
    return;
  }
  $response = '';
  $is_set = _empowersbc_set_values($statusName, $statusValue, $contactId);
  if(isset($is_set['value_set']) && $is_set['value_set'] == 'success') {
    $response = array('status' =>'success');
  }

  return drupal_json_output($response);
}

/**
 * Custom wrapper function to set the values in Civi Custom Fields
*/
function _empowersbc_set_values($fieldName, $fieldValue, $contactId) {
  if(empty($contactId) || empty($fieldName) || empty($fieldValue)) {
    return false;
  }
  //Set the  status value
  $custom_params = array();
  $custom_params[$fieldName] = $fieldValue;
  $custom_params['entityID'] = $contactId;
  $is_change = CRM_Core_BAO_CustomValueTable::setValues($custom_params);
  $output = '';
  if($is_change['is_error'] == 0) {
    $output = array('value_set' =>'success');
  }
  return $output;
}

/**
 * Custom wrapper function to Get the values in Civi Custom Fields
 */
function _empowersbc_get_values($fieldName, $fieldValue, $contactId) {
  if(empty($contactId) || empty($fieldName) || empty($fieldValue)) {
    return false;
  }
  //Set the  status value
  $custom_params = array();
  $custom_params[$fieldName] = $fieldValue;
  $custom_params['entityID'] = $contactId;
  $result = CRM_Core_BAO_CustomValueTable::getValues($custom_params);

  $output = '';
  if($result['is_error'] == 0) {
    $output = $result;
  }
  return $output;
}

/**
 * Custom function to Get the All status of Steps 1,2,3 & 4
 * Step1 : Signed Bid Uploaded
 * Step2 : Varification of Eligibilty Uploaded
 * Step3 and Step4 : Project Status "Completed"
 * Page : Homwowner Portal Home page
 */
function _empowersbc_get_steps_completion_status($contactId) {
  if(empty($contactId)) {
    return false;
  }
  $status = array();

  $project_status = $signed_bid = '';
  $get_selected_participating_contractor_id =  _empowersbc_get_selected_participating_contractors($contactId);
  if(!empty($get_selected_participating_contractor_id)) {
    $project_status = _empowersbc_get_project_status($contactId, $get_selected_participating_contractor_id);
    $signed_bid     = _empowersbc_get_signed_bids($contactId, $get_selected_participating_contractor_id);
  }

  // Get Signed Bid and Project Status from Project Information
  if(!empty($signed_bid)) {
    $status['step_1'] = 'completed';
  }
  if(!empty($project_status) && ($project_status == 'Completed')) {
    $status['step_3'] = 'completed';
    $status['step_4'] = 'completed';
  }

  //Get the Varification of Eligibilty Letter from Loan Details
  $get_loan_information = _empowersbc_get_loan_details($contactId);
  if(!empty($get_loan_information)) {
    $get_eligility_letter = $get_loan_information['custom_292'];
    if(!empty($get_eligility_letter)) {
      $status['step_2'] = 'completed';
    }
  }
  return $status;
}

/**
 * Get Selected Participating Contractor Based on the Signed Bid Uploaded
 */
function _empowersbc_get_selected_participating_contractors($contactId) {
  if(empty($contactId)) {
    return;
  }
  $get_selected_contractors = _empowersbc_get_contrator_names($contactId);

  $signed_bid = array();
  foreach($get_selected_contractors as $k => $contractors) {
    //Get the signed bid details
    $signed_bid     = _empowersbc_get_signed_bids($contactId, $contractors['id']);
    if(!empty($signed_bid) && isset($signed_bid[0]['custom_304'])) {
      $contractor_id = $contractors['id'];
      return $contractor_id;
    }
  }
  return '';
}

/**
 * Custom function to Create the New Leads Once contractor get chose by Homeowner
 * Page : Choose your contractor upgrade.
 */
function _empowersbc_select_participating_contractors() {
  global $user;

  if ( ! civicrm_initialize( ) ) {
    return;
  }

  //Get civicrm contact id from current drupal user
  $contact = _empowersbc_get_civicrm_contactid($user->uid);

  if(isset($_POST['cid']) && !empty($_POST['cid'])) {
    $contractorIds = array();
    $contractorIdsArr = explode(',', $_POST['cid']);
    //Create "Has Project with" Relationship
    $relationshipTypeId = 13;
    $contractorIds = $contractorIdsArr;
    $contactId = $contact;
    $leadStatus = array();
    $output = '';

    //Create new Relationship and Set the Status as "Lead"
     foreach ($contractorIds as $key => $contractorId) {
      //$leadStatus[] = _empowersbc_create_relationship($relationshipTypeId, $contractorId, $contactId);
      //return drupal_json_output($leadStatus);
       $current_date = date('Y-m-d');
       $params = array(
           'version' => 3,
           'sequential' => 1,
           'contact_id_a' => $contractorId,
           'contact_id_b' => $contactId,
           'relationship_type_id' => $relationshipTypeId,
           'start_date' => $current_date,
           'is_active' => 1,
           'custom_289'   => 'Lead',
           'custom_300'   => $current_date
       );

       $resultCreateRelationship = civicrm_api('Relationship', 'create', $params);
       if($resultCreateRelationship['is_error'] == 0 && !empty($resultCreateRelationship['values'])) {
         $output = array("status" => "success");
       }
    }
    return drupal_json_output($output);
  }
}

/**
 * Custom function to Get the lead info for Contractor
 */
function _get_lead_info_for_contractor($contractorID) {
  if ( ! civicrm_initialize( ) ) {
    return;
  }
  $config = CRM_Core_Config::singleton();
  $sql =

      'select r.id, r.contact_id_a, r.contact_id_b, r.relationship_type_id, r.is_active, r.start_date, c.project_status_289 as custom_289, c.last_status_update_300 as custom_300
        from civicrm_relationship r
           left join civicrm_value_lead_status_26  c on ( r.id = c.entity_id )
           inner join civicrm_contact cc on ( cc.id = r.contact_id_b and cc.is_deleted = 0 )
        where contact_id_a = %1 and r.relationship_type_id =13
        order by FIELD(c.project_status_289, "Lead", "Confirmed Lead", "Active",  "Completed", "Unrealized Lead", "Canceled") ,r.start_date desc ';

  $p = array(1 => array($contractorID, 'Integer'));
  $dao = CRM_Core_DAO::executeQuery($sql, $p);
  $dataArray = array();
  while ($dao->fetch()) {
    $daoArray = (array) $dao;
    if ($daoArray['custom_289'] == 'Lead' && $daoArray['start_date'] && strtotime($daoArray['start_date'])  < strtotime('-1 week') ){
      $daoArray['custom_289'] = 'Timed Out Leads';
    }
    //echo '<pre>'; print_r($daoArray); echo '</pre>';
    //$daoArray = array_map(function($item) { return array($item['element1']; }, $daoArray);
    unset($daoArray['_DB_DataObject_version']);
    unset($daoArray['__table']);
    unset($daoArray['N']);
    unset($daoArray['_database_dsn']);
    unset($daoArray['_database_dsn_md5']);
    unset($daoArray['_database']);
    unset($daoArray['_query']);
    unset($daoArray['_DB_resultid']);
    unset($daoArray['_resultFields']);
    unset($daoArray['_link_loaded']);
    unset($daoArray['_join']);
    unset($daoArray['_lastError']);
    array_push($dataArray, $daoArray);
  }
  return $dataArray;
}

/*
 * Custom function to get homeowner list for specified postal/zip code
 *
 */
function _get_homeowner_by_lender_zip($postal_code) {
  $postalCodes = '';
  if ( is_array($postal_code) and ! empty($postal_code)) {
    $postalCodes = implode(',', $postal_code);
  } else {
    return array();
  }
  $sql =
      "select cc.id, cc.display_name,
         cf.pre_qualification_status_290 as custom_290,
         cf.loan_application_status_293  as custom_293,
         ca.postal_code
        from civicrm_contact cc
           inner join civicrm_value_loan_details_11  cf on ( cc.id = cf.entity_id  )
           inner join civicrm_address ca on ( cc.id = ca.contact_id and ca.postal_code is not NULL)
        where  cc.contact_sub_type like '%Participating_Homeowner%' and cc.is_deleted = 0 and
              ca.postal_code in ( $postalCodes )
        ";

  //$p = array(1 => array($postalCodes, 'String'));
  $dao = CRM_Core_DAO::executeQuery($sql);
  $dataArray = array();
  $loan_status_count = array();
  while ($dao->fetch()) {
    $daoArray = (array) $dao;
    unset($daoArray['_DB_DataObject_version']);
    unset($daoArray['__table']);
    unset($daoArray['N']);
    unset($daoArray['_database_dsn']);
    unset($daoArray['_database_dsn_md5']);
    unset($daoArray['_database']);
    unset($daoArray['_query']);
    unset($daoArray['_DB_resultid']);
    unset($daoArray['_resultFields']);
    unset($daoArray['_link_loaded']);
    unset($daoArray['_join']);
    unset($daoArray['_lastError']);

    if (empty($daoArray['custom_293']) && empty($daoArray['custom_290'])) {
      continue;
    }
    $status = '';
    if ($daoArray['custom_293'] == 'pending') {
      $status = 'Pending Closing';
    } else if ($daoArray['custom_293'] == 'closed') {
      $status = 'Loan closed';
    } else if ($daoArray['custom_293'] == 'declined') {
      $status = 'Loan declined';
    } else if ($daoArray['custom_290'] == 'pending') {
      $status = 'Pending Pre-Qualification';
    } else if ($daoArray['custom_290'] == 'declined') {
      $status = 'Pre-qualification denied';
    } else if ($daoArray['custom_290'] == 'approved') {
      $status = 'Pre-Qualified';
    }
    $loan_status_count[] =$status;
    unset($daoArray['custom_290']);
    unset($daoArray['custom_293']);
    $daoArray['status'] = $status;

    array_push($dataArray, $daoArray);
  }
  $loan_status_count = array_count_values($loan_status_count);
  $status_order = array('Pending Pre-Qualification', 'Pre-Qualified', 'Pending Closing', 'Loan closed', 'Loan declined','Pre-qualification denied');
  $dataArray = _empowersbc_sort_by_load_status($dataArray, $status_order);
  return array($dataArray, $loan_status_count);
}

/*
 * Custom function to get Contractor Names
 *
 */
function _empowersbc_get_contrator_names($homeOwnerId) {
  if ( empty($homeOwnerId)) {
    return;
  }
  $sql = "select cca.id, cca.display_name from civicrm_contact cca inner join civicrm_relationship cr on( cca.id = cr.contact_id_a) left join civicrm_contact ccb on ( ccb.id = cr.contact_id_b )
where cr.relationship_type_id = 13 and cr.is_active = 1 and cr.contact_id_b = %1 and cca.is_deleted = 0";
  $p = array(1 => array($homeOwnerId, 'Integer'));
  $dao = CRM_Core_DAO::executeQuery($sql, $p);
  $dataArray = array();
  while ($dao->fetch()) {
    $daoArray = (array) $dao;
    unset($daoArray['_DB_DataObject_version']);
    unset($daoArray['__table']);
    unset($daoArray['N']);
    unset($daoArray['_database_dsn']);
    unset($daoArray['_database_dsn_md5']);
    unset($daoArray['_database']);
    unset($daoArray['_query']);
    unset($daoArray['_DB_resultid']);
    unset($daoArray['_resultFields']);
    unset($daoArray['_link_loaded']);
    unset($daoArray['_join']);
    unset($daoArray['_lastError']);
    array_push($dataArray, $daoArray);
  }
  return $dataArray;
}

/*
 * Custom function to Update the Drupal Variable
 */
function _empowersbc_update_variable_values($contactId, $contractor_ids, $relationship_id) {
   global $user;
   if ( ! civicrm_initialize( ) ) {
    return;
   }
   //Get civicrm contact id from current drupal user
   if(empty($contractor_ids) || empty($contactId) || empty($relationship_id)) {
      return;
   }

  $relationshipIds = explode("_",$relationship_id);
  $result = array();
  $custom_params = array();
  if(!empty($relationshipIds)) {
    foreach ($relationshipIds as $rid) {
      $custom_params['entityID'] = $rid;
      $custom_params['custom_475'] = 1;
      $result[] = CRM_Core_BAO_CustomValueTable::setValues($custom_params);
    }
  }

  $output = array("status" => "success");
  return drupal_json_output($output);
}

/*
 * Create Drupal User while creating civicrm Contact
 */
function _empowersbc_create_drupal_user($user_details , $role = '') {

  if ( ! civicrm_initialize( ) ) {
    return;
  }
  if(empty($user_details) && empty($user_details['email'])) {
    return;
  }
  $username = $user_details['email'];
  $email    = $user_details['email'];
  $contactId    = $user_details['contactId'];
  if(!empty($user_details['name'])) {
    $username = $user_details['name'];
  }

  $role_id = $role_name = '';
  $password = user_password(8);
  $status = 1;

  if ($role == 'homeowner'){
    $role_id = 5;
    $role_name = 'emPower homeowner';
  } else if($role == 'contractor') {
    $role_id = 6;
    $role_name = 'emPower contractor';
  } else if($role == 'lender') {
    $role_id = 7;
    $role_name = 'emPower lender';
  }

  $params = array(
    'name' => $username,
    'pass' => $password, // note: do not md5 the password
    'mail' => $email,
    'status' => 1,
    'init' => $email,
    'roles' => array(
      DRUPAL_AUTHENTICATED_RID => 'authenticated user',
      $role_id => $role_name,
    ),
  );


  try { 
      //Check If user with Same Email Id already Exist
      $user_exists = 0;
      if ($account = user_load_by_mail($email)) {  
        $user_exists = 1;
         
         //Show the message ONLY for Anonymouse user
         if (!user_is_logged_in()) {
           drupal_set_message(t('An account already exists for this email address. If you have forgotten your password, please <a href="/user/password">request a new one</a>'));
        }
        return false;
      }
      //the first parameter is left blank so a new user is created
      $user_object = user_save('', $params);
      if(!$user_exists) {
      drupal_set_message(t('Thank you! An account for your personalized Home Energy Portal has been created. You will receive an email shortly with login instructions.'));
        _user_mail_notify('register_no_approval_required', $user_object);
      }

      $sql = "UPDATE civicrm_uf_match uf SET uf.contact_id = {$contactId} WHERE uf.uf_id = ". $user_object->uid;
      $dao = CRM_Core_DAO::executeQuery($sql);
  } catch (Exception $e) { echo "in catch";
    drupal_set_message($e->getMessage());
  }


//  if ($user_object) { 
    //_user_mail_notify('register_admin_created', $user_object);
    //_user_mail_notify('register_no_approval_required', $user_object);
   // _user_mail_notify('register_pending_approval', $user_object);

 // }


  $current_path = current_path();
  //drupal_goto($current_path);
}

/*
 * Get the money Format
 */
function _empowersbc_money_format($amount) {
  if(empty($amount)) {
    return;
  }
  $formatted_amount = '';
  setlocale(LC_MONETARY, 'en_US.UTF-8');
  $formatted_amount = money_format('%.2n', $amount);
  return $formatted_amount;
}

?>
