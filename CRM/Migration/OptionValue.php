<?php

/**
 * Class for ForumZFD OptionValue Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 4 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_OptionValue extends CRM_Migration_ForumZfd {

  private $_optionGroupId = NULL;

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $optionValue = civicrm_api3('OptionValue', 'create', $this->setApiParams());
        return $optionValue;
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Could not create OptionValue in '.__METHOD__.', source data is '
          .implode(';', $this->_sourceData));
      }
    }
    return FALSE;
  }

  /**
   * Method to set the param for the api option value create
   *
   * @return array
   */
  private function setApiParams() {
    $apiParams = array(
      'option_group_id' => $this->_sourceData['option_group_id'],
      'label' => $this->_sourceData['label'],
      'name' => $this->_sourceData['name'],
      'filter' => $this->_sourceData['filter'],
      'weight' => $this->_sourceData['weight'],
      'description' => $this->_sourceData['description'],
      'component_id' => $this->_sourceData['component_id'],
      'visibility_id' => $this->_sourceData['visibility_id'],
      'is_optgroup' => $this->_sourceData['is_optgroup'],
      'is_reserved' => $this->_sourceData['is_reserved'],
      'is_active' => $this->_sourceData['is_active']
    );
    // add id if already exists on name so values are updated
    try {
      $apiParams['id'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $apiParams['option_group_id'],
        'name' => $apiParams['name'],
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
    return $apiParams;
  }


  /**
   * Implementation of method to validate if source data is good enough for option value
   *
   * @return bool
   */
  public function validSourceData() {
    // option group id should either be in sourceData or in property
    if (!isset($this->_sourceData['option_group_id'])) {
      $this->_sourceData['option_group_id'] = $this->_optionGroupId;
    } else {
      $this->setOptionGroup($this->_sourceData['option_group_id']);
    }
    if (empty($this->_sourceData['option_group_id'])) {
      $this->_logger->logMessage('Error', 'OptionValue has no option_group_id, OptionValue not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    if (!isset($this->_sourceData['name'])) {
      $this->_logger->logMessage('Error', 'OptionValue has no name, OptionValue not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to set the option group id. This can be the option_group name or the id
   *
   * @param $optionGroupId
   */
  public function setOptionGroup($optionGroupId) {
      $this->_optionGroupId = $optionGroupId;
  }
}