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
    if (isset($this->_sourceData['group_type'])) {
      $emptyTest = array(
        CRM_Core_DAO::VALUE_SEPARATOR."1".CRM_Core_DAO::VALUE_SEPARATOR,
        CRM_Core_DAO::VALUE_SEPARATOR."2".CRM_Core_DAO::VALUE_SEPARATOR,
        CRM_Core_DAO::VALUE_SEPARATOR."1".CRM_Core_DAO::VALUE_SEPARATOR."2".CRM_Core_DAO::VALUE_SEPARATOR,
        );
      if (!in_array($this->_sourceData['group_type'], $emptyTest)) {
        unset($this->_sourceData['group_type']);
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
    // check if created_id exists, if not leave empty
    if (isset($this->_sourceData['created_id']) && !empty($this->_sourceData['created_id'])) {
      $createdCount = civicrm_api3('Contact', 'getcount', array('id' => $this->_sourceData['created_id']));
      if ($createdCount != 1) {
        unset($this->_sourceData['created_id']);
      }
    } else {
      if (isset($this->_sourceData['created_id'])) {
        unset($this->_sourceData['created_id']);
      }
    }
    // ignore group 1 (Administrators)
    if ($this->_sourceData['id'] == 1) {
      $this->_logger->logMessage('Warning', 'Source group '.$this->_sourceData['id'].' (Administrators) ignored');
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

  /**
   * Method to add group custom data
   */
  public static function addCustomData() {
    // specific logger
    $logger = new CRM_Migration_Logger('group_custom_data');
    // retrieve all custom tables for group
    $query = "SELECT * FROM forumzfd_custom_group WHERE extends = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array('Group', 'String'),
    ));
    while ($dao->fetch()) {
      $group = new CRM_Migration_Group('group_custom_data', $dao, $logger);
      $group->createCustomGroupIfNotExists($group->_sourceData);
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $group->generateMigrateTableName($group->_sourceData['table_name']);
      $daoCustomData = $group->getCustomDataDao($migrateTableName);
      $columns = $group->getCustomDataColumns($daoCustomData, $group->_sourceData['table_name']);
      while ($daoCustomData->fetch()) {
        // find new group id
        $newGroupId = $group->findNewGroupId($daoCustomData->entity_id);
        if ($newGroupId) {
          $dao->entity_id = $newGroupId;
          $group->insertCustomData($daoCustomData, $group->_sourceData['table_name'], $columns);
        } else {
          $logger->logMessage('Error', 'Could not find or create a new contact for '.$daoCustomData->entity_id.' and table name '
            .$group->_sourceData['table_name'].', custom data not migrated.');
        }
      }
    }
  }

}
