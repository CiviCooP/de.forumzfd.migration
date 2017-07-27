<?php

/**
 * GroupCustomData.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_group_custom_data_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  CRM_Migration_Group::addCustomData();
  $returnValues[] = 'All custom data for groups migrated to CiviCRM, check log for errors';
  return civicrm_api3_create_success($returnValues, $params, 'GroupCustomData', 'Migrate');
}
