<?php

/**
 * CustomPresse.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_presse_Migrate($params) {
  $civiTable = "civicrm_value_presse_6";
  $fzfdTable = "forumzfd_value_presse_6";
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
  $logger = new CRM_Migration_Logger('presse');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (medium_31 IS NOT NULL 
    OR fachpresse_32 IS NOT NULL OR in_presseverteiler_33 IS NOT NULL OR themen_35 IS NOT NULL OR vip_kontakt_152 IS NOT NULL OR 
    dateianhang_153 IS NOT NULL OR position_154 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All presse custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' presse data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomPresse', 'Migrate');
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
  if (!empty($daoSource->medium_31)) {
    $insertIndex++;
    $insertFields[] = 'medium_31';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->medium_31, 'String');
  }
  if (!empty($daoSource->fachpresse_32)) {
    $insertIndex++;
    $insertFields[] = 'fachpresse_32';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fachpresse_32, 'Integer');
  }
  if (!empty($daoSource->in_presseverteiler_33)) {
    $insertIndex++;
    $insertFields[] = 'in_presseverteiler_33';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->in_presseverteiler_33, 'Integer');
  }
  if (!empty($daoSource->themen_35)) {
    $insertIndex++;
    $insertFields[] = 'themen_35';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->themen_35, 'String');
  }
  if (!empty($daoSource->vip_kontakt_152)) {
    $insertIndex++;
    $insertFields[] = 'vip_kontakt_152';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vip_kontakt_152, 'Integer');
  }
  if (!empty($daoSource->dateianhang_153)) {
    $insertIndex++;
    $insertFields[] = 'dateianhang_153';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dateianhang_153, 'Integer');
  }
  if (!empty($daoSource->position_154)) {
    $insertIndex++;
    $insertFields[] = 'position_154';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->position_154, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
