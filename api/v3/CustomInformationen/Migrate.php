<?php

/**
 * CustomInformationen.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_informationen_Migrate($params) {
  $civiTable = "civicrm_value_informationen_1";
  $fzfdTable = "forumzfd_value_informationen_1";
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
  $logger = new CRM_Migration_Logger('informationen');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (abo_fbf_4 IS NOT NULL OR fbf_dauerabo_5 IS NOT NULL 
    OR fbf_exemplare_6 IS NOT NULL OR infos_nur_auf_englisch_8 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All informationen custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' informationen custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomInformationen', 'Migrate');
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
  if (!empty($daoSource->abo_fbf_4)) {
    $insertIndex++;
    $insertFields[] = 'abo_fbf_4';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->abo_fbf_4, 'String');
  }
  if (!empty($daoSource->fbf_dauerabo_5)) {
    $insertIndex++;
    $insertFields[] = 'fbf_dauerabo_5';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fbf_dauerabo_5, 'Integer');
  }
  if (!empty($daoSource->fbf_exemplare_6)) {
    $insertIndex++;
    $insertFields[] = 'fbf_exemplare_6';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fbf_exemplare_6, 'Integer');
  }
  if (!empty($daoSource->infos_nur_auf_englisch_8)) {
    $insertIndex++;
    $insertFields[] = 'infos_nur_auf_englisch_8';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->infos_nur_auf_englisch_8, 'Integer');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
