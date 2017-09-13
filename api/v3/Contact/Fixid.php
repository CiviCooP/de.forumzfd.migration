<?php

/**
 * Contact.Fixid API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_Fixid($params) {
  set_time_limit(0);
  $returnValues = array();
  $fixedIds = 0;
  // fix the ones without email
  $query = "SELECT fc.new_contact_id, fc.id AS source_id FROM forumzfd_contact fc 
    LEFT JOIN civicrm_email em ON fc.new_contact_id = em.contact_id
    WHERE em.id IS NULL AND fc.fixed_id = %1 AND fc.new_contact_id IS NOT NULL LIMIT 5000";
  $dao = CRM_Core_DAO::executeQuery($query, array(1 => array(0, 'Integer')));
  while ($dao->fetch()) {
    $fixedIds++;
    $updateContact = 'UPDATE civicrm_contact SET id = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateContact, array(
      1 => array($dao->source_id, 'Integer'),
      2 => array($dao->new_contact_id, 'Integer'),
    ));
    $updateFixed = "UPDATE forumzfd_contact SET fixed_id = %1 WHERE id = %2";
    CRM_Core_DAO::executeQuery($updateFixed, array(
      1 => array(1, 'Integer'),
      2 => array($dao->source_id, 'Integer'),
    ));
  }
  if (empty($dao->N)) {
    $returnValues[] = 'No more contacts to fix';
  } else {
    $returnValues[] = $fixedIds.' contact ids fixed';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Contact', 'Fixid');
}
