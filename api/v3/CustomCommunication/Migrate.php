<?php
/**
 * CustomCommunication.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_communication_Migrate($params) {
  $civiTable = "civicrm_value_communication_details";
  $fzfdTable = "forumzfd_value_communication_details";
  set_time_limit(0);
  $countCreated = 0;
  $countLogged = 0;
  $returnValues = array();
  //first create custom group if it does not exist yet
  if (!CRM_Core_DAO::checkTableExists($civiTable)) {
    $queryGroupDefinition = 'SELECT * FROM forumzfd_custom_group WHERE table_name = %1 ';
    $daoGroupDefinition = CRM_Core_DAO::executeQuery($queryGroupDefinition,
      array(1 => array($civiTable, 'String')));
    if ($daoGroupDefinition->fetch()) {
      CRM_Migration_ContactCustomData::createContactCustomGroup($daoGroupDefinition);
    }
  }
  $logger = new CRM_Migration_Logger('communication_details');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (best_time_to_contact IS NOT NULL OR 
    communication_status IS NOT NULL OR reason_for_do_not_mail IS NOT NULL
    OR reason_for_do_not_phone IS NOT NULL OR reason_for_do_not_email IS NOT NULL) LIMIT 5000";
  $daoSource = CRM_Core_DAO::executeQuery($querySource);
  while ($daoSource->fetch()) {
    // update is_processed
    $updateQuery = 'UPDATE '.$fzfdTable.' SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    // check if contact exists
    $contactCount = civicrm_api3('Contact', 'getcount', array(
      'id' => $daoSource->entity_id,
    ));
    if ($contactCount == 1) {
      $countCreated++;
      $insertQuery = NULL;
      $insertParams = array();
      _createQueryAndParams($civiTable, $daoSource, $insertQuery, $insertParams);
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    } else {
      $logger->logMessage('Error', 'No contact '.$daoSource->entity_id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  if ($daoSource->N == 0) {
    $returnValues[] = 'All communication details custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' communication details custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomCommunication', 'Migrate');
}

/**
 * Method to get the query for inserting
 *
 * @param $civiTable
 * @param $daoSource
 * @param $insertQuery
 * @param $insertParams
 */
function _createQueryAndParams($civiTable, $daoSource, &$insertQuery, &$insertParams) {
  $insertIndex = 1;
  $insertFields = array('entity_id');
  $insertValues = array('%1');
  $insertParams = array(1 => array($daoSource->entity_id, 'Integer'));
  if (!empty($daoSource->best_time_to_contact)) {
    $insertIndex++;
    $insertFields[] = 'best_time_to_contact';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->best_time_to_contact, 'String');
  }
  if (!empty($daoSource->communication_status)) {
    $insertIndex++;
    $insertFields[] = 'communication_status';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->communication_status, 'String');
  }
  if (!empty($daoSource->reason_for_do_not_mail)) {
    $insertIndex++;
    $insertFields[] = 'reason_for_do_not_mail';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->reason_for_do_not_mail, 'String');
  }
  if (!empty($daoSource->reason_for_do_not_phone)) {
    $insertIndex++;
    $insertFields[] = 'reason_for_do_not_phone';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->reason_for_do_not_phone, 'String');
  }
  if (!empty($daoSource->reason_for_do_not_email)) {
    $insertIndex++;
    $insertFields[] = 'reason_for_do_not_email';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->reason_for_do_not_email, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
