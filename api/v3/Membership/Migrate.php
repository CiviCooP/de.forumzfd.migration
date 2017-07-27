<?php

/**
 * Membership.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_membership_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'membership';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_membership WHERE is_processed = 0 ORDER BY id');
  while ($daoSource->fetch()) {
    $civiMembership = new CRM_Migration_Membership($entity, $daoSource, $logger);
    $newMembership = $civiMembership->migrate();
    if ($newMembership == FALSE) {
      $updateQuery = 'UPDATE forumzfd_membership SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer',),
        2 => array($daoSource->id, 'Integer',),
        ));
    } else {
      $createCount++;
      $updateQuery = 'UPDATE forumzfd_membership SET is_processed = %1, new_membership_id = %2 WHERE id = %3';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer',),
        2 => array($newMembership['id'], 'Integer',),
        3 => array($daoSource->id, 'Integer',),
        ));
    }
  }
  $returnValues[] = $createCount.' memberships migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  return civicrm_api3_create_success($returnValues, $params, 'Membership', 'Migrate');
}
