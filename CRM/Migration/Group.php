<?php

/**
 * Class for ForumZFD Group Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 6 July 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Group extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Group', 'create', $apiParams);
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update group, error from API Group create: ' . $ex->getMessage();
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
    // find created and modified contact if found
    if (isset($this->_sourceData['created_id'])) {
      $newCreatedId = $this->findNewContactId($this->_sourceData['created_id']);
      if ($newCreatedId) {
        $this->_sourceData['created_id'] = $newCreatedId;
      } else {
        unset($this->_sourceData['created_id']);
      }
    }
    if (isset($this->_sourceData['modified_id'])) {
      $newModifiedId = $this->findNewContactId($this->_sourceData['modified_id']);
      if ($newModifiedId) {
        $this->_sourceData['modified_id'] = $newModifiedId;
      } else {
        unset($this->_sourceData['modified_id']);
      }
    }
    $apiParams = $this->_sourceData;
    // fix parents later
    $remove = array('id', 'parents');
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
    // title can not be empty
    if (!isset($this->_sourceData['title']) || empty($this->_sourceData['title'])) {
      $this->_logger->logMessage('Error', 'Source group '.$this->_sourceData['id'].' does not have a title, not migrated');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to fix the group parents at the end
   *
   * @param $sourceData
   */
  public function fixParents($sourceData) {
    // find new group id
    $newGroupId = $this->findNewGroupId($sourceData->id);
    // parents is comma separated string, find new one for each source one and use GroupNesting API
    $oldParents = explode(',', $sourceData->parents);
    foreach ($oldParents as $oldParent) {
      $newParentId = $this->findNewGroupId($oldParent);
      if ($newParentId) {
        try {
          civicrm_api3('GroupNesting', 'create', array(
            'child_group_id' => $newGroupId,
            'parent_group_id' => $newParentId
          ));
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Warning', 'Error adding group parent for '.$oldParent.' with group '
            .$sourceData->id.', group migrated but parent ignored');
        }
      } else {
        $this->_logger->logMessage('Warning', 'Could not find group parent for '.$oldParent.' with group '
          .$sourceData->id.', group migrated but parent ignored');
      }
    }
  }

}
