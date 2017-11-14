<?php

/**
 * CustomTrainer.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_trainer_Migrate($params) {
  $civiTable = "civicrm_value_trainerin_19";
  $fzfdTable = "forumzfd_value_trainerin_19";
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
  $logger = new CRM_Migration_Logger('trainerin');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (themengebiete_120 IS NOT NULL 
    OR funktion_121 IS NOT NULL OR t_tig_in_122 IS NOT NULL OR sprachen_123 IS NOT NULL OR umsatzsteuerpflichtig_126 IS NOT NULL OR 
    regionen_135 IS NOT NULL OR bewerbung_140 IS NOT NULL OR keine_weiter_zusammenarbeit_147 IS NOT NULL OR kommentare_150 IS NOT NULL OR 
    lebenslauf_151 IS NOT NULL OR hat_bereits_f_r_afk_gearbeitet_228 IS NOT NULL OR projekttitel_229 IS NOT NULL OR 
    anzeige_in_traineruebersicht_447 IS NOT NULL OR trainer_hintergrund_448 IS NOT NULL OR fachgebiete_452 IS NOT NULL OR 
    fachgebiete_en__464 IS NOT NULL OR hintergrund_en_neu__466 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All trainerin custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' trainerin data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomTrainer', 'Migrate');
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
  if (!empty($daoSource->themengebiete_120)) {
    $insertIndex++;
    $insertFields[] = 'themengebiete_120';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->themengebiete_120, 'String');
  }
  if (!empty($daoSource->funktion_121)) {
    $insertIndex++;
    $insertFields[] = 'funktion_121';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->funktion_121, 'String');
  }
  if (!empty($daoSource->t_tig_in_122)) {
    $insertIndex++;
    $insertFields[] = 't_tig_in_122';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->t_tig_in_122, 'String');
  }
  if (!empty($daoSource->sprachen_123)) {
    $insertIndex++;
    $insertFields[] = 'sprachen_123';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->sprachen_123, 'String');
  }
  if (!empty($daoSource->umsatzsteuerpflichtig_126)) {
    $insertIndex++;
    $insertFields[] = 'umsatzsteuerpflichtig_126';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->umsatzsteuerpflichtig_126, 'Integer');
  }
  if (!empty($daoSource->regionen_135)) {
    $insertIndex++;
    $insertFields[] = 'regionen_135';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->regionen_135, 'String');
  }
  if (!empty($daoSource->bewerbung_140)) {
    $insertIndex++;
    $insertFields[] = 'bewerbung_140';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->bewerbung_140, 'String');
  }
  if (!empty($daoSource->keine_weiter_zusammenarbeit_147)) {
    $insertIndex++;
    $insertFields[] = 'keine_weiter_zusammenarbeit_147';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->keine_weiter_zusammenarbeit_147, 'String');
  }
  if (!empty($daoSource->kommentare_150)) {
    $insertIndex++;
    $insertFields[] = 'kommentare_150';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->kommentare_150, 'String');
  }
  if (!empty($daoSource->lebenslauf_151)) {
    $insertIndex++;
    $insertFields[] = 'lebenslauf_151';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->lebenslauf_151, 'Integer');
  }
  if (!empty($daoSource->hat_bereits_f_r_afk_gearbeitet_228)) {
    $insertIndex++;
    $insertFields[] = 'hat_bereits_f_r_afk_gearbeitet_228';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->hat_bereits_f_r_afk_gearbeitet_228, 'Integer');
  }
  if (!empty($daoSource->projekttitel_229)) {
    $insertIndex++;
    $insertFields[] = 'projekttitel_229';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->projekttitel_229, 'String');
  }
  if (!empty($daoSource->anzeige_in_traineruebersicht_447)) {
    $insertIndex++;
    $insertFields[] = 'anzeige_in_traineruebersicht_447';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->anzeige_in_traineruebersicht_447, 'Integer');
  }
  if (!empty($daoSource->trainer_hintergrund_448)) {
    $insertIndex++;
    $insertFields[] = 'trainer_hintergrund_448';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->trainer_hintergrund_448, 'String');
  }
  if (!empty($daoSource->fachgebiete_452)) {
    $insertIndex++;
    $insertFields[] = 'fachgebiete_452';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fachgebiete_452, 'String');
  }
  if (!empty($daoSource->fachgebiete_en__464)) {
    $insertIndex++;
    $insertFields[] = 'fachgebiete_en__464';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->fachgebiete_en__464, 'String');
  }
  if (!empty($daoSource->hintergrund_en_neu__466)) {
    $insertIndex++;
    $insertFields[] = 'hintergrund_en_neu__466';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->hintergrund_en_neu__466, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
