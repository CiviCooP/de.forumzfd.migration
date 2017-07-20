<?php

/**
 * Class for ForumZFD GroupContact Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 July 2017
 * @license AGPL-3.0
 */
class CRM_Migration_GroupContact extends CRM_Migration_ForumZfd {

  private $_apiParams = array();

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $this->setApiParams();
        $created = civicrm_api3('GroupContact', 'create', $this->_apiParams);
        $this->migrateSubscriptionHistory();
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update group, error from API Group create: ' . $ex->getMessage();
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }

  /**
   * Implementation of method to ste the api parameters
   *
   * @return array
   */
  public function setApiParams() {
    $apiParams = $this->_sourceData;
    $remove = array('id', );
    foreach ($remove as $removeKey) {
      unset($apiParams[$removeKey]);
    }
    // remove *_options array
    foreach ($apiParams as $key => $apiParam) {
      if (is_array($apiParam)) {
        unset($apiParams[$key]);
      }
    }
    return $apiParams;
  }

  /**
   * Method to migrate subscription history for contact/group
   * Using SQL as there is no SubscriptionHistory API
   */
  private function migrateSubscriptionHistory() {
    $sourceQuery = 'SELECT * FROM forumzfd_subscription_history WHERE contact_id = %1 AND group_id = %2';
    $sourceParams = array(
      1 => array($this->_sourceData['contact_id'], 'Integer',),
      2 => array($this->_sourceData['group_id'], 'Integer',),);
    $dao = CRM_Core_DAO::executeQuery($sourceQuery, $sourceParams);
    while ($dao->fetch()) {
      $targetQuery = 'INSERT INTO forumzfd_civicrm.civicrm_subscription_history (contact_id, group_id, date, method, status) VALUES(%1, %2, %3, %4, %5)';
      $targetParams = array(
        1 => array($this->_apiParams['contact_id'], 'Integer',),
        2 => array($this->_apiParams['group_id'], 'Integer',),
        3 => array($dao->date, 'String',),
        4 => array($dao->method, 'String',),
        5 => array($dao->status, 'String',),);
      CRM_Core_DAO::executeQuery($targetQuery, $targetParams);
    }
  }

  /**
   * Implementation of method to validate if source data is good enough for group contact
   *
   * @return bool
   */
  public function validSourceData() {
    // contact id can not be empty
    if (!isset($this->_sourceData['contact_id']) || empty($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Source group contact '.$this->_sourceData['id'].' does not have an contact id, not migrated');
      return FALSE;
    }
    // group id can not be empty
    if (!isset($this->_sourceData['group_id']) || empty($this->_sourceData['group_id'])) {
      $this->_logger->logMessage('Error', 'Source group contact '.$this->_sourceData['id'].' does not have an group id, not migrated');
      return FALSE;
    }
    // new contact id has to exist
    $newContactId = $this->findNewContactId($this->_sourceData['contact_id']);
    if ($newContactId) {
      $this->_apiParams['contact_id'] = $newContactId;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new contact for source group contact '.$this->_sourceData['id']
        .' with old contact id '.$this->_sourceData['contact_id'].', not migrated');
      return FALSE;
    }
    // new group id has to exist
    $newGroupId = $this->findNewGroupId($this->_sourceData['group_id']);
    if ($newGroupId) {
      $this->_apiParams['group_id'] = $newGroupId;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new group for source group contact '.$this->_sourceData['id']
        .' with old group id '.$this->_sourceData['group_id'].', not migrated');
      return FALSE;
    }
    return TRUE;
  }
}
