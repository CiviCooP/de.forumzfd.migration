<?php

/**
 * Class for ForumZFD Employer Migration to CiviCRM
 * (update contact if checks are OK)
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Employer extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
          $apiParams = array(
            'id' => $this->_sourceData['id'],
            'employer_id' => $this->_sourceData['employer_id'],
          );
          $created = civicrm_api3('Contact', 'create', $apiParams);
          return $created;
        }
        catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not update contact with employer details, error from API Contact create: ' . $ex->getMessage() . '. Source data is ';
        $paramMessage = array();
        foreach ($apiParams as $paramKey => $paramValue) {
          $paramMessage[] = $paramKey . ' with value ' . $paramValue;
        }
        $message .= implode('; ', $paramMessage);
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }

  /**
   * Implementation of method to validate if source data is good enough for contact
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['id'])) {
      $this->_logger->logMessage('Error', 'Contact has no contact_id, not updated with employer migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    return TRUE;
  }

}