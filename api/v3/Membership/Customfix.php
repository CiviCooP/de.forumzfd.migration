<?php
function civicrm_api3_membership_Customfix($params) {
  $civiTable = "civicrm_value_zahlungsdetails_9";
  $fzfdTable = "forumzfd_value_zahlungsdetails_9";
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
  $logger = new CRM_Migration_Logger('zahlungsdetails_9');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (sollbetrag_46 IS NOT NULL OR 
    einzug_47 IS NOT NULL OR ueberweisung_49 IS NOT NULL OR austrittsdatum_233 IS NOT NULL) LIMIT 5000";
  $daoSource = CRM_Core_DAO::executeQuery($querySource);
  while ($daoSource->fetch()) {
    // update is_processed
    $updateQuery = 'UPDATE '.$fzfdTable.' SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    // get new membership id
    $daoSource->entity_id =  CRM_Core_DAO::singleValueQuery('SELECT new_membership_id FROM forumzfd_membership WHERE id = '.$daoSource->entity_id);
    $membershipCount = civicrm_api3('Membership', 'getcount', array(
      'id' => $daoSource->entity_id,
    ));
    if ($membershipCount == 1) {
      $countCreated++;
      $insertQuery = NULL;
      $insertParams = array();
      _createQueryAndParams($civiTable, $daoSource, $insertQuery, $insertParams);
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    } else {
      $logger->logMessage('Error', 'No membership '.$daoSource->entity_id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  if ($daoSource->N == 0) {
    $returnValues[] = 'All zahlungsdetails_9 custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' zahlungsdetails_9 custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Membership', 'FixCustomData');
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
  if (!empty($daoSource->sollbetrag_46)) {
    $insertIndex++;
    $insertFields[] = 'sollbetrag_46';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->sollbetrag_46, 'Money');
  }
  if (!empty($daoSource->einzug_47)) {
    $insertIndex++;
    $insertFields[] = 'einzug_47';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->einzug_47, 'String');
  }
  if (!empty($daoSource->ueberweisung_49)) {
    $insertIndex++;
    $insertFields[] = 'ueberweisung_49';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->ueberweisung_49, 'String');
  }
  if (!empty($daoSource->austrittsdatum_233)) {
    $insertIndex++;
    $insertFields[] = 'austrittsdatum_233';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->austrittsdatum_233, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}