<?php
/**
 * OptionValue.Migrate API for ForumZFD migration of tags
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 5 April 2017
 * @license AGPL-3.0
 */
function civicrm_api3_option_value_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'option_value';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_option_value WHERE is_processed = 0 ORDER BY id LIMIT 250');
  while ($daoSource->fetch()) {
    $civiOptionValue = new CRM_Migration_OptionValue($entity, $daoSource, $logger);
    $newOptionValue = $civiOptionValue->migrate();
    if ($newOptionValue == FALSE) {
      $logCount++;
    } else {
      $createCount++;
    }
    $updateQuery = 'UPDATE forumzfd_option_value SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($updateQuery, array(1 => array(1, 'Integer'), 2 => array($daoSource->id, 'Integer')));
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more option values to migrate';
  } else {
    $returnValues[] = $createCount.' option values migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'OptionValue', 'Migrate');
}

