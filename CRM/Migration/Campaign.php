<?php

/**
 * Class for ForumZFD Campaign Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 4 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Campaign extends CRM_Migration_ForumZfd
{

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Campaign', 'create', $apiParams);
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update campaign, error from API Campaign create: ' . $ex->getMessage() . '. Source data is ';
        $paramMessage = array();
        foreach ($apiParams as $paramKey => $paramValue) {
          if (!is_array($paramValue)) {
            $paramMessage[] = $paramKey . ' with value ' . $paramValue;
          }
        }
        $message .= implode('; ', $paramMessage);
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }

  /**
   * Implementation of method to validate if source data is good enough for campaign
   *
   * @return array
   */
  public function setApiParams() {
    $apiParams = $this->_sourceData;
    if (!isset($this->_sourceData['campaign_type_id'])) {
      $apiParams['campaign_type_id'] = 1;
    }
    if (!isset($this->_sourceData['status_id'])) {
      $apiParams['status_id'] = 1;
    }
    $remove = array('user_unique_id', 'display_name', 'sort_name', 'primary_contact_id', 'id');
    foreach ($remove as $removeKey) {
      unset($apiParams[$removeKey]);
    }
    // update parent_id if required
    if (isset($apiParams['parent_id']) && !empty($apiParams['parent_id'])) {
      $apiParams['parent_id'] = $this->findNewCampaignId($apiParams['parent_id']);
    }
    // update created_id
    if (isset($apiParams['created_id']) && !empty($apiParams['created_id'])) {
      $newContactId = $this->findNewContactId($apiParams['created_id']);
      if ($newContactId) {
        $apiParams['created_id'] = $newContactId;
      } else {
        $apiParams['created_id'] = 1;
      }
    } else {
      $apiParams['created_id'] = 1;
    }
    // update last_modified_id
    if (isset($apiParams['last_modified_id']) && !empty($apiParams['last_modified_id'])) {
      $newContactId = $this->findNewContactId($apiParams['last_modified_id']);
      if ($newContactId) {
        $apiParams['last_modified_id'] = $newContactId;
      } else {
        $apiParams['last_modified_id'] = 1;
      }
    } else {
      $apiParams['last_modified_id'] = 1;
    }
    return $apiParams;
  }

  /**
   * Implementation of method to validate if source data is good enough for contact
   *
   * @return bool
   */
  public function validSourceData() {
    return TRUE;
  }
}
