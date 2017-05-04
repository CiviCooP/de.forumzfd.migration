<?php

/**
 * Employer.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 18 April 2017
 * @throws API_Exception
 */
function civicrm_api3_employer_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'employer';
  $createCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_contact WHERE employer_id IS NOT NULL');
  while ($daoSource->fetch()) {
    $civiEmployer = new CRM_Migration_Employer($entity, $daoSource, $logger);
    $civiEmployer->migrate();
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more employers to migrate';
  } else {
    $returnValues[] = $createCount.' employers updated in CiviCRM';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Employer', 'Migrate');
}
