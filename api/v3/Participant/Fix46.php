<?php

function civicrm_api3_participant_Fix46($params) {
  $civiTable = "civicrm_value_zusatzinfos_weiterbildung_berufsbegleitend_46";
  $fzfdTable = "forumzfd_value_zusatzinfos_weiterbildung_berufsbegleitend_46";
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
      if ($daoGroupDefinition->extends_entity_column_id == 3) {
        _fixExtendValues($civiTable);
      }
    }
  }
  $logger = new CRM_Migration_Logger('zusatz_46');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (aktueller_arbeitgeber_348 IS NOT NULL OR 
    aufgabenbereich_349 IS NOT NULL OR beruflicher_werdegang_erfahrunge_350 IS NOT NULL OR 
    bisherige_ausbildung_351 IS NOT NULL OR andere_qualifikationen_352 IS NOT NULL OR 
    erfahrung_email_353 IS NOT NULL OR erfahrung_textverarbeitung_354 IS NOT NULL OR erfahrung_suchmaschinen_355 IS NOT NULL OR
    erfahrung_audiokonferenz_356 IS NOT NULL OR erfahrung_foren_357 IS NOT NULL OR
    erfahrung_textchat_358 IS NOT NULL OR erfahrung_blogs_359 IS NOT NULL OR
    erfahrung_wikis_360 IS NOT NULL OR erfahrung_lernplattformen_361 IS NOT NULL OR erfahrung_virtuelles_klassenzimm_362 IS NOT NULL OR 
    eigener_laptop_363 IS NOT NULL OR englischkenntnisse_364 IS NOT NULL OR 
    motivation_365 IS NOT NULL OR berufliche_zukunft_366 IS NOT NULL OR sonstige_bemerkungen_367 IS NOT NULL OR 
    wie_haben_sie_von_unseren_angebo_469 IS NOT NULL)";
  $daoSource = CRM_Core_DAO::executeQuery($querySource);
  while ($daoSource->fetch()) {
    // update is_processed
    $updateQuery = 'UPDATE '.$fzfdTable.' SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    // get new event id
    $daoSource->entity_id =  CRM_Core_DAO::singleValueQuery('SELECT new_participant_id FROM forumzfd_participant WHERE id = '.$daoSource->entity_id);
    $participantCount = civicrm_api3('Participant', 'getcount', array(
      'id' => $daoSource->entity_id,
    ));
    if ($participantCount == 1) {
      $countCreated++;
      $insertQuery = NULL;
      $insertParams = array();
      _createQueryAndParams($civiTable, $daoSource, $insertQuery, $insertParams);
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    } else {
      $logger->logMessage('Error', 'No participant for source record '.$daoSource->id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  $returnValues[] = 'All zusatz 46 custom data for participants migrated to CiviCRM';
  return civicrm_api3_create_success($returnValues, $params, 'Participant', 'Fix46');
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
  if (!empty($daoSource->aktueller_arbeitgeber_348)) {
    $insertIndex++;
    $insertFields[] = 'aktueller_arbeitgeber_348';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aktueller_arbeitgeber_348, 'String');
  }
  if (!empty($daoSource->aufgabenbereich_349)) {
    $insertIndex++;
    $insertFields[] = 'aufgabenbereich_349';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aufgabenbereich_349, 'String');
  }
  if (!empty($daoSource->beruflicher_werdegang_erfahrunge_350)) {
    $insertIndex++;
    $insertFields[] = 'beruflicher_werdegang_erfahrunge_350';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beruflicher_werdegang_erfahrunge_350, 'String');
  }
  if (!empty($daoSource->bisherige_ausbildung_351)) {
    $insertIndex++;
    $insertFields[] = 'bisherige_ausbildung_351';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bisherige_ausbildung_351, 'String');
  }
  if (!empty($daoSource->andere_qualifikationen_352)) {
    $insertIndex++;
    $insertFields[] = 'andere_qualifikationen_352';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->andere_qualifikationen_352, 'String');
  }
  if (!empty($daoSource->erfahrung_email_353)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_email_353';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_email_353, 'String');
  }
  if (!empty($daoSource->erfahrung_textverarbeitung_354)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_textverarbeitung_354';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_textverarbeitung_354, 'String');
  }
  if (!empty($daoSource->erfahrung_suchmaschinen_355)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_suchmaschinen_355';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_suchmaschinen_355, 'String');
  }
  if (!empty($daoSource->erfahrung_audiokonferenz_356)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_audiokonferenz_356';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_audiokonferenz_356, 'String');
  }
  if (!empty($daoSource->erfahrung_foren_357)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_foren_357';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_foren_357, 'String');
  }
  if (!empty($daoSource->erfahrung_textchat_358)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_textchat_358';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_textchat_358, 'String');
  }
  if (!empty($daoSource->erfahrung_blogs_359)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_blogs_359';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_blogs_359, 'String');
  }
  if (!empty($daoSource->erfahrung_wikis_360)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_wikis_360';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_wikis_360, 'String');
  }
  if (!empty($daoSource->erfahrung_lernplattformen_361)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_lernplattformen_361';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_lernplattformen_361, 'String');
  }
  if (!empty($daoSource->erfahrung_virtuelles_klassenzimm_362)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_virtuelles_klassenzimm_362';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_virtuelles_klassenzimm_362, 'String');
  }
  if (!empty($daoSource->eigener_laptop_363)) {
    $insertIndex++;
    $insertFields[] = 'eigener_laptop_363';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->eigener_laptop_363, 'String');
  }
  if (!empty($daoSource->englischkenntnisse_364)) {
    $insertIndex++;
    $insertFields[] = 'englischkenntnisse_364';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->englischkenntnisse_364, 'String');
  }
  if (!empty($daoSource->motivation_365)) {
    $insertIndex++;
    $insertFields[] = 'motivation_365';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->motivation_365, 'String');
  }
  if (!empty($daoSource->berufliche_zukunft_366)) {
    $insertIndex++;
    $insertFields[] = 'berufliche_zukunft_366';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->berufliche_zukunft_366, 'String');
  }
  if (!empty($daoSource->sonstige_bemerkungen_367)) {
    $insertIndex++;
    $insertFields[] = 'sonstige_bemerkungen_367';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->sonstige_bemerkungen_367, 'String');
  }
  if (!empty($daoSource->wie_haben_sie_von_unseren_angebo_469)) {
    $insertIndex++;
    $insertFields[] = 'wie_haben_sie_von_unseren_angebo_469';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->wie_haben_sie_von_unseren_angebo_469, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}

/**
 * Method to fix the extends_entity_column_value
 *
 * @param string $civiTable
 */
function _fixExtendValues($civiTable) {
  $newTypeId = CRM_Core_DAO::singleValueQuery('SELECT value FROM civicrm_option_value WHERE option_group_id = 15 
    AND name = "weiterbildung_berufsbegleitend"');
  $newExtend = CRM_Core_DAO::VALUE_SEPARATOR.$newTypeId.CRM_Core_DAO::VALUE_SEPARATOR;
  $updateExtends = 'UPDATE civicrm_custom_group SET extends_entity_column_id = %1, extends_entity_column_value = %2 WHERE table_name = %3';
  CRM_Core_DAO::executeQuery($updateExtends, array(
    1 => array(3, 'Integer'),
    2 => array($newExtend, 'String'),
    3 => array($civiTable, 'String'),
  ));
}
