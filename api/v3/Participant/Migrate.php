<?php

/**
 * Participant.Migrate API for ForumZFD migration of contacts
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
function civicrm_api3_participant_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'participant';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_participant WHERE is_processed = 0 ORDER BY id LIMIT 1500');
  while ($daoSource->fetch()) {
    $civiParticipant = new CRM_Migration_Participant($entity, $daoSource, $logger);
    $newParticipant = $civiParticipant->migrate();
    if ($newParticipant == FALSE) {
      $logCount++;
      $query = 'UPDATE forumzfd_participant SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array(1, 'Integer'),
        2 => array($daoSource->id, 'Integer'),
      ));
    } else {
      $query = 'UPDATE forumzfd_participant SET new_participant_id = %1, is_processed = %2 WHERE id = %3';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array($newParticipant['id'], 'Integer'),
        2 => array(1, 'Integer'),
        3 => array($daoSource->id, 'Integer'),
      ));
      $createCount++;
    }
  }
  if (empty($daoSource->N)) {
    // add custom data at the end
    CRM_Migration_Participant::addCustomData();
    $returnValues[] = 'No more participants to migrate';
  } else {
    $returnValues[] = $createCount.' participants migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Participant', 'Migrate');
}

