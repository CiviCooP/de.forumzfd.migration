<?php

function civicrm_api3_activity_Fix55($params) {
  $civiTable = "civicrm_value_werbecodes_55";
  $fzfdTable = "forumzfd_value_werbecodes_55";
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
      if (!empty($daoGroupDefinition->extends_entity_column_value)) {
        _fixExtendValues($civiTable);
      }
    }
  }
  $logger = new CRM_Migration_Logger('werbecodes_55');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0";
  $daoSource = CRM_Core_DAO::executeQuery($querySource);
  while ($daoSource->fetch()) {
    // update is_processed
    $updateQuery = 'UPDATE '.$fzfdTable.' SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));

    // get new activity id
    $daoSource->entity_id =  CRM_Core_DAO::singleValueQuery('SELECT new_activity_id FROM forumzfd_activity WHERE id = '.$daoSource->entity_id);
    $activityCount = civicrm_api3('Activity', 'getcount', array(
      'id' => $daoSource->entity_id,
    ));
    if ($activityCount == 1) {
      $countCreated++;
      $insertQuery = NULL;
      $insertParams = array();
      _createQueryAndParams($civiTable, $daoSource, $insertQuery, $insertParams);
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
    } else {
      $logger->logMessage('Error', 'No activity for source record '.$daoSource->id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  $returnValues[] = 'All werbecodes_55 custom data for activities migrated to CiviCRM';
  return civicrm_api3_create_success($returnValues, $params, 'Activity', 'Fix55');
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
  if (!empty($daoSource->werbe_499)) {
    $insertIndex++;
    $insertFields[] = 'werbe_499';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->werbe_499, 'Integer');
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
  $newActivityTypeId = CRM_Core_DAO::singleValueQuery('SELECT value FROM civicrm_option_value WHERE option_group_id = 2
    AND name = "Spendenmailing erhalten"');
  $newExtend = CRM_Core_DAO::VALUE_SEPARATOR.$newActivityTypeId.CRM_Core_DAO::VALUE_SEPARATOR;
  $updateExtends = 'UPDATE civicrm_custom_group SET extends_entity_column_id = %1, extends_entity_column_value = %2 WHERE table_name = %3';
  CRM_Core_DAO::executeQuery($updateExtends, array(
    1 => array(3, 'Integer'),
    2 => array($newExtend, 'String'),
    3 => array($civiTable, 'String'),
  ));
}
