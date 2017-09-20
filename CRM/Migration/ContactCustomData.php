<?php

/**
 * Class for ForumZFD Contact Custom Data Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_ContactCustomData extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $this->generateMigrateTableName($this->_sourceData['table_name']);
      $dao = $this->getCustomDataDao($migrateTableName);
      $columns = $this->getCustomDataColumns($dao, $this->_sourceData['table_name']);
      while ($dao->fetch()) {
        $this->insertCustomData($dao, $this->_sourceData['table_name'], $columns);
      }
    }
    return FALSE;
  }

  /**
   * Implementation of method to validate if source data is good enough for note
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['table_name'])) {
      $this->_logger->logMessage('Error', 'Contact Custom Data has no table_name, not migrated.');
      return FALSE;
    }
    // create custom group and custom fields if necessary, error when not able to
    $created = $this->createCustomGroupIfNotExists($this->_sourceData);
    if ($created == FALSE) {
      $this->_logger->logMessage('Error', 'Could not find or create custom group with the name '.$this->_sourceData['table_name'].', custom data not migrated.');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to create custom group for contact custom data fix
   *
   * @param $daoCustomGroup
   * @return bool
   */
  public static function createContactCustomGroup($daoCustomGroup) {
    $createParams = get_object_vars($daoCustomGroup);
    $removes = array('N', 'id', 'new_custom_group_id');
    foreach ($createParams as $createParamKey => $createParamValue) {
      if (substr($createParamKey, 0, 1) == '_') {
        unset($createParams[$createParamKey]);
      }
      if (in_array($createParamKey, $removes)) {
        unset($createParams[$createParamKey]);
      }
    }
    if (!isset($createParams['created_date'])) {
      $createParams['created_date'] = date('Ymd');
    }
    try {
      $created = civicrm_api3('CustomGroup', 'create', $createParams);
      self::createContactCustomFields($createParams['name'], $createParams['extends'], $created['id']);
      return $created['values'][$created['id']];
    }
    catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to find or create custom fields for custom group
   *
   * @param $customGroupName
   * @param $extends
   */
  public static function createContactCustomFields($customGroupName, $extends, $customGroupId) {
    // first find all source fields for custom group
    $query = "SELECT id FROM forumzfd_custom_group WHERE name = %1 AND extends = %2";
    $sourceCustomGroupId = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($customGroupName, 'String'),
      2 => array($extends, 'String'),
    ));
    if ($sourceCustomGroupId) {
      $query = "SELECT * FROM forumzfd_custom_field WHERE custom_group_id = %1";
      $sourceCustomFields = CRM_Core_DAO::executeQuery($query, array(
        1 => array($sourceCustomGroupId, 'Integer'),
      ));
      while ($sourceCustomFields->fetch()) {
        // get new option group id if required
        if (isset($sourceCustomFields->option_group_id) && !empty($sourceCustomFields->option_group_id)) {
          $sourceCustomFields->option_group_id = CRM_Migration_CustomOptionData::getNewOptionGroupIdForCustomField($sourceCustomFields->option_group_id);
        }
        $createParams = get_object_vars($sourceCustomFields);
        $removes = array('id', 'custom_group_id', 'N', 'target_custom_group_id', 'new_custom_field_id');
        // remove all elements starting with '_' and $removes
        foreach ($createParams as $createParamKey => $createParamValue) {
          if (substr($createParamKey, 0, 1) == '_') {
            unset($createParams[$createParamKey]);
          }
          if (in_array($createParamKey, $removes)) {
            unset($createParams[$createParamKey]);
          }
          // remove all empty ones
          if (empty($createParamValue)) {
            unset($createParams[$createParamKey]);
          }
        }
        $createParams['custom_group_id'] = $customGroupId;
        civicrm_api3('CustomField', 'create', $createParams);
      }
    }
  }

}