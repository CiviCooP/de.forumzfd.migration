<?php

/**
 * Class for ForumZFD Contact Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 5 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Contact extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Contact', 'create', $apiParams);
        $this->addCustomData();
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update contact, error from API Contact create: '.$ex->getMessage().'. Source data is ';
        $paramMessage = array();
        foreach ($apiParams as $paramKey => $paramValue) {
          $paramMessage[] = $paramKey.' with value '.$paramValue;
        }
        $message .= implode('; ', $paramMessage);
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }
  /**
   * Method to create params for contact create (remove id as we need a new contact)
   */
  private function setApiParams() {
    $apiParams = $this->_sourceData;
    if (isset($apiParams['id'])) {
      unset ($apiParams['id']);
    }
    if (empty($apiParams['external_identifier'])) {
      unset($apiParams['external_identifier']);
    }
    foreach ($apiParams as $paramKey => $paramValue) {
      if (is_array($paramValue)) {
        unset($apiParams[$paramKey]);
      }
    }
    // ignore employer_id, will be set later
    if (isset($apiParams['employer_id'])) {
      unset($apiParams['employer_id']);
    }
    $remove = array('user_unique_id', 'display_name', 'sort_name', 'primary_contact_id');
    foreach ($remove as $removeKey) {
      unset($apiParams[$removeKey]);
    }
    return $apiParams;
  }

  /**
   * Implementation of method to validate if source data is good enough for contact
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['id'])) {
      $this->_logger->logMessage('Error', 'Contact has no contact_id, not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    // check if email and postal greeting exists, if not use default
    $this->checkGreeting();
    return TRUE;
  }

  /**
   * Method to check if the email and/or postal greeting exist and are valid for the contact types involved. If not, use default
   */
  private function checkGreeting() {
    $config = CRM_Migration_Config::singleton();
    $defaultEmail = NULL;
    $defaultPostal = NULL;
    $filter = NULL;
    // warning if both email greeting custom and id set
    if (!empty($this->_sourceData['email_greeting_custom']) && !empty($this->_sourceData['email_greeting_id'])) {
      $this->_logger->logMessage('Warning', 'Both email_greeting_id and email_greeting_custom set for contact '
      .$this->_sourceData['display_name'].', email_greeting_id ignored');
      $this->_sourceData['email_greeting_id'] = NULL;
    }
    // warning if both postal greeting custom and id set
    if (!empty($this->_sourceData['postal_greeting_custom']) && !empty($this->_sourceData['postal_greeting_id'])) {
      $this->_logger->logMessage('Warning', 'Both postal_greeting_id and postal_greeting_custom set for contact '
      .$this->_sourceData['display_name'].', postal_greeting_id ignored');
      $this->_sourceData['postal_greeting_id'] = NULL;
    }
    // set filter based on contact type
    switch ($this->_sourceData['contact_type']) {
      case "Individual":
        $filter = 1;
        $defaultEmail = $config->getDefaultEmailIndividual();
        $defaultPostal = $config->getDefaultPostalIndividual();
        break;
      case "Household":
        $filter = 2;
        $defaultEmail = $config->getDefaultEmailHousehold();
        $defaultPostal = $config->getDefaultPostalHousehold();
        break;
      case "Organization":
        $filter = 3;
        $defaultEmail = $config->getDefaultEmailOrganization();
        $defaultPostal = $config->getDefaultPostalOrganization();
        break;
    }
    // check email greeting
    if (isset($this->_sourceData['email_greeting_id']) && !empty($this->_sourceData['email_greeting_id'])) {
      try {
        $emailCount = civicrm_api3('OptionValue', 'getcount', array(
          'option_group_id' => 'email_greeting',
          'value' => $this->_sourceData['email_greeting_id'],
          'filter' => $filter,
        ));
        if ($emailCount == 0) {
          $this->_logger->logMessage('Warning', 'Could not find email_greeting_id '.$this->_sourceData['email_greeting_id']
            .' for contact with name '.$this->_sourceData['display_name'].' and contact type '.$this->_sourceData['contact_type']
            .', replaced with the default email greeting id '.$defaultEmail);
          $this->_sourceData['email_greeting_id'] = $defaultEmail;
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Warning', 'Error from API OptionValue getcount in '.__METHOD__.' for email_greeting_id '
          .$this->_sourceData['email_greeting_id'].', contact with name '.$this->_sourceData['display_name'].' and contact type '
          .$this->_sourceData['contact_type'].', replaced with the default email greeting id '.$defaultEmail);
        $this->_sourceData['email_greeting_id'] = $defaultEmail;
      }
    }
    // check postal greeting
    if (isset($this->_sourceData['postal_greeting_id']) && !empty($this->_sourceData['postal_greeting_id'])) {
      try {
        $postalCount = civicrm_api3('OptionValue', 'getcount', array(
          'option_group_id' => 'postal_greeting',
          'value' => $this->_sourceData['postal_greeting_id'],
          'filter' => $filter,
        ));
        if ($postalCount == 0) {
          $this->_logger->logMessage('Warning', 'Could not find postal_greeting_id '.$this->_sourceData['postal_greeting_id']
            .' for contact with name '.$this->_sourceData['display_name'].' and contact type '.$this->_sourceData['contact_type']
            .', replaced with the default postal greeting id '.$defaultPostal);
          $this->_sourceData['postal_greeting_id'] = $defaultPostal;
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Warning', 'Error from API OptionValue getcount in '.__METHOD__.' for postal_greeting_id '
          .$this->_sourceData['postal_greeting_id'].', contact with name '.$this->_sourceData['display_name'].' and contact type '
          .$this->_sourceData['contact_type'].', replaced with the default postal greeting id '.$defaultPostal);
        $this->_sourceData['postal_greeting_id'] = $defaultPostal;
      }
    }
  }

  /**
   * Method to add contact custom data if necessary
   *
   * @access private
   */
  private function addCustomData() {
  }
}