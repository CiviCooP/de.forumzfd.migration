<?php

/**
 * Class for ForumZFD Phone Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Phone extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      if ($this->contactExists($this->_sourceData['contact_id'])) {
        $apiParams = $this->setApiParams();
        try {
          $newEmail = civicrm_api3('Phone', 'create', $apiParams);
          return $newEmail;
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Error', 'Could not create or update phone '.$this->_sourceData['id'].' '
            .$this->_sourceData['phone'].' for contact '.$this->_sourceData['contact_id']
            .'. Error from API Phone create: '.$ex->getMessage());
        }
      } else {
        $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
          .$this->_sourceData['contact_id'].' for phone, not migrated.');
      }
    }
    return FALSE;
  }

  /**
   * Method to check if phone type is valid
   *
   * @return bool
   * @access private
   */

  private function validPhoneType() {
    if (!isset($this->_sourceData['phone_type_id'])) {
      $this->_logger->logMessage('Warning', 'Phone of contact_id '.$this->_sourceData['contact_id']
        .'has no phone_type_id, phone_type_id 1 used');
      $this->_sourceData['phone_type_id'] = 1;
    } else {
      try {
        $count = civicrm_api3('OptionValue', 'getcount', array(
          'value' => $this->_sourceData['phone_type_id'],
          'option_group_id' => 'phone_type'));
        if ($count != 1) {
          $this->_logger->logMessage('Warning', 'Phone with contact_id ' . $this->_sourceData['contact_id']
            . ' does not have a valid phone_type_id (' . $count . ' of ' . $this->_sourceData['phone_type_id']
            . 'found), phone created with phone_type_id 1');
          $this->_sourceData['phone_type_id'] = 1;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Error retrieving phone_type_id from CiviCRM for phone with contact_id '
          . $this->_sourceData['contact_id'] . ' and phone_type_id' . $this->_sourceData['phone_type_id']
          . ', phone ignored. Error from API OptionValue getcount : ' . $ex->getMessage());
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Implementation of method to validate if source data is good enough for phone
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Phone has no contact_id, Phone not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }

    if (empty($this->_sourceData['phone'])) {
      $this->_logger->logMessage('Error', 'Phone with id '.$this->_sourceData['id'] .' has an empty phone number, Phone not migrated.');
      return FALSE;
    }
    if (!$this->validLocationType()) {
      return FALSE;
    }
    if (!$this->validPhoneType()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to retrieve api params from source data
   *
   * @return array
   */
  private function setApiParams() {
    $apiParams = $this->_sourceData;
    $removes = array('new_phone_id', 'id', '*_options', 'is_processed');
    foreach ($this->_sourceData as $key => $value) {
      if (in_array($key, $removes)) {
        unset($apiParams[$key]);
      }
      if (is_array($value)) {
        unset($apiParams[$key]);
      }
    }
    return $apiParams;
  }

}