<?php

/**
 * Activity.Migrate API
 *
 * @author Erik Hommel <hommel@ee-atwork.nl>
 * @date 26 July 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_activity_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'activity';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_activity WHERE is_processed = 0 ORDER BY id LIMIT 2500');
  while ($daoSource->fetch()) {
    $civiActivity = new CRM_Migration_Activity($entity, $daoSource, $logger);
    $newActivity = $civiActivity->migrate();
    if ($newActivity == FALSE) {
      $logCount++;
      $query = 'UPDATE forumzfd_activity SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array(1, 'Integer'),
        2 => array($daoSource->id, 'Integer'),
      ));
    } else {
      $query = 'UPDATE forumzfd_activity SET new_activity_id = %1, is_processed = %2 WHERE id = %3';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array($newActivity['id'], 'Integer'),
        2 => array(1, 'Integer'),
        3 => array($daoSource->id, 'Integer'),
      ));
      $createCount++;
    }
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more activities to migrate';
  } else {
    $returnValues[] = $createCount.' activities migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Activity', 'Migrate');
}
