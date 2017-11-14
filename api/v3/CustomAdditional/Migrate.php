<?php

/**
 * CustomAdditional.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_additional_Migrate($params) {
  $civiTable = "civicrm_value_additional_preferences_2";
  $fzfdTable = "forumzfd_value_additional_preferences_2";
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
  $logger = new CRM_Migration_Logger('additional_preferences_2');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND kontaktwunsch_12 IS NOT NULL LIMIT 5000";
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
      $insertQuery = 'INSERT INTO ' . $civiTable . ' (entity_id, kontaktwunsch_12) VALUES(%1, %2)';
      CRM_Core_DAO::executeQuery($insertQuery, array(
        1 => array($daoSource->entity_id, 'Integer'),
        2 => array($daoSource->kontaktwunsch_12, 'String'),
      ));
    } else {
      $logger->logMessage('Error', 'No contact '.$daoSource->entity_id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  if ($daoSource->N == 0) {
    $returnValues[] = 'All additional preferences custom data for contacts migrated to CiviCRM';
  } else {
    $returnValues[] = $countCreated.' additional preferences custom data for contacts migrated to CiviCRM, '.$countLogged.' logged with errors. More runs required';
  }
  return civicrm_api3_create_success($returnValues, $params, 'CustomAdditional', 'Migrate');
}
