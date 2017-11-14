<?php

/**
 * CustomOptionGroups.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_custom_option_groups_Migrate($params) {
  set_time_limit(0);
  $updateQuery = "UPDATE forumzfd_custom_option_data SET new_option_group_id = %1 WHERE option_group_id = %2";
  $customOptionData = new CRM_Migration_CustomOptionData();
  // create option groups if required
  $daoGroups = CRM_Core_DAO::executeQuery("SELECT DISTINCT(option_group_id), option_group_name, option_group_title, option_group_description FROM forumzfd_custom_option_data");
  while ($daoGroups->fetch()) {
    $newOptionGroupId = $customOptionData->migrateGroups($daoGroups);
    CRM_Core_DAO::executeQuery($updateQuery, array(
      1 => array($newOptionGroupId, 'Integer'),
      2 => array($daoGroups->option_group_id, 'Integer'),
    ));
  }
  // option values next
  $daoValues = CRM_Core_DAO::executeQuery("SELECT * FROM forumzfd_custom_option_data");
  while($daoValues->fetch()) {
    $customOptionData->migrateValues($daoValues);
  }
  return civicrm_api3_create_success(array(), $params, 'CustomOptionData', 'Migrate');
}

