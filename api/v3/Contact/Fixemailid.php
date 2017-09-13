<?php

/**
 * Contact.Fixemailid API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_Fixemailid($params) {
  set_time_limit(0);
  $returnValues = array();
  $fixedIds = 0;
  // fix the ones with email
  $query = "SELECT fc.new_contact_id, fc.id AS source_id FROM forumzfd_contact fc 
    LEFT JOIN civicrm_email em ON fc.new_contact_id = em.contact_id
    WHERE em.id IS NOT NULL AND fc.fixed_id = %1 AND fc.new_contact_id IS NOT NULL LIMIT 5000";
  $dao = CRM_Core_DAO::executeQuery($query, array(1 => array(0, 'Integer')));
  while ($dao->fetch()) {
    $fixedIds++;
    $savedEmails = array();
    // find all emails for contact
    $foundEmails = civicrm_api3('Email', 'get', array(
      'contact_id' => $dao->new_contact_id,
      'options' => array('limit' => 0),
    ));
    // foreach found, store values and delete emails
    foreach ($foundEmails['values'] as $foundEmail) {
      $savedValues = array(
        'location_type_id' => $foundEmail['location_type_id'],
        'is_primary' => $foundEmail['is_primary'],
        'email' => $foundEmail['email'],
      );
      $savedEmails[] = $savedValues;
      civicrm_api3('Email', 'delete', array('id' => $foundEmail['id']));
    }
    // now update contact and recreate emails for new contact id
    $updateContactQuery = 'UPDATE civicrm_contact SET id = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateContactQuery, array(
      1 => array($dao->source_id, 'Integer'),
      2 => array($dao->new_contact_id, 'Integer'),
    ));
    foreach ($savedEmails as $savedEmail) {
      $emailParams = array(
        'contact_id' => $dao->source_id,
        'location_type_id' => $savedEmail['location_type_id'],
        'is_primary' => $savedEmail['is_primary'],
        'email' => $savedEmail['email'],
      );
      civicrm_api3('Email', 'create', $emailParams);
    }
    $updateFixed = "UPDATE forumzfd_contact SET fixed_id = %1 WHERE id = %2";
    CRM_Core_DAO::executeQuery($updateFixed, array(
      1 => array(1, 'Integer'),
      2 => array($dao->source_id, 'Integer'),
    ));
  }
  if (empty($dao->N)) {
    // set next contact id
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM civicrm_contact');
    $maxId++;
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_contact AUTO_INCREMENT = '.$maxId);
    $returnValues[] = 'No more contacts with email to fix';
  } else {
    $returnValues[] = $fixedIds.' contact ids which had emails fixed';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Contact', 'Fixemailid');
}
