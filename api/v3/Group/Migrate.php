<?php

/**
 * Group.Migrate API for ForumZFD migration of groups
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
function civicrm_api3_group_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'group';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_group ORDER BY id');
  while ($daoSource->fetch()) {
    $civiGroup = new CRM_Migration_Group($entity, $daoSource, $logger);
    $newGroup = $civiGroup->migrate();
    if ($newGroup == FALSE) {
      $logCount++;
    } else {
      $query = 'UPDATE forumzfd_group SET new_group_id = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($query, array(
        1 => array($newGroup['id'], 'Integer'),
        2 => array($daoSource->id, 'Integer'),
      ));
      $createCount++;
    }
  }
  // finally fix parents!
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_group WHERE parents IS NOT NULL');
  while ($daoSource->fetch()) {
    $civiGroup = new CRM_Migration_Group($entity, $daoSource, $logger);
    $civiGroup->fixParents($daoSource);
  }
  $returnValues[] = $createCount.' groups migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  return civicrm_api3_create_success($returnValues, $params, 'Group', 'Migrate');
}

