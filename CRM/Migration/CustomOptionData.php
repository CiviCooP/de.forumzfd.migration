<?php

/**
 * Class for ForumZFD OptionValue Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 September 2017
 * @license AGPL-3.0
 */
class CRM_Migration_CustomOptionData {

  private $_logger = NULL;
  private $_sourceData = NULL;

  /**
   * CRM_Migration_CustomOptionData constructor.
   */
  public function __construct() {
    $this->_logger = new CRM_Migration_Logger('custom_data_option_groups');
  }

  /**
   * Method to migrate the data object for option value and option group
   *
   * @param $sourceData
   * @return int
   */
  public function migrateGroups($sourceData) {
    $this->_sourceData = $sourceData;
    // get or create option group
    $newOptionGroupId = $this->getOptionGroup();
    return $newOptionGroupId;
  }

  /**
   * Method to create option values if necessary
   *
   * @param $sourceData
   */
  public function migrateValues($sourceData) {
    $this->_sourceData = $sourceData;
    // get or create option value
    $this->getOptionValue();
  }

  /**
   * Method to get the new option group id
   *
   * @param $optionGroupId
   * @return null|string
   */
  public static function getNewOptionGroupIdForCustomField($optionGroupId) {
    $query = 'SELECT new_option_group_id FROM forumzfd_custom_option_data WHERE option_group_id = %1 LIMIT 1';
    return CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($optionGroupId, 'Integer'),
    ));
  }

  /**
   * Method to find or create the option group
   *
   * @return mixed
   */
  private function getOptionGroup() {
    $count = civicrm_api3('OptionGroup', 'getcount', array(
      'name' => $this->_sourceData->option_group_name,
    ));
    switch ($count) {
      case 0:
        return $this->createOptionGroup();
        break;
      case 1:
        return civicrm_api3('OptionGroup', 'getvalue', array(
          'name' => $this->_sourceData->option_group_name,
          'return' => 'id',
        ));
        break;
      default:
        $this->_logger->logMessage('Warning', 'More than one option group found with name '
          .$this->_sourceData->option_group_name.', new option group created.');
        return $this->createOptionGroup();
    }
  }

  /**
   * Method to find or create the option group
   */
  private function getOptionValue() {
    $count = civicrm_api3('OptionValue', 'getcount', array(
      'option_group_id' => $this->_sourceData->option_group_name,
      'value' => $this->_sourceData->option_value_value,
    ));
    switch ($count) {
      case 0:
        $this->createOptionValue();
        break;
      case 1:
        break;
      default:
        $this->_logger->logMessage('Warning', 'Option Value '.$this->_sourceData->option_value_value
          .' meerdere keren gevonden in option group '.$this->_sourceData->option_group_name);
        break;
    }
  }

  /**
   * Method to create option group
   *
   * @return mixed
   */
  private function createOptionGroup() {
    $optionGroup = civicrm_api3('OptionGroup', 'create', array(
      'name' => $this->_sourceData->option_group_name,
      'title' => $this->_sourceData->option_group_title,
      'description' => $this->_sourceData->option_group_description,
      'is_active' => 1,
      'is_reserved' => 1,
    ));
    return $optionGroup['id'];
  }

  /**
   * Method to create option value
   */
  private function createOptionValue() {
    civicrm_api3('OptionValue', 'create', array(
      'option_group_id' => $this->_sourceData->option_group_name,
      'name' => $this->_sourceData->option_value_name,
      'value' => $this->_sourceData->option_value_value,
      'label' => $this->_sourceData->option_value_label,
      'is_active' => 1,
    ));
  }

}