<?php

/**
 * Class for ForumZFD Website Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Website extends CRM_Migration_ForumZfd {

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
          $newWebsite = civicrm_api3('Website', 'create', $apiParams);
          return $newWebsite;
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Error', 'Could not create or website '.$this->_sourceData['id'].' '
            .$this->_sourceData['url'].' for contact '.$this->_sourceData['contact_id']
            .'. Error from API Website create: '.$ex->getMessage());
        }
      } else {
        $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
          .$this->_sourceData['contact_id'].' for website, not migrated.');
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
    $removes = array('new_website_id', 'id', '*_options', 'is_processed');
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
   * Implementation of method to validate if source data is good enough for website
   *
   * @return bool
   */
  public function validSourceData() {

    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Website has no contact_id, website not migrated. Website id is '.$this->_sourceData['id']);
      return FALSE;
    }

    if (empty($this->_sourceData['url'])) {
      $this->_logger->logMessage('Error', 'Website has an empty url, website not migrated. Website id is '.$this->_sourceData['id']);
      return FALSE;
    }

    return TRUE;
  }
}