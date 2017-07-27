<?php

/**
 * Class for ForumZFD ActivityType Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 4 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Activity extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
    try {
      $apiParams = $this->setApiParams();
      $created = civicrm_api3('Activity', 'create', $apiParams);
      return $created;
    } catch (CiviCRM_API3_Exception $ex) {
      $message = 'Could not add or update activity with id '.$this->_sourceData['id'].', error from API Activity create: '.$ex->getMessage();
      $this->_logger->logMessage('Error', $message);
      return FALSE;
    }
  }
    return FALSE;
  }

  /**
   * Method to set the params for the activity create
   * @return array
   */
  private function setApiParams() {
    $apiParams = $this->_sourceData;
    $removes = array('id', 'activity_id', 'activity_type_name', 'original_id');
    foreach ($apiParams as $apiParamKey => $apiParamValue) {
      if (is_array($apiParamValue)) {
        unset($apiParams[$apiParamKey]);
      }
      if (in_array($apiParamKey, $removes)) {
        unset($apiParams[$apiParamKey]);
      }
      if (empty($apiParamValue)) {
        unset($apiParams[$apiParamKey]);
      }
    }
    // retrieve source_record_id for memberships, or ignore
    if (isset($apiParams['source_record_id']) && !empty($apiParams['source_record_id'])) {
      if ($apiParams['activity_type_id'] == 7 || $apiParams['activity_type_id'] == 8 || $apiParams['activity_type_id'] == 62 || $apiParams['activity_type_id'] == 63) {
        $newMembershipId = $this->findNewMembershipId($apiParams['source_record_id']);
        if ($newMembershipId) {
          $apiParams['source_record_id'] = $newMembershipId;
        } else {
          unset($apiParams['source_record_id']);
        }
      } else {
        unset($apiParams['source_record_id']);
      }
    }
    // find new campaign id if required
    if (isset($apiParams['campaign_id']) && !empty($apiParams['campaign_id'])) {
      $newCampaignId = $this->findNewCampaignId($apiParams['campaign_id']);
      if ($newCampaignId) {
        $apiParams['campaign_id'] = $newCampaignId;
      } else {
        $this->_logger->logMessage('Warning', 'No new campaign found for activity '.$this->_sourceData['id']
          .' with campaign '.$apiParams['campaign_id'].', campaign removed from migrated activity');
        unset($apiParams['campaign_id']);
      }
    }
    // get source, assignee(s) and target(s)
    $this->addActivityContactParams($apiParams);
    return $apiParams;
  }

  /**
   * Method to add the activity contacts with source activity id and put them into apiParams
   *
   * @param $apiParams
   */
  private function addActivityContactParams(&$apiParams) {
    $sourceContactIds = $this->getActivityContacts('source');
    if (!empty($sourceContactIds)) {
      $apiParams['source_contact_id'] = $sourceContactIds;
    }
    $assigneeContactIds = $this->getActivityContacts('assignee');
    if (!empty($assigneeContactIds)) {
      $apiParams['assignee_id'] = $assigneeContactIds;
    }
    $targetContactIds = $this->getActivityContacts('target');
    if (!empty($targetContactIds)) {
      $apiParams['target_id'] = $targetContactIds;
    }
  }

  /**
   * Method to get the activity contacts
   *
   * @param string $type
   * @return array|int
   */
  private function getActivityContacts($type) {
    $contactIds = array();
    $recordType = NULL;
    switch ($type) {
      case 'source':
        $query = 'SELECT contact_id FROM forumzfd_activity_contact WHERE activity_id = %1 AND record_type_id = %2 LIMIT 1';
        $sourceContactId = CRM_Core_DAO::singleValueQuery($query, array(
          1 => array($this->_sourceData['id'], 'Integer'),
          2 => array($this->getSourceRecordTypeId(), 'Integer'),
        ));
        if (!empty($sourceContactId)) {
          $newContactId = $this->findNewContactId($sourceContactId);
          if ($newContactId) {
            return $newContactId;
          }
        }
        break;
      case 'target':
        $recordType = $this->getTargetRecordTypeId();
        break;
      case 'assignee':
        $recordType = $this->getAssigneeRecordTypeId();
        break;
    }
    if ($recordType) {
      $query = 'SELECT contact_id FROM forumzfd_activity_contact WHERE activity_id = %1 AND record_type_id = %2';
      $dao = CRM_Core_DAO::executeQuery($query, array(
        1 => array($this->_sourceData['id'], 'Integer'),
        2 => array($recordType, 'Integer'),
      ));
      while ($dao->fetch()) {
        $newContactId = $this->findNewContactId($dao->contact_id);
        if ($newContactId) {
          $contactIds[] = $newContactId;
        }
      }
    }
    return $contactIds;
  }

  /**
   * Method to validate sourceData for activity
   */
  function validSourceData() {
    // required fields activity_type_id
    if (!isset($this->_sourceData['activity_type_id']) || empty($this->_sourceData['activity_type_id'])) {
      $this->_logger->logMessage('Error', 'No activity type id found for activity '.$this->_sourceData['id'].', not migrated.');
      return FALSE;
    }
    // required fields activity_type_name
    if (!isset($this->_sourceData['activity_type_name']) || empty($this->_sourceData['activity_type_name'])) {
      $this->_logger->logMessage('Error', 'No activity type name found for activity '.$this->_sourceData['id'].', not migrated.');
      return FALSE;
    }
    // get new activity type id
    $newActivityTypeId = $this->findNewActivityTypeIdWithName($this->_sourceData['activity_type_name']);
    if ($newActivityTypeId) {
      $this->_sourceData['activity_type_id'] = $newActivityTypeId;
    } else {
      $this->_logger->logMessage('Error', 'No activity type id found for activity type name '.$this->_sourceData['activity_type_name']
        .' with activity '.$this->_sourceData['id'].', not migrated.');
      return FALSE;
    }
    // default status_id if not present
    if (!isset($this->_sourceData['status_id']) || empty($this->_sourceData['status_id'])) {
      $this->_sourceData['status_id'] = $this->getCompletedActivityStatusId();
    }
    return TRUE;
  }

  /**
   * Method to add activity custom data
   */
  public static function addCustomData() {
    // specific logger
    $logger = new CRM_Migration_Logger('activity_custom_data');
    // retrieve all custom tables for activity
    $query = "SELECT * FROM forumzfd_custom_group WHERE extends = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(
      1 => array('Activity', 'String'),
    ));
    while ($dao->fetch()) {
      $activity = new CRM_Migration_Activity('activity_custom_data', $dao, $logger);
      $activity->createCustomGroupIfNotExists($activity->_sourceData);
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $activity->generateMigrateTableName($activity->_sourceData['table_name']);
      $daoCustomData = $activity->getCustomDataDao($migrateTableName);
      $columns = $activity->getCustomDataColumns($daoCustomData, $activity->_sourceData['table_name']);
      while ($daoCustomData->fetch()) {
        // find new activity id
        $newActivityId = $activity->findNewActivityId($daoCustomData->entity_id);
        if ($newActivityId) {
          $dao->entity_id = $newActivityId;
          $activity->insertCustomData($daoCustomData, $activity->_sourceData['table_name'], $columns);
        } else {
          $logger->logMessage('Error', 'Could not find or create a new contact for '.$daoCustomData->entity_id.' and table name '
            .$activity->_sourceData['table_name'].', custom data not migrated.');
        }
      }
    }
  }
}