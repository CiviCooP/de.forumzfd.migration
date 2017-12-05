<?php

function civicrm_api3_participant_Fix58($params) {
  $civiTable = "civicrm_value_zusatzinfos_online_seminare_58";
  $fzfdTable = "forumzfd_value_zusatzinfos_online_seminare_58";
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
  $logger = new CRM_Migration_Logger('zusatz_58');
  $querySource = "SELECT * FROM ".$fzfdTable." WHERE is_processed = 0 AND (what_is_your_motivation_to_take__502 IS NOT NULL OR 
    how_would_you_like_to_be_called__503 IS NOT NULL )";
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
  $returnValues[] = 'All zusatz 58 custom data for participants migrated to CiviCRM';
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
  if (!empty($daoSource->what_is_your_motivation_to_take__502)) {
    $insertIndex++;
    $insertFields[] = 'what_is_your_motivation_to_take__502';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->what_is_your_motivation_to_take__502, 'String');
  }
  if (!empty($daoSource->how_would_you_like_to_be_called__503)) {
    $insertIndex++;
    $insertFields[] = 'how_would_you_like_to_be_called__503';
    $insertValues[] = '%'.$insertIndex;
    $insertParams[$insertIndex] = array($daoSource->how_would_you_like_to_be_called__503, 'String');
  }
  $insertQuery = 'INSERT INTO ' . $civiTable . ' ('.implode(', ', $insertFields)
    .') VALUES('.implode(', ',$insertValues).')';
}
