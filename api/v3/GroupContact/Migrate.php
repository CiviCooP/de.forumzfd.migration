<?php

/**
 * GroupContact.Migrate API
 *
 * @author Erik Hommel <hommel@ee-atwork.nl>
 * @date 26 July 2017
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_group_contact_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'group_contact';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_group_contact WHERE is_processed = 0 LIMIT 2000');
  while ($daoSource->fetch()) {
    $civiGroupContact = new CRM_Migration_GroupContact($entity, $daoSource, $logger);
    $newGroupContact = $civiGroupContact->migrate();
    if ($newGroupContact == FALSE) {
      $logCount++;
      $updateQuery = 'UPDATE forumzfd_group_contact SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer'),
        2 => array($daoSource->id, 'Integer'),));
    } else {
      $createCount++;
      $updateQuery = 'UPDATE forumzfd_group_contact SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer'),
        2 => array($daoSource->id, 'Integer'),));
    }
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more group contacts to migrate';
  } else {
    $returnValues[] = $createCount.' group contacts migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'GroupContact', 'Migrate');
}
