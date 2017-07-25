<?php

/**
 * Class for ForumZFD Entity Tag Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_EntityTag extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      if ($this->contactExists($this->_sourceData['entity_id'])) {
        $apiParams = $this->setApiParams();
        // if not already exists
        $count = civicrm_api3('EntityTag', 'getcount', array(
          'entity_table' => 'civicrm_contact',
          'entity_id' => $apiParams['entity_id'],
          'tag_id' => $apiParams['tag_id'],
        ));
        if ($count == 0) {
          try {
            $newEntityTag = civicrm_api3('EntityTag', 'create', $apiParams);
            return $newEntityTag;
          }
          catch (CiviCRM_API3_Exception $ex) {
            $this->_logger->logMessage('Error', 'Could not create or update entity tag '.$this->_sourceData['tag_id'].' '
              .' for contact '.$this->_sourceData['entity_id'].'. Error from API EntityTag create: '.$ex->getMessage());
          }
        }
      } else {
        $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
          .$this->_sourceData['entity_id'].' for entity tag, not migrated.');
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
    $removes = array('new_entity_tag_id', 'id', '*_options', 'is_processed');
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
   * Implementation of method to validate if source data is good enough for entity_tag
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['entity_id'])) {
      $this->_logger->logMessage('Error', 'EntityTag has no entity_id, not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }

    // new contact has to exist
    $newContactId = $this->findNewContactId($this->_sourceData['entity_id']);
    if (empty($newContactId)) {
      $this->_logger->logMessage('Error', 'No new contact_id found for entity tag with id '.$this->_sourceData['id'].', entity tag not migrated');
      return FALSE;
    } else {
      $this->_sourceData['entity_id'] = $newContactId;
    }

    if (empty($this->_sourceData['tag_id'])) {
      $this->_logger->logMessage('Error', 'EntityTag has no tag_id, not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }
    return TRUE;
  }
}