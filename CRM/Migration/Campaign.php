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
        $message = 'Could not add or update campaign, error from API Contact campaign: ' . $ex->getMessage() . '. Source data is ';
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
   * Implementation of method to validate if source data is good enough for address
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
      $query = 'SELECT new_campaign_id FROM forumzfd_campaign WHERE id = %1';
      $newParentId = CRM_Core_DAO::singleValueQuery($query, array(
        1 => array($apiParams['parent_id'], 'Integer'),
      ));
      $apiParams['parent_id'] = $newParentId;
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
