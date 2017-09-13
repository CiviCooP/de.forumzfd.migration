<?php

/**
 * Class for ForumZFD Note Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Note extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      $apiParams = $this->setApiParams();
      try {
        $newNote = civicrm_api3('Note', 'create', $apiParams);
        return $newNote;
      }
      catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Could not create or update note '.$this->_sourceData['id'].' '
          .' for contact '.$this->_sourceData['contact_id'].'. Error from API Note create: '.$ex->getMessage());
      }
    } else {
      $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
        .$this->_sourceData['contact_id'].' for note, not migrated.');
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
    $removes = array('new_note_id', 'id', '*_options', 'is_processed');
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
   * Implementation of method to validate if source data is good enough for note
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['entity_id'])) {
      $this->_logger->logMessage('Error', 'Note has no entity_id, not migrated. Source note id is '.$this->_sourceData['id']);
      return FALSE;
    }
    // entity_id should exists, depending on entity_table
    switch ($this->_sourceData['entity_table']) {
      case 'civicrm_contact':
        break;
      case 'civicrm_contribution':
        $newContributionId = $this->findNewContribution($this->_sourceData['entity_id']);
        if ($newContributionId) {
          $this->_sourceData['entity_id'] = $newContributionId;
        } else {
          $this->_logger->logMessage('Error', 'Could not find new contribution for note with id '.$this->_sourceData['id'].', not migrated');
          return FALSE;
        }
        break;
      case 'civicrm_participant':
        $newParticipantId = $this->findNewPatricipantId($this->_sourceData['entity_id']);
        if ($newParticipantId) {
          $this->_sourceData['entity_id'] = $newParticipantId;
        } else {
          $this->_logger->logMessage('Error', 'Could not find new participant for note with id '.$this->_sourceData['id'].', not migrated');
          return FALSE;
        }
        break;
      case 'civicrm_relationship':
        $newRelationshipId = $this->findNewRelationshipId($this->_sourceData['entity_id']);
        if ($newRelationshipId) {
          $this->_sourceData['entity_id'] = $newRelationshipId;
        } else {
          $this->_logger->logMessage('Error', 'Could not find new relationship for note with id '.$this->_sourceData['id'].', not migrated');
          return FALSE;
        }
        break;
      default:
        $this->_logger->logMessage('Error', 'entity table '.$this->_sourceData['entity_table'],' not foreseen for note with id '.$this->_sourceData['id'].', not migrated');
        return FALSE;
        break;
    }
    if (empty($this->_sourceData['note'])) {
      $this->_logger->logMessage('Error', 'Note is empty, not migrated. Source note id is '.$this->_sourceData['id']);
      return FALSE;
    }
    return TRUE;
  }
}