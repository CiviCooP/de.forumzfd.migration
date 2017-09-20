<?php

/**
 * CustomAkademie.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_akademie_Migrate($params) {
  $civiTable = "civicrm_value_akademie_12";
  $fzfdTable = "forumzfd_value_akademie_12";
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
  $logger = new CRM_Migration_Logger('akademie_12');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (kursteilnahme_84 IS NOT NULL OR 
    aufgaben_im_akademie_team_437 IS NOT NULL OR gewichtung_anzeige_teamseite_438 IS NOT NULL
    OR funktion_en__454 IS NOT NULL OR aufgaben_im_akademie_team_en__455 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All akademie_12 custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' akademie_12 custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomAkademie', 'Migrate');
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
  if (!empty($daoSource->kursteilnahme_84)) {
    $insertIndex++;
    $insertFields[] = 'kursteilnahme_84';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->kursteilnahme_84, 'String');
  }
  if (!empty($daoSource->aufgaben_im_akademie_team_437)) {
    $insertIndex++;
    $insertFields[] = 'aufgaben_im_akademie_team_437';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aufgaben_im_akademie_team_437, 'String');
  }
  if (!empty($daoSource->gewichtung_anzeige_teamseite_438)) {
    $insertIndex++;
    $insertFields[] = 'gewichtung_anzeige_teamseite_438';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->gewichtung_anzeige_teamseite_438, 'Integer');
  }
  if (!empty($daoSource->funktion_en__454)) {
    $insertIndex++;
    $insertFields[] = 'funktion_en__454';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->funktion_en__454, 'String');
  }
  if (!empty($daoSource->aufgaben_im_akademie_team_en__455)) {
    $insertIndex++;
    $insertFields[] = 'aufgaben_im_akademie_team_en__455';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aufgaben_im_akademie_team_en__455, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
