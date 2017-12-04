<?php

/**
 * Class for ForumZFD Event Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 July 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Event extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Event', 'create', $apiParams);
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update event, error from API Event create: ' . $ex->getMessage();
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }

  /**
   * Implementation of method to validate if source data is good enough for event
   *
   * @return array
   */
  public function setApiParams() {
    // find campagn_id if required
    if (!empty($this->_sourceData['campaign_id'])) {
      $newCampaignId = $this->findNewCampaignId($this->_sourceData['campaign_id']);
      if ($newCampaignId) {
        $this->_sourceData['campaign_id'] = $newCampaignId;
      } else {
        unset($this->_sourceData['campaign_id']);
      }
    }
    // remove participant_listing_id if empty
    if (empty($this->_sourceData['participant_listing_id'])) {
      unset($this->_sourceData['participant_listing_id']);
    }
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
   * Implementation of method to validate if source data is good enough for contact
   *
   * @return bool
   */
  public function validSourceData() {
    // event type can not be empty
    if (!isset($this->_sourceData['event_type_id']) || empty($this->_sourceData['event_type_id'])) {
      $this->_logger->logMessage('Error', 'Source event '.$this->_sourceData['id'].' does not have an event type set, nit migrated');
      return FALSE;
    }
    // event type has to exist
    try {
      $count = civicrm_api3('OptionValue', 'getcount', array(
        'option_group_id' => 'event_type',
        'value' => $this->_sourceData['event_type_id'],
      ));
      if ($count == 0) {
        $this->_logger->logMessage('Error', 'Could not find event type '.$this->_sourceData['event_type_id']
          .' for event '.$this->_sourceData['id'].', not migrated');
        return FALSE;
      }
      if ($count > 1) {
        $this->_logger->logMessage('Error', 'Found more than one event types '.$this->_sourceData['event_type_id']
          .' for event '.$this->_sourceData['id'].', not migrated');
        return FALSE;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Error trying to check event type '.$this->_sourceData['event_type_id']
        .' for event '.$this->_sourceData['id'].' with API OptionValue getcount. Error from API: '.$ex->getMessage());
    }
    // financial type has to exist
    if (!empty($this->_sourceData['financial_type_id'])) {
      $count = $this->countFinancialTypeId($this->_sourceData['financial_type_id']);
      if ($count == 0) {
        $this->_logger->logMessage('Error', 'Could not find financial type ' . $this->_sourceData['financial_type_id']
          . ' for event ' . $this->_sourceData['id'] . ', not migrated');
        return FALSE;
      }
      if ($count > 1) {
        $this->_logger->logMessage('Error', 'Found more than one financial types ' . $this->_sourceData['financial_type_id']
          . ' for event ' . $this->_sourceData['id'] . ', not migrated');
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to add event custom data
   */
  public static function addCustomData() {
    // specific logger
    $logger = new CRM_Migration_Logger('event_custom_data');
    // retrieve all custom tables for participant
    $query = "SELECT * FROM forumzfd_custom_group WHERE extends = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array('Event', 'String'),
    ));
    while ($dao->fetch()) {
      $event = new CRM_Migration_Event('event_custom_data', $dao, $logger);
      $event->createCustomGroupIfNotExists($event->_sourceData);
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $event->generateMigrateTableName($event->_sourceData['table_name']);
      $daoCustomData = $event->getCustomDataDao($migrateTableName);
      $columns = $event->getCustomDataColumns($daoCustomData, $event->_sourceData['table_name']);
      while ($daoCustomData->fetch()) {
        // find new event id
        $newEventId = $event->findNewPatricipantId($daoCustomData->entity_id);
        if ($newEventId) {
          $dao->entity_id = $newEventId;
          $event->insertCustomData($daoCustomData, $event->_sourceData['table_name'], $columns);
        } else {
          $logger->logMessage('Error', 'Could not find or create a new event for '.$daoCustomData->entity_id.' and table name '
            .$event->_sourceData['table_name'].', custom data not migrated.');
        }
      }
    }
  }

}
