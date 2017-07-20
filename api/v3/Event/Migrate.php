<?php

/**
 * Event.Migrate API for ForumZFD migration of events
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel <hommel@ee-atwork.nl>
 * @date 6 July 2017
 * @license AGPL-3.0
 */
function civicrm_api3_event_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'event';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_event ORDER BY id');
  while ($daoSource->fetch()) {
    $civiEvent = new CRM_Migration_Event($entity, $daoSource, $logger);
    $newEvent = $civiEvent->migrate();
    if ($newEvent == FALSE) {
      $logCount++;
    } else {
      $query = 'UPDATE forumzfd_event SET new_event_id = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array($newEvent['id'], 'Integer'),
        2 => array($daoSource->id, 'Integer'),
      ));
      $createCount++;
    }
  }
  $returnValues[] = $createCount.' events migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  return civicrm_api3_create_success($returnValues, $params, 'Event', 'Migrate');
}

