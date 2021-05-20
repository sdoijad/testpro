<?php

/*
 * @file
 * Form-handling functions And Page Callback functions  for empowermodule.
 */

/**
 *  Function to call the accept the lead
 **/
function _empowersbc_accept_lead($relID, $contractorID){

  if ( ! civicrm_initialize( ) ) {
    return;
  }
  $params  = array(
      'version' => 3,
      'sequential' => 1,
      'id' => $relID,
    //'contact_id_a' => $contractorID,
    //'contact_id_b' => $homeOwnerID,
      'relationship_type_id' => 13,
      'is_active' => 1,
  );

  $resultRelationshipAB = civicrm_api('Relationship', 'getsingle', $params);

  if ( ! empty($resultRelationshipAB)) {
    $updateParams = array(
        'version'      => 3,
        'id'           => $resultRelationshipAB['id'],
        'is_active'    => 1,
        'custom_289'   => 'Confirmed Lead',
    );

    $out = civicrm_api('Relationship', 'create', $updateParams);
    return drupal_json_output(array('status' =>'success'));

  }
}

/**
 *  Function to call the Do Not accept the lead
 **/
function _empowersbc_donot_accept_lead($homeOwnerID, $contractorID){
  if ( ! civicrm_initialize( ) ) {
    return;
  }
  $params  = array(
      'version' => 3,
      'sequential' => 1,
      'contact_id_a' => $contractorID,
      'contact_id_b' => $homeOwnerID,
      'relationship_type_id' => 13,
      'is_active' => 1,
  );

  $resultRelationshipAB = civicrm_api('Relationship', 'getsingle', $params);

  if ( ! empty($resultRelationshipAB)) {
    $updateParams = array(
        'version'      => 3,
        'id'           => $resultRelationshipAB['id'],
        'is_active'    => 1,
        'custom_289'   => 'Unrealized Lead',
    );

    $out = civicrm_api('Relationship', 'create', $updateParams);
    return drupal_json_output(array('status' =>'success'));

  }
}

/**
 * Custom function to send email in Pre-Installation
 */
function _empowersbc_submit_review_pre($contactId) {
  global $user;
  if(in_array('emPower lender',$user->roles)){

    //Loan-detail form
    $form_id = 'webform_client_form_352' ;
    $nid     = 352;
    $node = node_load($nid);
    $pq_status = 'pending';

    $lender_id = '';
    if(isset($contactId)) {
      $lender_id = $contactId;
    }

    $data = array(
        2 => array($pq_status),
        5 => array($lender_id),
    );

    $submission = (object) array(
        'nid' => $nid,
        'uid' => $user->uid,
        'submitted' => REQUEST_TIME,
        'completed' => REQUEST_TIME,
        'modified'  => REQUEST_TIME,
        'remote_addr' => ip_address(),
        'is_draft' => 0,
        'data' => $data
    );

    // module_load_include('inc', 'webform', 'includes/webform.submissions');
    // $insert =  webform_submission_insert($node, $submission);
  }

  //$to = "sachin@cividesk.com";
  $to = "emPowerSBC@co.santa-barbara.ca.us";
  $params = array();
  drupal_mail('empowersbc', 'loan_review_pre', $to, language_default(), $params, 'emPower', TRUE);
  drupal_set_message(t('Email has been sent to emPower to review your Loan Details submit'));
  //return drupal_json_output(array('status' =>'success'));
  echo drupal_json_output(array('status' =>'success'));
  exit;
}

/**
 * Custom function to send email in Post-Installation
 */
function _empowersbc_submit_review_post($contactId) {

  global $user;
  if(in_array('emPower lender',$user->roles)){

    //Loan-detail form
    $form_id = 'webform_client_form_352' ;
    $nid     = 352;
    $node = node_load($nid);

    $la_status = 'pending';

    $lender_id = '';
    if(isset( $contactId )) {
      $lender_id = $contactId;
    }

    $data = array(
        4 => array($la_status),
        5  => array($lender_id),
    );

    $submission = (object) array(
        'nid' => $nid,
        'uid' => $user->uid,
        'submitted' => REQUEST_TIME,
        'completed' => REQUEST_TIME,
        'modified'  => REQUEST_TIME,
        'remote_addr' => ip_address(),
        'is_draft' => 0,
        'data' => $data
    );

    //module_load_include('inc', 'webform', 'includes/webform.submissions');
    //webform_submission_insert($node, $submission);
  }


  //$to = "sachin@cividesk.com";
  $to = "emPowerSBC@co.santa-barbara.ca.us";
  $params = array();
  drupal_mail('empowersbc', 'loan_review_post', $to, language_default(), $params, 'emPower', TRUE);
  drupal_set_message(t('Email has been sent to emPower to review your Loan Details submit'));
  //return drupal_json_output(array('status' =>'success'));
  echo drupal_json_output(array('status' =>'success'));
  exit;
}

?>
