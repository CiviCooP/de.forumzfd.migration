<?php
/**
 * Phone.Migrate API for ForumZFD migration of phones
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
function civicrm_api3_phone_Migrate($params) {
  set_time_limit(0);
  $entity = "phone";
  $returnValues = array();
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_phone WHERE is_processed = 0 ORDER BY contact_id LIMIT 1500');
  while ($daoSource->fetch()) {
    $civiPhone = new CRM_Migration_Phone($entity, $daoSource, $logger);
    $newPhone = $civiPhone->migrate();
    if ($newPhone == FALSE) {
      $logCount++;
      $updateQuery = 'UPDATE forumzfd_phone SET is_processed = %1 WHERE id = %2';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer'),
        2 => array($daoSource->id, 'Integer'),));
    } else {
      $createCount++;
      $updateQuery = 'UPDATE forumzfd_phone SET is_processed = %1, new_phone_id = %2 WHERE id = %3';
      CRM_Core_DAO::executeQuery($updateQuery, array(
        1 => array(1, 'Integer'),
        2 => array($newPhone['id'], 'Integer'),
        3 => array($daoSource->id, 'Integer'),));
    }
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more phones to migrate';
  } else {
    $returnValues[] = $createCount.' phones migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Phone', 'Migrate');
}

