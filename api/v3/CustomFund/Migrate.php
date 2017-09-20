<?php

/**
 * CustomFund.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_fund_Migrate($params) {
  $civiTable = "civicrm_value_fundraising_10";
  $fzfdTable = "forumzfd_value_fundraising_10";
  set_time_limit(0);
  $countCreated = 0;
  $countLogged = 0;
  $returnValues = array();
  //first create custom group if it does not exist yetforumzfd_civicrm.forumzfd_value_fundraising_10.status_kontakt_74
  if (!CRM_Core_DAO::checkTableExists($civiTable)) {
    $queryGroupDefinition = 'SELECT * FROM forumzfd_custom_group WHERE table_name = %1 ';
    $daoGroupDefinition = CRM_Core_DAO::executeQuery($queryGroupDefinition,
      array(1 => array($civiTable, 'String')));
    if ($daoGroupDefinition->fetch()) {
      CRM_Migration_ContactCustomData::createContactCustomGroup($daoGroupDefinition);
    }
  }
  $logger = new CRM_Migration_Logger('fundraising_10');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (versand_quittungen_59 IS NOT NULL OR 
    studienreise_interesse_70 IS NOT NULL OR zahlungsweise_71 IS NOT NULL OR kampagne402010_72 IS NOT NULL OR 
    vermaechtnisdarlehen_73 IS NOT NULL OR status_kontakt_74 IS NOT NULL OR 
    studienreise_teilnahme_75 IS NOT NULL OR telefonaktion_2006_86 IS NOT NULL OR 
    telefonaktion_2007_87 IS NOT NULL OR telefonaktion_2008_97 IS NOT NULL OR 
    endsumme_erh_hung_f_rderbeitrag__98 IS NOT NULL OR telefonaktion_2010_111 IS NOT NULL OR 
    endsumme_erh_hung_f_rderbeitrag__112 IS NOT NULL) LIMIT 5000";
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
    $returnValues[] = 'All fundraising custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' fundraising custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomFund', 'Migrate');
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
  if (!empty($daoSource->versand_quittungen_59)) {
    $insertIndex++;
    $insertFields[] = 'versand_quittungen_59';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->versand_quittungen_59, 'String');
  }
  if (!empty($daoSource->studienreise_interesse_70)) {
    $insertIndex++;
    $insertFields[] = 'studienreise_interesse_70';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->studienreise_interesse_70, 'String');
  }
  if (!empty($daoSource->zahlungsweise_71)) {
    $insertIndex++;
    $insertFields[] = 'zahlungsweise_71';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->zahlungsweise_71, 'String');
  }
  if (!empty($daoSource->kampagne402010_72)) {
    $insertIndex++;
    $insertFields[] = 'kampagne402010_72';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->kampagne402010_72, 'String');
  }
  if (!empty($daoSource->vermaechtnisdarlehen_73)) {
    $insertIndex++;
    $insertFields[] = 'vermaechtnisdarlehen_73';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->vermaechtnisdarlehen_73, 'String');
  }
  if (!empty($daoSource->status_kontakt_74)) {
    $insertIndex++;
    $insertFields[] = 'status_kontakt_74';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->status_kontakt_74, 'String');
  }
  if (!empty($daoSource->studienreise_teilnahme_75)) {
    $insertIndex++;
    $insertFields[] = 'studienreise_teilnahme_75';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->studienreise_teilnahme_75, 'String');
  }
  if (!empty($daoSource->telefonaktion_2006_86)) {
    $insertIndex++;
    $insertFields[] = 'telefonaktion_2006_86';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->telefonaktion_2006_86, 'String');
  }
  if (!empty($daoSource->telefonaktion_2007_87)) {
    $insertIndex++;
    $insertFields[] = 'telefonaktion_2007_87';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->telefonaktion_2007_87, 'String');
  }
  if (!empty($daoSource->telefonaktion_2008_97)) {
    $insertIndex++;
    $insertFields[] = 'telefonaktion_2008_97';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->telefonaktion_2008_97, 'String');
  }
  if (!empty($daoSource->endsumme_erh_hung_f_rderbeitrag__98)) {
    $insertIndex++;
    $insertFields[] = 'endsumme_erh_hung_f_rderbeitrag__98';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->endsumme_erh_hung_f_rderbeitrag__98, 'Money');
  }
  if (!empty($daoSource->telefonaktion_2010_111)) {
    $insertIndex++;
    $insertFields[] = 'telefonaktion_2010_111';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->telefonaktion_2010_111, 'String');
  }
  if (!empty($daoSource->endsumme_erh_hung_f_rderbeitrag__112)) {
    $insertIndex++;
    $insertFields[] = 'endsumme_erh_hung_f_rderbeitrag__112';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->endsumme_erh_hung_f_rderbeitrag__112, 'Money');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
