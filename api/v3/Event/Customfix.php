<?php

function civicrm_api3_event_Customfix($params) {
  $civiTable = "civicrm_value_zusatzinfos_veranstaltungen_44";
  $fzfdTable = "forumzfd_value_zusatzinfos_veranstaltungen_44";
  set_time_limit(0);
  $countCreated = 0;
  $countLogged = 0;
  $returnValues = array();
  //first create custom group if it does not exist yet
  if (!CRM_Core_DAO::checkTableExists($civiTable)) {
    $queryGroupDefinition = 'SELECT * FROM forumzfd_custom_group WHERE table_name = %1';
    $daoGroupDefinition = CRM_Core_DAO::executeQuery($queryGroupDefinition,
      array(1 => array($civiTable, 'String')));

    if ($daoGroupDefinition->fetch()) {
      CRM_Migration_ContactCustomData::createContactCustomGroup($daoGroupDefinition);
    }
  }
  $logger = new CRM_Migration_Logger('zusatzinfos_veranstaltungen_44');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (veranstaltungssprache_303 IS NOT NULL OR 
    org_ap_312 IS NOT NULL OR inhalt_ap_313 IS NOT NULL OR bewerbungsschluss_325 IS NOT NULL OR 
    tagungsort_326 IS NOT NULL OR preis_449 IS NOT NULL OR fr_hbucherpreis_450 IS NOT NULL 
    OR zusatzkosten_bernachtung_451 IS NOT NULL) LIMIT 5000";
  $daoSource = CRM_Core_DAO::executeQuery($querySource);
  while ($daoSource->fetch()) {
    // update is_processed
    $updateQuery = 'UPDATE '.$fzfdTable.' SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    // get new event id
    $daoSource->entity_id =  CRM_Core_DAO::singleValueQuery('SELECT new_event_id FROM forumzfd_event WHERE id = '.$daoSource->entity_id);
    $eventCount = civicrm_api3('Event', 'getcount', array(
      'id' => $daoSource->entity_id,
    ));
    if ($eventCount == 1) {
      $countCreated++;
      $insertQuery = NULL;
      $insertParams = array();
      _createQueryAndParams($civiTable, $daoSource, $insertQuery, $insertParams);
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    } else {
      $logger->logMessage('Error', 'No event '.$daoSource->entity_id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  if ($daoSource->N == 0) {
    $returnValues[] = 'All zusatz 44 custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' zusatz 44 custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Event', 'FixCustomData');
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
  if (!empty($daoSource->veranstaltungssprache_303)) {
    $insertIndex++;
    $insertFields[] = 'veranstaltungssprache_303';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->veranstaltungssprache_303, 'String');
  }
  if (!empty($daoSource->dozent_in_311)) {
    $insertIndex++;
    $insertFields[] = 'dozent_in_311';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dozent_in_311, 'String');
  }
  if (!empty($daoSource->org_ap_312)) {
    $insertIndex++;
    $insertFields[] = 'org_ap_312';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->org_ap_312, 'String');
  }
  if (!empty($daoSource->inhalt_ap_313)) {
    $insertIndex++;
    $insertFields[] = 'inhalt_ap_313';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->inhalt_ap_313, 'String');
  }
  if (!empty($daoSource->bewerbungsschluss_325)) {
    $insertIndex++;
    $insertFields[] = 'bewerbungsschluss_325';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bewerbungsschluss_325, 'String');
  }
  if (!empty($daoSource->tagungsort_326)) {
    $insertIndex++;
    $insertFields[] = 'tagungsort_326';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->tagungsort_326, 'String');
  }
  if (!empty($daoSource->preis_449)) {
    $insertIndex++;
    $insertFields[] = 'preis_449';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->preis_449, 'String');
  }
  if (!empty($daoSource->fr_hbucherpreis_450)) {
    $insertIndex++;
    $insertFields[] = 'fr_hbucherpreis_450';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fr_hbucherpreis_450, 'String');
  }
  if (!empty($daoSource->zusatzkosten_bernachtung_451)) {
    $insertIndex++;
    $insertFields[] = 'zusatzkosten_bernachtung_451';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zusatzkosten_bernachtung_451, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}

