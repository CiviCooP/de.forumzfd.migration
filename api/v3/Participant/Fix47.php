<?php

function civicrm_api3_participant_Fix47($params) {
  $civiTable = "civicrm_value_zusatzinfos_weiterbildung_vollzeit_47";
  $fzfdTable = "forumzfd_value_zusatzinfos_weiterbildung_vollzeit_47";
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
  $logger = new CRM_Migration_Logger('zusatz_47');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (hoechster_abschluss_368 IS NOT NULL OR 
    ausbildung_1_369 IS NOT NULL OR zeit_ort_ausbildung_1_370 IS NOT NULL OR einrichtung_abschluss_1_371 IS NOT NULL OR 
    ausbildung_2_372 IS NOT NULL OR zeit_ort_ausbildung_2_373 IS NOT NULL OR einrichtung_abschluss_2_374 IS NOT NULL OR 
    ausbildung_3_375 IS NOT NULL OR zeit_ort_ausbildung_3_376 IS NOT NULL OR einrichtung_abschluss_3_377 IS NOT NULL OR 
    weitere_erfahrungen_378 IS NOT NULL OR erlernter_beruf_379 IS NOT NULL OR aktueller_arbeitgeber_380 IS NOT NULL OR 
    aufgabenbereich_381 IS NOT NULL OR berufserfahrung_1_382 IS NOT NULL OR zeit_ort_berufserfahrung_1_383 IS NOT NULL OR 
    beschreibung_berufserfahrung_1_384 IS NOT NULL OR berufserfahrung_2_385 IS NOT NULL OR zeit_ort_berufserfahrung_2_386 IS NOT NULL OR 
    beschreibung_berufserfahrung_2_387 IS NOT NULL OR berufserfahrung_3_388 IS NOT NULL OR zeit_ort_berufserfahrung_3_389 IS NOT NULL OR 
    beschreibung_berufserfahrung_3_390 IS NOT NULL OR ehrenamt_1_391 IS NOT NULL OR zeit_ort_ehrenamt_1_392 IS NOT NULL OR  
    beschreibung_ehrenamt_1_393 IS NOT NULL OR ehrenamt_2_394 IS NOT NULL OR zeit_ort_ehrenamt_2_395 IS NOT NULL OR 
    beschreibung_ehrenamt_2_396 IS NOT NULL OR ehrenamt_3_397 IS NOT NULL OR zeit_ort_ehrenamt_3_398 IS NOT NULL OR 
    beschreibung_ehrenamt_3_399 IS NOT NULL OR erfahrungen_inwiefern_relevant_400 IS NOT NULL OR dauer_arbeit_krisengebiete_401 IS NOT NULL OR 
    muttersprache_402 IS NOT NULL OR zweite_muttersprache_403 IS NOT NULL OR erste_fremdsprache_404 IS NOT NULL OR 
    erste_fremdsprache_sprachlevel_s_405 IS NOT NULL OR erste_fremdsprache_sprachlevel_m_406 IS NOT NULL OR zweite_fremdsprache_407 IS NOT NULL OR 
    zweite_fremdsprache_sprachlevel__408 IS NOT NULL OR zweite_fremdsprache_sprachlevel__409 IS NOT NULL OR dritte_fremdsprache_410 IS NOT NULL OR 
    dritte_fremdsprache_sprachlevel__411 IS NOT NULL OR dritte_fremdsprache_sprachlevel__412 IS NOT NULL OR vierte_fremdsprache_413 IS NOT NULL OR 
    vierte_fremdsprache_sprachlevel__414 IS NOT NULL OR vierte_fremdsprache_sprachlevel__415 IS NOT NULL OR bewerbung_nur_grundlagenkurs_416 IS NOT NULL OR 
    motivation_417 IS NOT NULL OR andere_auslandsaufenthalte_418 IS NOT NULL OR im_auftrag_von_organisation_419 IS NOT NULL OR
    welche_organisation_420 IS NOT NULL OR andere_erfahrungen_421 IS NOT NULL OR teilnahme_orientierungstag_422 IS NOT NULL OR 
    welcher_orientierungstag_423 IS NOT NULL OR kursfinanzierung_424 IS NOT NULL OR berufsperspektiven_425 IS NOT NULL OR 
    sonstige_bemerkungen_426 IS NOT NULL OR wie_von_angebot_erfahren_427 IS NOT NULL OR familienstand_428 IS NOT NULL OR 
    zahl_kinder_429 IS NOT NULL)";
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
  $returnValues[] = 'All zusatz 47 custom data for participants migrated to CiviCRM';
  return civicrm_api3_create_success($returnValues, $params, 'Participant', 'Fix47');
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
  if (!empty($daoSource->hoechster_abschluss_368)) {
    $insertIndex++;
    $insertFields[] = 'hoechster_abschluss_368';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->hoechster_abschluss_368, 'String');
  }
  if (!empty($daoSource->ausbildung_1_369)) {
    $insertIndex++;
    $insertFields[] = 'ausbildung_1_369';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ausbildung_1_369, 'String');
  }
  if (!empty($daoSource->zeit_ort_ausbildung_1_370)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ausbildung_1_370';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ausbildung_1_370, 'String');
  }
  if (!empty($daoSource->einrichtung_abschluss_1_371)) {
    $insertIndex++;
    $insertFields[] = 'einrichtung_abschluss_1_371';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->einrichtung_abschluss_1_371, 'String');
  }
  if (!empty($daoSource->ausbildung_2_372)) {
    $insertIndex++;
    $insertFields[] = 'ausbildung_2_372';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ausbildung_2_372, 'String');
  }
  if (!empty($daoSource->zeit_ort_ausbildung_2_373)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ausbildung_2_373';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ausbildung_2_373, 'String');
  }
  if (!empty($daoSource->einrichtung_abschluss_2_374)) {
    $insertIndex++;
    $insertFields[] = 'einrichtung_abschluss_2_374';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->einrichtung_abschluss_2_374, 'String');
  }
  if (!empty($daoSource->ausbildung_3_375)) {
    $insertIndex++;
    $insertFields[] = 'ausbildung_3_375';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ausbildung_3_375, 'String');
  }
  if (!empty($daoSource->zeit_ort_ausbildung_3_376)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ausbildung_3_376';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ausbildung_3_376, 'String');
  }
  if (!empty($daoSource->einrichtung_abschluss_3_377)) {
    $insertIndex++;
    $insertFields[] = 'einrichtung_abschluss_3_377';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->einrichtung_abschluss_3_377, 'String');
  }
  if (!empty($daoSource->weitere_erfahrungen_378)) {
    $insertIndex++;
    $insertFields[] = 'weitere_erfahrungen_378';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->weitere_erfahrungen_378, 'String');
  }
  if (!empty($daoSource->erlernter_beruf_379)) {
    $insertIndex++;
    $insertFields[] = 'erlernter_beruf_379';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erlernter_beruf_379, 'String');
  }
  if (!empty($daoSource->aktueller_arbeitgeber_380)) {
    $insertIndex++;
    $insertFields[] = 'aktueller_arbeitgeber_380';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aktueller_arbeitgeber_380, 'String');
  }
  if (!empty($daoSource->aufgabenbereich_381)) {
    $insertIndex++;
    $insertFields[] = 'aufgabenbereich_381';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->aufgabenbereich_381, 'String');
  }
  if (!empty($daoSource->berufserfahrung_1_382)) {
    $insertIndex++;
    $insertFields[] = 'berufserfahrung_1_382';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->berufserfahrung_1_382, 'String');
  }
  if (!empty($daoSource->zeit_ort_berufserfahrung_1_383)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_berufserfahrung_1_383';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_berufserfahrung_1_383, 'String');
  }
  if (!empty($daoSource->beschreibung_berufserfahrung_1_384)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_berufserfahrung_1_384';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_berufserfahrung_1_384, 'String');
  }
  if (!empty($daoSource->berufserfahrung_2_385)) {
    $insertIndex++;
    $insertFields[] = 'berufserfahrung_2_385';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->berufserfahrung_2_385, 'String');
  }
  if (!empty($daoSource->zeit_ort_berufserfahrung_2_386)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_berufserfahrung_2_386';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_berufserfahrung_2_386, 'String');
  }
  if (!empty($daoSource->beschreibung_berufserfahrung_2_387)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_berufserfahrung_2_387';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_berufserfahrung_2_387, 'String');
  }
  if (!empty($daoSource->berufserfahrung_3_388)) {
    $insertIndex++;
    $insertFields[] = 'berufserfahrung_3_388';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->berufserfahrung_3_388, 'String');
  }
  if (!empty($daoSource->zeit_ort_berufserfahrung_3_389)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_berufserfahrung_3_389';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_berufserfahrung_3_389, 'String');
  }
  if (!empty($daoSource->beschreibung_berufserfahrung_3_390)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_berufserfahrung_3_390';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_berufserfahrung_3_390, 'String');
  }
  if (!empty($daoSource->ehrenamt_1_391)) {
    $insertIndex++;
    $insertFields[] = 'ehrenamt_1_391';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ehrenamt_1_391, 'String');
  }
  if (!empty($daoSource->zeit_ort_ehrenamt_1_392)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ehrenamt_1_392';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ehrenamt_1_392, 'String');
  }
  if (!empty($daoSource->beschreibung_ehrenamt_1_393)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_ehrenamt_1_393';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_ehrenamt_1_393, 'String');
  }
  if (!empty($daoSource->ehrenamt_2_394)) {
    $insertIndex++;
    $insertFields[] = 'ehrenamt_2_394';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ehrenamt_2_394, 'String');
  }
  if (!empty($daoSource->zeit_ort_ehrenamt_2_395)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ehrenamt_2_395';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ehrenamt_2_395, 'String');
  }
  if (!empty($daoSource->beschreibung_ehrenamt_2_396)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_ehrenamt_2_396';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_ehrenamt_2_396, 'String');
  }
  if (!empty($daoSource->ehrenamt_3_397)) {
    $insertIndex++;
    $insertFields[] = 'ehrenamt_3_397';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ehrenamt_3_397, 'String');
  }
  if (!empty($daoSource->zeit_ort_ehrenamt_3_398)) {
    $insertIndex++;
    $insertFields[] = 'zeit_ort_ehrenamt_3_398';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zeit_ort_ehrenamt_3_398, 'String');
  }
  if (!empty($daoSource->beschreibung_ehrenamt_3_399)) {
    $insertIndex++;
    $insertFields[] = 'beschreibung_ehrenamt_3_399';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->beschreibung_ehrenamt_3_399, 'String');
  }
  if (!empty($daoSource->erfahrungen_inwiefern_relevant_400)) {
    $insertIndex++;
    $insertFields[] = 'erfahrungen_inwiefern_relevant_400';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrungen_inwiefern_relevant_400, 'String');
  }
  if (!empty($daoSource->dauer_arbeit_krisengebiete_401)) {
    $insertIndex++;
    $insertFields[] = 'dauer_arbeit_krisengebiete_401';
    $insertValues[] = '%' . $insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dauer_arbeit_krisengebiete_401, 'String');
  }
  if (!empty($daoSource->muttersprache_402)) {
    $insertIndex++;
    $insertFields[] = 'muttersprache_402';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->muttersprache_402, 'String');
  }
  if (!empty($daoSource->muttersprache_402)) {
    $insertIndex++;
    $insertFields[] = 'muttersprache_402';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->muttersprache_402, 'String');
  }
  if (!empty($daoSource->zweite_muttersprache_403)) {
    $insertIndex++;
    $insertFields[] = 'zweite_muttersprache_403';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zweite_muttersprache_403, 'String');
  }
  if (!empty($daoSource->erste_fremdsprache_404)) {
    $insertIndex++;
    $insertFields[] = 'erste_fremdsprache_404';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erste_fremdsprache_404, 'String');
  }
  if (!empty($daoSource->erste_fremdsprache_sprachlevel_s_405)) {
    $insertIndex++;
    $insertFields[] = 'erste_fremdsprache_sprachlevel_s_405';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erste_fremdsprache_sprachlevel_s_405, 'String');
  }
  if (!empty($daoSource->erste_fremdsprache_sprachlevel_m_406)) {
    $insertIndex++;
    $insertFields[] = 'erste_fremdsprache_sprachlevel_m_406';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erste_fremdsprache_sprachlevel_m_406, 'String');
  }
  if (!empty($daoSource->zweite_fremdsprache_407)) {
    $insertIndex++;
    $insertFields[] = 'zweite_fremdsprache_407';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zweite_fremdsprache_407, 'String');
  }
  if (!empty($daoSource->zweite_fremdsprache_sprachlevel__408)) {
    $insertIndex++;
    $insertFields[] = 'zweite_fremdsprache_sprachlevel__408';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zweite_fremdsprache_sprachlevel__408, 'String');
  }
  if (!empty($daoSource->zweite_fremdsprache_sprachlevel__409)) {
    $insertIndex++;
    $insertFields[] = 'zweite_fremdsprache_sprachlevel__409';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zweite_fremdsprache_sprachlevel__409, 'String');
  }
  if (!empty($daoSource->dritte_fremdsprache_410)) {
    $insertIndex++;
    $insertFields[] = 'dritte_fremdsprache_410';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dritte_fremdsprache_410, 'String');
  }
  if (!empty($daoSource->dritte_fremdsprache_sprachlevel__411)) {
    $insertIndex++;
    $insertFields[] = 'dritte_fremdsprache_sprachlevel__411';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dritte_fremdsprache_sprachlevel__411, 'String');
  }
  if (!empty($daoSource->dritte_fremdsprache_sprachlevel__412)) {
    $insertIndex++;
    $insertFields[] = 'dritte_fremdsprache_sprachlevel__412';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->dritte_fremdsprache_sprachlevel__412, 'String');
  }
  if (!empty($daoSource->vierte_fremdsprache_413)) {
    $insertIndex++;
    $insertFields[] = 'vierte_fremdsprache_413';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vierte_fremdsprache_413, 'String');
  }
  if (!empty($daoSource->vierte_fremdsprache_sprachlevel__414)) {
    $insertIndex++;
    $insertFields[] = 'vierte_fremdsprache_sprachlevel__414';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vierte_fremdsprache_sprachlevel__414, 'String');
  }
  if (!empty($daoSource->vierte_fremdsprache_sprachlevel__415)) {
    $insertIndex++;
    $insertFields[] = 'vierte_fremdsprache_sprachlevel__415';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vierte_fremdsprache_sprachlevel__415, 'String');
  }
  if (!empty($daoSource->bewerbung_nur_grundlagenkurs_416)) {
    $insertIndex++;
    $insertFields[] = 'bewerbung_nur_grundlagenkurs_416';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bewerbung_nur_grundlagenkurs_416, 'Integer');
  }
  if (!empty($daoSource->motivation_417)) {
    $insertIndex++;
    $insertFields[] = 'motivation_417';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->motivation_417, 'String');
  }
  if (!empty($daoSource->andere_auslandsaufenthalte_418)) {
    $insertIndex++;
    $insertFields[] = 'andere_auslandsaufenthalte_418';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->andere_auslandsaufenthalte_418, 'String');
  }
  if (!empty($daoSource->im_auftrag_von_organisation_419)) {
    $insertIndex++;
    $insertFields[] = 'im_auftrag_von_organisation_419';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->im_auftrag_von_organisation_419, 'Integer');
  }
  if (!empty($daoSource->welche_organisation_420)) {
    $insertIndex++;
    $insertFields[] = 'welche_organisation_420';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->welche_organisation_420, 'String');
  }
  if (!empty($daoSource->andere_erfahrungen_421)) {
    $insertIndex++;
    $insertFields[] = 'andere_erfahrungen_421';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->andere_erfahrungen_421, 'String');
  }
  if (!empty($daoSource->teilnahme_orientierungstag_422)) {
    $insertIndex++;
    $insertFields[] = 'teilnahme_orientierungstag_422';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->teilnahme_orientierungstag_422, 'Integer');
  }
  if (!empty($daoSource->welcher_orientierungstag_423)) {
    $insertIndex++;
    $insertFields[] = 'welcher_orientierungstag_423';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->welcher_orientierungstag_423, 'String');
  }
  if (!empty($daoSource->kursfinanzierung_424)) {
    $insertIndex++;
    $insertFields[] = 'kursfinanzierung_424';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->kursfinanzierung_424, 'String');
  }
  if (!empty($daoSource->berufsperspektiven_425)) {
    $insertIndex++;
    $insertFields[] = 'berufsperspektiven_425';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->berufsperspektiven_425, 'String');
  }
  if (!empty($daoSource->sonstige_bemerkungen_426)) {
    $insertIndex++;
    $insertFields[] = 'sonstige_bemerkungen_426';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->sonstige_bemerkungen_426, 'String');
  }
  if (!empty($daoSource->wie_von_angebot_erfahren_427)) {
    $insertIndex++;
    $insertFields[] = 'wie_von_angebot_erfahren_427';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->wie_von_angebot_erfahren_427, 'String');
  }
  if (!empty($daoSource->familienstand_428)) {
    $insertIndex++;
    $insertFields[] = 'familienstand_428';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->familienstand_428, 'String');
  }
  if (!empty($daoSource->zahl_kinder_429)) {
    $insertIndex++;
    $insertFields[] = 'zahl_kinder_429';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zahl_kinder_429, 'String');
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
    AND name = "weiterbildung_vollzeit"');
  $newExtend = CRM_Core_DAO::VALUE_SEPARATOR.$newTypeId.CRM_Core_DAO::VALUE_SEPARATOR;
  $updateExtends = 'UPDATE civicrm_custom_group SET extends_entity_column_id = %1, extends_entity_column_value = %2 WHERE table_name = %3';
  CRM_Core_DAO::executeQuery($updateExtends, array(
    1 => array(3, 'Integer'),
    2 => array($newExtend, 'String'),
    3 => array($civiTable, 'String'),
  ));
}
