<?php

/**
 * Class for ForumZFD Participant Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 July 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Participant extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Participant', 'create', $apiParams);
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update participant, error from API Participant create: ' . $ex->getMessage();
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
    $apiParams = $this->_sourceData;
    $remove = array('id', 'new_participant_id');
    foreach ($remove as $removeKey) {
      unset($apiParams[$removeKey]);
    }
    // remove *_options array
    foreach ($apiParams as $key => $apiParam) {
      if (is_array($apiParam)) {
        unset($apiParams[$key]);
      }
    }
    // replace participant_status_type if required
    $replaceStatusIds = array(
      1 => 22,
      2 => 1,
      4 => 4,
      5 => 18,
      6 => 19,
      7 => 7,
      8 => 17,
      10 => 7,
      12 => 24,
      13 => 13,
      14 => 19,
      16 => 6,
      19 => 23,
      20 => 11,
      21 => 14,
      22 => 21,
      23 => 20,
      25 => 25,
      26 => 26,
    );
    if (isset($replaceStatusIds[$apiParams['status_id']])) {
      $apiParams['status_id'] = $replaceStatusIds[$apiParams['status_id']];
    } else {
      $apiParams['status_id'] = 3;
      $this->_logger->logMessage('Warning', 'Source participant '.$this->_sourceData['id'].' has status '
        .$apiParams['status_id']. ', set to default of 3 (no show)');
    }

    return $apiParams;
  }

  /**
   * Implementation of method to validate if source data is good enough for contact
   *
   * @return bool
   */
  public function validSourceData() {
    // contact_id and event_id can not be empty
    if (empty($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Source participant '.$this->_sourceData['id'].' does not have a contact_id, not migrated');
      return FALSE;
    }
    if (empty($this->_sourceData['event_id'])) {
      $this->_logger->logMessage('Error', 'Source participant '.$this->_sourceData['id'].' does not have an event_id, not migrated');
      return FALSE;
    }
    // ignore if status = TeilnahmeMV
    if ($this->_sourceData['status_id'] == 27) {
      $this->_logger->logMessage('Warning', 'Source participant '.$this->_sourceData['id'].' has status TeilnahmeMV, not migrated');
      return FALSE;
    }
    // new contact id has to exist
    $newContactId = $this->findNewContactId($this->_sourceData['contact_id']);
    if ($newContactId) {
      $this->_sourceData['contact_id'] = $newContactId;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new contact id for '.$this->_sourceData['contact_id']
        .' with participant '.$this->_sourceData['id'].', not migrated');
      return FALSE;
    }
    // new event_id has to exist
    $newEventId = $this->findNewEventId($this->_sourceData['event_id']);
    if ($newEventId) {
      $this->_sourceData['event_id'] = $newEventId;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new event id for '.$this->_sourceData['event_id']
        .' with participant '.$this->_sourceData['id'].', not migrated');
      return FALSE;
    }
    // if campaign, find new one and warning if not found
    if (!empty($this->_sourceData['campaign_id'])) {
      $newCampaignId = $this->findNewCampaignId($this->_sourceData['campaign_id']);
      if ($newCampaignId) {
        $this->_sourceData['campaign_id'] = $newCampaignId;
      } else {
        $this->_logger->logMessage('Warning', 'No new campaign found for '.$this->_sourceData['campaign_id']
          .', no campaign added for migrated participant');
      }
    }
    // if transferred_to_contact_id, find new or remove
    if (isset($this->_sourceData['transferred_to_contact_id']) && !empty($this->_sourceData['transferred_to_contact_id'])) {
      $newContactId = $this->findNewContactId($this->_sourceData['transferred_to_contact_id']);
      if ($newContactId) {
        $this->_sourceData['transferred_to_contact_id'] = $newContactId;
      } else {
        unset($this->_sourceData['transferred_to_contact_id']);
      }
    } else {
      unset($this->_sourceData['transferred_to_contact_id']);
    }
    // if registered_by_id, find new or remove
    if (isset($this->_sourceData['registered_by_id']) && !empty($this->_sourceData['registered_by_id'])) {
      $newContactId = $this->findNewContactId($this->_sourceData['registered_by_id']);
      if ($newContactId) {
        $this->_sourceData['registered_by_id'] = $newContactId;
      } else {
        unset($this->_sourceData['registered_by_id']);
      }
    } else {
      unset($this->_sourceData['registered_by_id']);
    }
    return TRUE;
  }

  /**
   * Method to add participant custom data
   */
  public static function addCustomData() {
    // specific logger
    $logger = new CRM_Migration_Logger('participant_custom_data');
    // retrieve all custom tables for participant
    $query = "SELECT * FROM forumzfd_custom_group WHERE extends = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array('Participant', 'String'),
    ));
    while ($dao->fetch()) {
      $participant = new CRM_Migration_Participant('participant_custom_data', $dao, $logger);
      $participant->createCustomGroupIfNotExists($participant->_sourceData);
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $participant->generateMigrateTableName($participant->_sourceData['table_name']);
      $daoCustomData = $participant->getCustomDataDao($migrateTableName);
      $columns = $participant->getCustomDataColumns($daoCustomData, $participant->_sourceData['table_name']);
      while ($daoCustomData->fetch()) {
        // find new participant id
        $newParticipantId = $participant->findNewPatricipantId($daoCustomData->entity_id);
        if ($newParticipantId) {
          $dao->entity_id = $newParticipantId;
          $participant->insertCustomData($daoCustomData, $participant->_sourceData['table_name'], $columns);
        } else {
          $logger->logMessage('Error', 'Could not find or create a new contact for '.$daoCustomData->entity_id.' and table name '
            .$participant->_sourceData['table_name'].', custom data not migrated.');
        }
      }
    }
  }
}
