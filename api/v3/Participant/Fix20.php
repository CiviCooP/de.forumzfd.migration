<?php

function civicrm_api3_participant_Fix20($params) {
  $civiTable = "civicrm_value_further_information_20";
  $fzfdTable = "forumzfd_value_further_information_20";
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
      if ($daoGroupDefinition->extends_entity_column_id == 2) {
        $newEventId = CRM_Core_DAO::singleValueQuery('SELECT new_event_id FROM forumzfd_event WHERE id = 4');
        $updateExtends = 'UPDATE civicrm_custom_group SET extends_entity_column_id = %1, extends_entity_column_value = %2 WHERE table_name = %3';
        CRM_Core_DAO::executeQuery($updateExtends, array(
          1 => array(2, 'Integer'),
          2 => array(CRM_Core_DAO::VALUE_SEPARATOR.$newEventId.CRM_Core_DAO::VALUE_SEPARATOR, 'String'),
          3 => array($civiTable, 'String'),
        ));
      }
    }
  }
  $logger = new CRM_Migration_Logger('further_information_20');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (morning_panels_28_april_128 IS NOT NULL OR 
    afternoon_panels_28_april_129 IS NOT NULL OR evening_panel_discussion_28_apri_130 IS NOT NULL OR 
    morning_panels_29_april_131 IS NOT NULL OR afternoon_panels_29_april_132 IS NOT NULL OR 
    subscribe_to_academy_newsletter_133 IS NOT NULL)";
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
      $logger->logMessage('Error', 'No participant '.$daoSource->entity_id.' found, custom record not migrated.');
      $countLogged++;
    }
  }
  $returnValues[] = 'All further_information_20 custom data for participants migrated to CiviCRM';
  return civicrm_api3_create_success($returnValues, $params, 'Participant', 'Fix20');
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
  if (!empty($daoSource->morning_panels_28_april_128)) {
    $insertIndex++;
    $insertFields[] = 'morning_panels_28_april_128';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->morning_panels_28_april_128, 'Integer');
  }
  if (!empty($daoSource->afternoon_panels_28_april_129)) {
    $insertIndex++;
    $insertFields[] = 'afternoon_panels_28_april_129';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->afternoon_panels_28_april_129, 'Integer');
  }
  if (!empty($daoSource->evening_panel_discussion_28_apri_130)) {
    $insertIndex++;
    $insertFields[] = 'evening_panel_discussion_28_apri_130';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->evening_panel_discussion_28_apri_130, 'Integer');
  }
  if (!empty($daoSource->morning_panels_29_april_131)) {
    $insertIndex++;
    $insertFields[] = 'morning_panels_29_april_131';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->morning_panels_29_april_131, 'Integer');
  }
  if (!empty($daoSource->afternoon_panels_29_april_132)) {
    $insertIndex++;
    $insertFields[] = 'afternoon_panels_29_april_132';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->afternoon_panels_29_april_132, 'Integer');
  }
  if (!empty($daoSource->subscribe_to_academy_newsletter_133)) {
    $insertIndex++;
    $insertFields[] = 'subscribe_to_academy_newsletter_133';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->subscribe_to_academy_newsletter_133, 'Integer');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
