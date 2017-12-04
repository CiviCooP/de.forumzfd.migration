<?php

function civicrm_api3_entity_file_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'entity_file';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $query = "SELECT * FROM forumzfd_entity_file WHERE entity_table IN (%1, %2, %3, %4, %5) AND is_processed = 0 LIMIT 5000";
  $daoSource = CRM_Core_DAO::executeQuery($query, array(
    1 => array('civicrm_activity', 'String'),
    2 => array('civicrm_contact', 'String'),
    3 => array('civicrm_note', 'String'),
    4 => array('civicrm_value_presse_6', 'String'),
    5 => array('civicrm_value_trainerin_19', 'String')
  ));
  while ($daoSource->fetch()) {
    $civiEntityFile = new CRM_Migration_EntityFile($entity, $daoSource, $logger);
    $newEntityFile = $civiEntityFile->migrate();
    if ($newEntityFile == FALSE) {
      $logCount++;
    } else {
      $createCount++;
    }
    $updateQuery = 'UPDATE forumzfd_entity_file SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),));
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more entity files to migrate';
  } else {
    $returnValues[] = $createCount.' entity_files migrated to CiviCRM, '.$logCount.' with logged errors OR that already existed that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'EntityFile', 'Migrate');
}
