<?php

/**
 * ContactCustomData.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contact_custom_data_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $entity = 'contact_custom_data';
  $logger = new CRM_Migration_Logger($entity);
  $query = "SELECT * FROM forumzfd_custom_group WHERE extends IN(%1, %2, %3)";
  $queryParams = array(
    1 => array('Contact', 'String'),
    2 => array('Individual', 'String'),
    3 => array('Organization', 'String'),
  );
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  while ($dao->fetch()) {
    // migrate only if target table does not exist yet
    if (!CRM_Core_DAO::checkTableExists($dao->table_name)) {
      $contactCustomData = new CRM_Migration_ContactCustomData($entity, $dao, $logger);
      $contactCustomData->migrate();
    }
  }
  $returnValues[] = 'All custom data for contacts migrated to CiviCRM, check log for errors';
  return civicrm_api3_create_success($returnValues, $params, 'ContactCustomData', 'Migrate');
}