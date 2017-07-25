<?php
/**
 * Contribution.Migrate API for ForumZFD migration of contacts
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 14 June 2017
 * @license AGPL-3.0
 */
function civicrm_api3_contribution_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'contribution';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_contribution WHERE is_processed = 0 ORDER BY id LIMIT 2500');
  while ($daoSource->fetch()) {
    $civiContribution = new CRM_Migration_Contribution($entity, $daoSource, $logger);
    $newContribution = $civiContribution->migrate();
    if ($newContribution == FALSE) {
      $logCount++;
      $updateQuery = 'UPDATE forumzfd_contribution SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(1 => array(1, 'Integer'), 2 => array($daoSource->id, 'Integer')));
    } else {
      $createCount++;
      $updateQuery = 'UPDATE forumzfd_contribution SET is_processed = %1, new_contribution_id = %2 WHERE id = %3';
      CRM_Core_DAO::executeQuery($updateQuery, array(1 => array(1, 'Integer'), 2 => array($newContribution['id'], 'Integer'), 3 => array($daoSource->id, 'Integer')));
    }
  }
  // add custom data
  CRM_Migration_Contribution::addCustomData();

  if (empty($daoSource->N)) {
    $returnValues[] = 'No more contributions to migrate';
  } else {
    $returnValues[] = $createCount.' contributions migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Contribution', 'Migrate');
}

