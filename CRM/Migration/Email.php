<?php

/**
 * Class for ForumZFD Email Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Email extends CRM_Migration_ForumZfd {

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
          $newEmail = civicrm_api3('Email', 'create', $apiParams);
          return $newEmail;
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Error', 'Could not create or update email '.$this->_sourceData['id'].' '
            .$this->_sourceData['email'].' for contact '.$this->_sourceData['contact_id']
            .'. Error from API Email create: '.$ex->getMessage());
        }
      } else {
        $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
          .$this->_sourceData['contact_id'].' for email, not migrated.');
      }
    }
    return FALSE;
  }

  /**
   * Method to retrieve api params from source data
   *
   * @return array
   */
  private function setApiParams() {
    $apiParams = $this->_sourceData;
    $removes = array('new_email_id', 'id', '*_options', 'is_processed');
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


  /**
   * Implementation of method to validate if source data is good enough for email
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Email has no contact_id, email not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    if (empty($this->_sourceData['email'])) {
      $this->_logger->logMessage('Error', 'Email has an empty emailaddress, email not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    if (!$this->validLocationType()) {
      return FALSE;
    }
    return TRUE;
  }
}