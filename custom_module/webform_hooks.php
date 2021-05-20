<?php
/*
 * @file
 * All webforms hook functions for empower module.
 */

/**
 * Implements Webform's  hook_webform_submission_insert().
 */
function empowersbc_webform_submission_insert($node, $submission) {
  //Node id for "Create New lead form" =  $submission->nid == 363 on Production
  if($submission->nid == 363 && !empty($submission->data)){
     $fname = $submission->data[2][0];
     $lname = $submission->data[3][0];
     $email = $submission->data[4][0];

     // New User has been created In Drupal & also New Contact in Civicrm Because on In this webform
     // Contact is Not a Existing contact so New contact will get created and as per code in Civi Extension
     // New Drupal user gets created on creation on new contact

     //Get the contact details of newly created user
     try {
      $account = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'contact_type' => "Individual",
        'email' => $email,
        'is_primary' => 1
      ));

      } catch (Exception $e) {
        drupal_set_message($e->getMessage());
      }

     if(($account['is_error'] == 0) && (!empty($account['values']))) {
      $homeOwnerID  = $account['id'];
      $contractorID = _empowersbc_get_employer_id();

      // Create new lead and relationship of 'Has Project with'
      if(!empty($homeOwnerID) && !empty($contractorID)){
        $params  = array(
            'version' => 3,
            'sequential' => 1,
            'contact_id_a' => $contractorID,
            'contact_id_b' => $homeOwnerID,
            'relationship_type_id' => 13,
            'start_date' => date("Y-m-d"),
            'is_active' => 1,
            'custom_289'   => 'Confirmed Lead',
        );
        $is_create = civicrm_api('Relationship', 'create', $params);
        drupal_set_message(t('New lead has been created'));
      }
    }

    //Free Income Service
  } else if($submission->nid == 392 && !empty($submission->data)) {

    global $user;
    if(isset($_GET['cid1'])) {
      $contact_id = $_GET['cid1'];
    } else {
      $contact_id = $user->uid;
    }

    //Check for the values.
    $sid = $submission->sid;
    $nid = $submission->nid;
    $total_people =  _webform_get_value_key('civicrm_1_contact_1_cg31_custom_315', $sid, $nid) ? _webform_get_value_key('civicrm_1_contact_1_cg31_custom_315', $sid, $nid): "";
    $rent_own     =  _webform_get_value_key('civicrm_1_contact_1_cg31_custom_316', $sid, $nid) ? _webform_get_value_key('civicrm_1_contact_1_cg31_custom_316', $sid, $nid): "";
    $interest     =  _webform_get_value_key('civicrm_1_contact_1_cg31_custom_317', $sid, $nid) ? _webform_get_value_key('civicrm_1_contact_1_cg31_custom_317', $sid, $nid): "";
    $connect      =  _webform_get_value_key('civicrm_1_contact_1_cg31_custom_318', $sid, $nid) ? _webform_get_value_key('civicrm_1_contact_1_cg31_custom_318', $sid, $nid): "";

    //Create the Activity
    $result = civicrm_api3('Activity', 'create', array(
        'sequential' => 1,
        'activity_type_id' => "FREE Low-Income",
        'subject' => "Low-Income Weatherization Service",
        'custom_325' => $total_people,
        'custom_326' => $rent_own,
        'custom_327' => $interest,
        'custom_328' => $connect,
        'source_contact_id' => $contact_id,
    ));


    if($result['is_error'] == 0 && !empty($result['values'])) {
        drupal_set_message(t('Form has been submitted successfully'));
    }
  } else if($submission->nid == 320 && !empty($submission->data)) {

    $sid = $submission->sid;
    $nid = $submission->nid;
    // Create New user on submission of Contact Us form
    $fname  = _webform_get_value_key('civicrm_1_contact_1_contact_first_name', $sid, $nid);
    $lname  = _webform_get_value_key('civicrm_1_contact_1_contact_last_name', $sid, $nid);
    $city   = _webform_get_value_key('civicrm_1_contact_1_address_city', $sid, $nid);
    $postal = _webform_get_value_key('civicrm_1_contact_1_address_postal_code', $sid, $nid);
    $email  = _webform_get_value_key('civicrm_1_contact_1_email_email', $sid, $nid);
    $phone  = _webform_get_value_key('civicrm_1_contact_1_phone_phone', $sid, $nid);
    $county = _webform_get_value_key('civicrm_1_contact_1_cg1_custom_186', $sid, $nid);

    //check for Email Id already exists
    $get_user = civicrm_api3('Contact', 'get', array(
      'sequential' => 1,
      'contact_type' => "Individual",
      'email' => $email,
      'is_primary' => 1
    ));

    //Create new user if No records exists
    if(($get_user['is_error'] == 0) && (!empty($get_user['values']))) {
      if(isset($get_user['values'][0]['email']) && !empty($get_user['values'][0]['email'])) {
        $email_id   = $get_user['values'][0]['email'];
        $first_name = $get_user['values'][0]['first_name'];
        $last_name  = $get_user['values'][0]['last_name'];

        if(!empty($first_name) && !empty($last_name)) {
          $user_name = $first_name.'_'.$last_name;
          $user_name = strtolower($user_name);
        } else {
          $user_name = $email_id;
        }

        $fields = array(
          'email' => $email_id,
          'name'  => $user_name
        );

        //Check IF user is already exist or Not.
        if ($account =  user_load_by_mail($email_id)) {
            //return false;
        } else {
            //Create Drupal Homeowner User
           //_empowersbc_create_drupal_user($fields, 'homeowner');
        }
      }
    }
    // 50 Home Challenge
  } else if($submission->nid == 438 && !empty($submission->data)) {
      $sid = $submission->sid;
      $nid = $submission->nid;
      $email  = _webform_get_value_key('civicrm_1_contact_1_email_email', $sid, $nid);

      try {
        $account = civicrm_api3('Contact', 'get', array(
          'sequential' => 1,
          'contact_type' => "Individual",
          'contact_sub_type' => "Participating_Homeowner",
          'email' => $email,
          'is_primary' => 1
        ));
      } catch (Exception $e) {
        drupal_set_message($e->getMessage());
      }

      if(($account['is_error'] == 0) && (!empty($account['values']))) {
        $homeOwnerID  = $account['id'];

        // For Testing, Contractor ID is Cividesk
        $contractorID = 8494;

        // Create new lead and relationship of 'Has Project with'
        if(!empty($homeOwnerID) && !empty($contractorID)){
          $params  = array(
            'version' => 3,
            'sequential' => 1,
            'contact_id_a' => $contractorID,
            'contact_id_b' => $homeOwnerID,
            'relationship_type_id' => 13,
            'start_date' => date("Y-m-d"),
            'is_active' => 1,
            'custom_289'   => 'Pending Visit',
          );
          $is_create = civicrm_api('Relationship', 'create', $params);
          if( $params['is_error'] == 1 ) {
             drupal_set_message("Something is wrong there..");
          }
      }
    }
  }
}

/**
 * Implements Webform's  hook_webform_submission_presave().
 */
function empowersbc_webform_submission_presave($node, &$submission) {
  $fname = $lname = $city = $postal = $email = $phone = $county = '';
  if($submission->nid == 373 && !empty($submission->data)){
    $submission->data[7][0] = date('Y-m-d');
  }
}

/**
 * Custom function to get the submission using API webform_get_submissions().
 */
function _empowersbc_webform_get_submissions($nid , $uid) {
  if(empty($nid) || empty($uid)) {
    return;
  }

  module_load_include('inc', 'webform', 'includes/webform.submissions');

  $submission = '';
  $filter = array('nid' => $nid, 'uid' => $uid);

  $submission = webform_get_submissions($filter);

  if(!empty($submission)) {
    return $submission;
  }
}

// Helper function to get the Value from Key of Webform
function _webform_get_value_key($key, $sid, $nid) {
  module_load_include('inc','webform','includes/webform.submissions');
  $node = node_load($nid);
  $compMap = array();
  foreach ($node->webform['components'] as $c) {
    $compMap[$c['form_key']] = $c['cid'];
  }

  $value = '';
  $submission = webform_get_submissions(array('sid' => $sid));
  if (array_key_exists($compMap[$key], $submission[$sid]->data)) {
    $value = $submission[$sid]->data[$compMap[$key]][0];
  }
  return $value;
}

?>
