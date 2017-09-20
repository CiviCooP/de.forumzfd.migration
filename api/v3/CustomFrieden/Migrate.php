<?php

/**
 * CustomFrieden.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_frieden_Migrate($params) {
  $civiTable = "civicrm_value_friedenslauf_13";
  $fzfdTable = "forumzfd_value_friedenslauf_13";
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
  $logger = new CRM_Migration_Logger('friendenslauf_13');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (friedenslauf_88 IS NOT NULL OR 
    generell_kein_interesse_89 IS NOT NULL OR anzahl_teilnehmende_90 IS NOT NULL
    OR vorraussichtliche_teilnahme_91 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All friendenslauf_13 custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' friendenslauf_13 custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomFrieden', 'Migrate');
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
  if (!empty($daoSource->friedenslauf_88)) {
    $insertIndex++;
    $insertFields[] = 'friedenslauf_88';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->friedenslauf_88, 'String');
  }
  if (!empty($daoSource->generell_kein_interesse_89)) {
    $insertIndex++;
    $insertFields[] = 'generell_kein_interesse_89';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->generell_kein_interesse_89, 'Integer');
  }
  if (!empty($daoSource->anzahl_teilnehmende_90)) {
    $insertIndex++;
    $insertFields[] = 'anzahl_teilnehmende_90';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->anzahl_teilnehmende_90, 'Float');
  }
  if (!empty($daoSource->vorraussichtliche_teilnahme_91)) {
    $insertIndex++;
    $insertFields[] = 'vorraussichtliche_teilnahme_91';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vorraussichtliche_teilnahme_91, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
