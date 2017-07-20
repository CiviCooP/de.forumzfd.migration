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
    // contact_id and event_id can not be empty
    if (empty($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Source participant '.$this->_sourceData['id'].' does not have a contact_id, not migrated');
      return FALSE;
    }
    if (empty($this->_sourceData['event_id'])) {
      $this->_logger->logMessage('Error', 'Source participant '.$this->_sourceData['id'].' does not have an event_id, not migrated');
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
    return TRUE;
  }
}
