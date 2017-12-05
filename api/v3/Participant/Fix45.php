<?php

function civicrm_api3_participant_Fix45($params) {
  $civiTable = "civicrm_value_zusatzinfos_seminaranmeldung_45";
  $fzfdTable = "forumzfd_value_zusatzinfos_seminaranmeldung_45";
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
  $logger = new CRM_Migration_Logger('zusatz_45');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (erfahrung_330 IS NOT NULL OR 
    erwartungen_331 IS NOT NULL OR von_organisation_entsendet_332 IS NOT NULL OR 
    entsendeorganisation_konsortium_333 IS NOT NULL OR entsendeorganisation_334 IS NOT NULL OR 
    einsatzland_335 IS NOT NULL OR position_projekt_336 IS NOT NULL OR lokaler_internationaler_ma_337 IS NOT NULL OR
    unterkunft_benoetigt_338 IS NOT NULL OR erste_mahlzeit_anreisetag_339 IS NOT NULL OR
    letzte_mahlzeit_abreisetag_340 IS NOT NULL OR ankunftszeit_alt_341 IS NOT NULL OR
    ankunftszeit_342 IS NOT NULL OR besondere_wuensche_343 IS NOT NULL OR agb_akzeptiert_344 IS NOT NULL OR 
    sonstige_bemerkungen_345 IS NOT NULL OR woher_kennen_sie_die_akademie_346 IS NOT NULL OR 
    zahlungeingang_347 IS NOT NULL OR abreisedatum_453 IS NOT NULL OR bezuschussung_beantragt_467 IS NOT NULL)";
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
  $returnValues[] = 'All zusatz 45 custom data for participants migrated to CiviCRM';
  return civicrm_api3_create_success($returnValues, $params, 'Participant', 'Fix45');
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
  if (!empty($daoSource->erfahrung_330)) {
    $insertIndex++;
    $insertFields[] = 'erfahrung_330';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erfahrung_330, 'String');
  }
  if (!empty($daoSource->erwartungen_331)) {
    $insertIndex++;
    $insertFields[] = 'erwartungen_331';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erwartungen_331, 'String');
  }
  if (!empty($daoSource->von_organisation_entsendet_332)) {
    $insertIndex++;
    $insertFields[] = 'von_organisation_entsendet_332';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->von_organisation_entsendet_332, 'Integer');
  }
  if (!empty($daoSource->entsendeorganisation_konsortium_333)) {
    $insertIndex++;
    $insertFields[] = 'entsendeorganisation_konsortium_333';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->entsendeorganisation_konsortium_333, 'String');
  }
  if (!empty($daoSource->entsendeorganisation_334)) {
    $insertIndex++;
    $insertFields[] = 'entsendeorganisation_334';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->entsendeorganisation_334, 'String');
  }
  if (!empty($daoSource->einsatzland_335)) {
    $insertIndex++;
    $insertFields[] = 'einsatzland_335';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->einsatzland_335, 'String');
  }
  if (!empty($daoSource->position_projekt_336)) {
    $insertIndex++;
    $insertFields[] = 'position_projekt_336';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->position_projekt_336, 'String');
  }
  if (!empty($daoSource->lokaler_internationaler_ma_337)) {
    $insertIndex++;
    $insertFields[] = 'lokaler_internationaler_ma_337';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->lokaler_internationaler_ma_337, 'String');
  }
  if (!empty($daoSource->unterkunft_benoetigt_338)) {
    $insertIndex++;
    $insertFields[] = 'unterkunft_benoetigt_338';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->unterkunft_benoetigt_338, 'Integer');
  }
  if (!empty($daoSource->erste_mahlzeit_anreisetag_339)) {
    $insertIndex++;
    $insertFields[] = 'erste_mahlzeit_anreisetag_339';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->erste_mahlzeit_anreisetag_339, 'Integer');
  }
  if (!empty($daoSource->letzte_mahlzeit_abreisetag_340)) {
    $insertIndex++;
    $insertFields[] = 'letzte_mahlzeit_abreisetag_340';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->letzte_mahlzeit_abreisetag_340, 'Integer');
  }
  if (!empty($daoSource->ankunftszeit_alt_341)) {
    $insertIndex++;
    $insertFields[] = 'ankunftszeit_alt_341';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ankunftszeit_alt_341, 'String');
  }
  if (!empty($daoSource->ankunftszeit_342)) {
    $insertIndex++;
    $insertFields[] = 'ankunftszeit_342';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ankunftszeit_342, 'String');
  }
  if (!empty($daoSource->besondere_wuensche_343)) {
    $insertIndex++;
    $insertFields[] = 'besondere_wuensche_343';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->besondere_wuensche_343, 'String');
  }
  if (!empty($daoSource->agb_akzeptiert_344)) {
    $insertIndex++;
    $insertFields[] = 'agb_akzeptiert_344';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->agb_akzeptiert_344, 'String');
  }
  if (!empty($daoSource->sonstige_bemerkungen_345)) {
    $insertIndex++;
    $insertFields[] = 'sonstige_bemerkungen_345';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->sonstige_bemerkungen_345, 'String');
  }
  if (!empty($daoSource->woher_kennen_sie_die_akademie_346)) {
    $insertIndex++;
    $insertFields[] = 'woher_kennen_sie_die_akademie_346';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->woher_kennen_sie_die_akademie_346, 'String');
  }
  if (!empty($daoSource->zahlungeingang_347)) {
    $insertIndex++;
    $insertFields[] = 'zahlungeingang_347';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zahlungeingang_347, 'String');
  }
  if (!empty($daoSource->abreisedatum_453)) {
    $insertIndex++;
    $insertFields[] = 'abreisedatum_453';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->abreisedatum_453, 'String');
  }
  if (!empty($daoSource->bezuschussung_beantragt_467)) {
    $insertIndex++;
    $insertFields[] = 'bezuschussung_beantragt_467';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bezuschussung_beantragt_467, 'Integer');
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
  $newOnlineSeminarId = CRM_Core_DAO::singleValueQuery('SELECT value FROM civicrm_option_value WHERE option_group_id = 15 
    AND name = "Online-seminar"');
  $newSeminarId = CRM_Core_DAO::singleValueQuery('SELECT value FROM civicrm_option_value WHERE option_group_id = 15 
    AND name = "seminar"');
  $newExtend = CRM_Core_DAO::VALUE_SEPARATOR.$newSeminarId.CRM_Core_DAO::VALUE_SEPARATOR.$newOnlineSeminarId.CRM_Core_DAO::VALUE_SEPARATOR;
  $updateExtends = 'UPDATE civicrm_custom_group SET extends_entity_column_id = %1, extends_entity_column_value = %2 WHERE table_name = %3';
  CRM_Core_DAO::executeQuery($updateExtends, array(
    1 => array(3, 'Integer'),
    2 => array($newExtend, 'String'),
    3 => array($civiTable, 'String'),
  ));
}
