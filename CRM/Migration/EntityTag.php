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
    $removes = array('id', '*_options', 'is_processed', 'tag_name');
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
      $this->_logger->logMessage('Error', 'EntityTag with id '.$this->_sourceData['id'].' has no entity_id, not migrated.');
      return FALSE;
    }
    if (empty($this->_sourceData['tag_id'])) {
      $this->_logger->logMessage('Error', 'EntityTag with id '.$this->_sourceData['id'].' has no tag_id, not migrated.');
      return FALSE;
    }
    if (empty($this->_sourceData['tag_name'])) {
      $this->_logger->logMessage('Error', 'EntityTag with id '.$this->_sourceData['id'].' has no tag_name, not migrated.');
      return FALSE;
    }
    // get new tag id with tag name
    $newTagId = $this->findNewTagIdWithName($this->_sourceData['tag_name']);
    if ($newTagId) {
      $this->_sourceData['tag_id'] = $newTagId;
    } else {
      $this->_logger->logMessage('Error', 'No new tag found for entity tag with id . '.$this->_sourceData['id'].', not migrated.');
    }
    return TRUE;
  }
}