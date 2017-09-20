<?php

/**
 * CustomLobby.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_lobby_Migrate($params) {
  $civiTable = "civicrm_value_lobby_22";
  $fzfdTable = "forumzfd_value_lobby_22";
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
  $logger = new CRM_Migration_Logger('lobby');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (wahlkreis_141 IS NOT NULL OR plz_bereich_142 IS NOT NULL 
    OR ausschussmitgliedschaften_143 IS NOT NULL OR weitere_funktionen_144 IS NOT NULL OR parteizugeh_rigkeit_145 IS NOT NULL 
    OR pol_funktion_148 IS NOT NULL OR zus_tzliche_informationen_149 IS NOT NULL OR bundestagsfraktion_227 IS NOT NULL
    OR wahlperiode_234 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All lobby custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' lobby custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomLobby', 'Migrate');
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
  if (!empty($daoSource->wahlkreis_141)) {
    $insertIndex++;
    $insertFields[] = 'wahlkreis_141';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->wahlkreis_141, 'String');
  }
  if (!empty($daoSource->plz_bereich_142)) {
    $insertIndex++;
    $insertFields[] = 'plz_bereich_142';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->plz_bereich_142, 'String');
  }
  if (!empty($daoSource->ausschussmitgliedschaften_143)) {
    $insertIndex++;
    $insertFields[] = 'ausschussmitgliedschaften_143';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ausschussmitgliedschaften_143, 'String');
  }
  if (!empty($daoSource->weitere_funktionen_144)) {
    $insertIndex++;
    $insertFields[] = 'weitere_funktionen_144';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->weitere_funktionen_144, 'String');
  }
  if (!empty($daoSource->parteizugeh_rigkeit_145)) {
    $insertIndex++;
    $insertFields[] = 'parteizugeh_rigkeit_145';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->parteizugeh_rigkeit_145, 'String');
  }
  if (!empty($daoSource->pol_funktion_148)) {
    $insertIndex++;
    $insertFields[] = 'pol_funktion_148';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->pol_funktion_148, 'String');
  }
  if (!empty($daoSource->zus_tzliche_informationen_149)) {
    $insertIndex++;
    $insertFields[] = 'zus_tzliche_informationen_149';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zus_tzliche_informationen_149, 'String');
  }
  if (!empty($daoSource->bundestagsfraktion_227)) {
    $insertIndex++;
    $insertFields[] = 'bundestagsfraktion_227';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bundestagsfraktion_227, 'String');
  }
  if (!empty($daoSource->wahlperiode_234)) {
    $insertIndex++;
    $insertFields[] = 'wahlperiode_234';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->wahlperiode_234, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
