<?php

/**
 * Abstract class for ForumZFD Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
abstract class CRM_Migration_ForumZfd {

  protected $_logger = NULL;
  protected $_sourceData = array();
  protected $_entity = NULL;
  protected $_targetRecordTypeId = NULL;
  protected $_sourceRecordTypeId = NULL;
  protected $_assigneeRecordTypeId = NULL;
  protected $_completedActivityStatusId = NULL;

  /**
   * CRM_Migratie_ForumZfd constructor.
   *
   * @param string $entity
   * @param object $sourceData
   * @param object $logger
   * @throws Exception when entity invalid
   */
  public function __construct($entity, $sourceData = NULL, $logger = NULL) {
    $entity = strtolower($entity);
    if (!$this->entityCanBeMigrated($entity)) {
      throw new Exception('Entity '.$entity.' can not be migrated.');
    } else {
      $this->_entity = $entity;
      $this->_sourceData = (array)$sourceData;
      $this->cleanSourceData();
      $this->_logger = $logger;
    }
    try {
      $this->_completedActivityStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_status',
        'name' => 'Completed',
        'return' => 'value',
      ));
      $recordTypes = civicrm_api3('OptionValue', 'get', array(
        'option_group_id' => 'activity_contacts',
      ));
      foreach ($recordTypes['values'] as $recordType) {
        switch ($recordType['name']) {
          case 'Activity Assignees':
            $this->_assigneeRecordTypeId = $recordType['value'];
            break;
          case 'Activity Source':
            $this->_sourceRecordTypeId = $recordType['value'];
            break;
          case 'Activity Targets':
            $this->_targetRecordTypeId = $recordType['value'];
            break;
        }
      }
    }
    catch (CiviCRM_API3_Exception $ex) {

    }
  }

  /**
   * Method to remove DAO parts of source data and unnecessary is processed element
   *
   * @access private
   */
  private function cleanSourceData() {
    foreach ($this->_sourceData as $sourceKey => $sourceValue) {
      if ($sourceKey == 'N' || substr($sourceKey, 0, 1) == '_') {
        unset($this->_sourceData[$sourceKey]);
      }
    }
    if (isset($this->_sourceData['is_processed'])) {
      unset($this->_sourceData['is_processed']);
    }
  }

  /**
   * Method to check if entity can be migrated
   * 
   * @param string $entity
   * @return bool
   * @access private
   */
  private function entityCanBeMigrated($entity) {
    $validEntities = array(
      'address',
      'activity',
      'activity_custom_data',
      'campaign',
      'contact',
      'contact_custom_data',
      'contribution',
      'email',
      'employer',
      'entity_tag',
      'event',
      'group',
      'group_custom_data',
      'group_contact',
      'membership',
      'note',
      'option_value',
      'participant',
      'participant_custom_data',
      'phone',
      'relationship',
      'website');
    if (!in_array($entity, $validEntities)) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * Abstract method to migrate incoming data
   */
  abstract function migrate();

  /**
   * Abstract Method to validate if source data is good enough
   */
  abstract function validSourceData();

  /**
   * Check if is_primary is set to 1, it can actually be set and otherwise set to 0 and log
   *
   * @access protected
   */
  protected function checkIsPrimary() {
    if ($this->_sourceData['is_primary'] == 1) {
      $countQuery = 'SELECT COUNT(*) FROM civicrm_'.$this->_entity.' WHERE contact_id = %1 AND is_primary = %2';
      $countParams = array(
        1 => array($this->_sourceData['contact_id'], 'Integer'),
        2 => array(1, 'Integer')
      );
      $countPrimary = CRM_Core_DAO::singleValueQuery($countQuery, $countParams);
      if ($countPrimary > 0) {
        $this->_sourceData['is_primary'] = 0;
      }
    }
  }

  /**
   * Method to check if contact already exists
   * 
   * @param int $contactId
   * @return bool
   * @access protected
   */
  protected function contactExists($contactId) {
    $query = 'SELECT COUNT(*) FROM civicrm_contact WHERE id = %1';
    $params = array(1 => array($contactId, 'Integer'));
    $countContact = CRM_Core_DAO::singleValueQuery($query, $params);
    if ($countContact == 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * Method to check if location type is valid
   *
   * @return bool
   * @access protected
   */
  protected function validLocationType() {
    if (!isset($this->_sourceData['location_type_id'])) {
      $this->_logger->logMessage('Warning', $this->_entity.' of contact_id '.$this->_sourceData['contact_id']
        .'has no location_type_id, location_type_id 1 used');
      $this->_sourceData['location_type_id'] = 1;
    } else {
      try {
        $count = civicrm_api3('LocationType', 'getcount', array('id' => $this->_sourceData['location_type_id']));
        if ($count != 1) {
          $this->_logger->logMessage('Warning', $this->_entity.' with contact_id ' . $this->_sourceData['contact_id']
            . ' does not have a valid location_type_id (' . $count . ' of ' . $this->_sourceData['location_type_id']
            . 'found), created with location_type_id 1');
          $this->_sourceData['location_type_id'] = 1;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Error retrieving location type id from CiviCRM for '.$this->_entity
          .' with contact_id '. $this->_sourceData['contact_id'] . ' and location_type_id' 
          . $this->_sourceData['location_type_id']
          . ', ignored. Error from API LocationType getcount : ' . $ex->getMessage());
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to check if membership type is valid
   *
   * @return bool
   * @access protected
   */
  protected function validMembershipType() {
    if (!isset($this->_sourceData['membership_type_id'])) {
      $this->_logger->logMessage('Error', $this->_entity.' of contact_id '.$this->_sourceData['contact_id']
        .'has no membership_type_id, not migrated');
      return FALSE;
    } else {
      $valid = array(1, 2, 3, 5, 6, 7);
      if (!in_array($this->_sourceData['membership_type_id'], $valid)) {
        $this->_logger->logMessage('Error', $this->_entity.' of contact_id '.$this->_sourceData['contact_id']
          .'has no valid membership_type_id, not migrated');
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to check if membership status is valid
   *
   * @return bool
   * @access protected
   */
  protected function validMembershipStatus() {
    if (!isset($this->_sourceData['status_id'])) {
      $this->_logger->logMessage('Error', $this->_entity.' of contact_id '.$this->_sourceData['contact_id']
        .'has no status_id, not migrated');
      return FALSE;
    } else {
      try {
        $count = civicrm_api3('MembershipStatus', 'getcount', array('id' => $this->_sourceData['status_id']));
        if ($count != 1) {
          $this->_logger->logMessage('Error', $this->_entity.' with contact_id ' . $this->_sourceData['contact_id']
            . ' does not have a valid status_id (' . $count . ' of ' . $this->_sourceData['status_id']
            . 'found), not migrated');
          return FALSE;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Error retrieving status_id from CiviCRM for '.$this->_entity
          .' with contact_id '. $this->_sourceData['contact_id'] . ' and status_id'
          . $this->_sourceData['status_id']
          . ', ignored. Error from API MembershipStatus getcount : ' . $ex->getMessage());
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Method to find the new membership id with the old one
   *
   * @param $sourceMembershipId
   * @return null|string
   */
  protected function findNewMembershipId($sourceMembershipId) {
    $query = 'SELECT new_membership_id FROM forumzfd_membership WHERE id = '.$sourceMembershipId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new campaign id with the old one
   *
   * @param $sourceCampaignId
   * @return null|string
   */
  protected function findNewCampaignId($sourceCampaignId) {
    $query = 'SELECT new_campaign_id FROM forumzfd_campaign WHERE id = '.$sourceCampaignId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new contact id with the old one
   *
   * @param $sourceContactId
   * @return null|string
   */
  protected function findNewContactId($sourceContactId) {
    $query = 'SELECT new_contact_id FROM forumzfd_contact WHERE id = '.$sourceContactId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new activity id with the old one
   *
   * @param $sourceActivityId
   * @return null|string
   */
  protected function findNewActivityId($sourceActivityId) {
    $query = 'SELECT new_activity_id FROM forumzfd_activity WHERE id = '.$sourceActivityId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new participant id with the old one
   *
   * @param $sourceParticipantId
   * @return null|string
   */
  protected function findNewPatricipantId($sourceParticipantId) {
    $query = 'SELECT new_participant_id FROM forumzfd_participant WHERE id = '.$sourceParticipantId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new relationship id with the old one
   *
   * @param $sourceRelationshipId
   * @return null|string
   */
  protected function findNewRelationshipId($sourceRelationshipId) {
    $query = 'SELECT new_relationship_id FROM forumzfd_relationship WHERE id = '.$sourceRelationshipId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new contribution id with the old one
   *
   * @param $sourceContributionId
   * @return null|string
   */
  protected function findNewContribution($sourceContributionId) {
    $query = 'SELECT new_contribution_id FROM forumzfd_contribution WHERE id = '.$sourceContributionId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new event id with the old one
   *
   * @param $sourceEventId
   * @return null|string
   */
  protected function findNewEventId($sourceEventId) {
    $query = 'SELECT new_event_id FROM forumzfd_event WHERE id = '.$sourceEventId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find the new group id with the old one
   *
   * @param $sourceGroupId
   * @return null|string
   */
  protected function findNewGroupId($sourceGroupId) {
    $query = 'SELECT new_group_id FROM forumzfd_group WHERE id = '.$sourceGroupId;
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Method to find new activity type id
   *
   * @param $activityTypeName
   * @return array|bool
   */
  protected function findNewActivityTypeIdWithName($activityTypeName) {
    try {
      return civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_type',
        'name' => $activityTypeName,
        'return' => 'value',
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to find new tag id with name
   *
   * @param $tagName
   * @return array|bool
   */
  protected function findNewTagIdWithName($tagName) {
    try {
      return civicrm_api3('Tag', 'getvalue', array(
        'name' => $tagName,
        'return' => 'id',
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Method to count the financial type
   *
   * @param $financialTypeId
   * @return bool
   */
  protected function countFinancialTypeId($financialTypeId) {
    try {
      return civicrm_api3('FinancialType', 'getcount', array(
        'id' => $financialTypeId,
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Error trying to check financial type '.$financialTypeId
        .' with API OptionValue getcount. Error from API: '.$ex->getMessage());
    }
  }

  /**
   * Method to get the migration table name from the custom table name
   *
   * @param string $tableName
   * @return string
   */
  protected function generateMigrateTableName($tableName) {
    $newName = $tableName;
    $nameParts = explode('civicrm_value_', $tableName);
    if (isset($nameParts[1])) {
      $newName = 'forumzfd_value_'.$nameParts[1];
    }
    return $newName;
  }

  /**
   * Method to get the DAO with data from the Custom Table name
   *
   * @param $tableName
   * @return bool|CRM_Core_DAO|object
   */
  protected function getCustomDataDao($tableName) {
    if (CRM_Core_DAO::checkTableExists($tableName)) {
      return CRM_Core_DAO::executeQuery("SELECT * FROM ".$tableName." WHERE is_processed = 0");
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get all the relevant columns for an insert of custom data.
   * This is done by walking through the dao properties and checking if there
   * is a column name with the same name in the target table
   *
   * @param $dao
   * @param $tableName
   * @return array
   */
  protected function getCustomDataColumns($dao, $tableName) {
    $columns = array();
    // add a param for each incoming $dao property that also has a column in the target table
    if (!is_object($dao)) {
      $this->_logger->logMessage('Warning', 'Could not find a dao for custom table '.$tableName);
    } else {
      $dao->fetch();
      $daoProperties = get_object_vars($dao);
      foreach ($daoProperties as $daoProperty => $daoValue) {
        if (substr($daoProperty,0,1) != '_' && $daoProperty != 'id') {
          if (CRM_Core_DAO::checkFieldExists($tableName, $daoProperty)) {
            $column = array(
              'name' => $daoProperty,
              'type' => $this->getCustomColumnType($daoProperty, $tableName),
            );
            $columns[] = $column;
          }
        }
      }
      // just to be sure, remove id from column list if it is there. Not needed as we are going to insert
      foreach ($columns as $columnId => $columnName) {
        if ($columnName == 'id') {
          unset($columns[$columnId]);
        }
      }
      return $columns;
    }
  }

  /**
   * Method to insert custom data into custom table
   *
   * @param $dao
   * @param $tableName
   * @param $columns
   * @throws Exception when not able to insert into table
   * @return int
   */
  protected function insertCustomData($dao, $tableName, $columns) {
    // get columns to ignore
    $ignoreColumns = CRM_Migration_Config::singleton()->getCustomFieldsToIgnore($tableName);
    // only insert if not exists yet
    if ($this->customDataExists($dao, $tableName, $columns) == FALSE) {
      $indexArray = array();
      $insertParams = array();
      $columnNames = array();
      foreach ($columns as $columnKey => $column) {
        // only if custom field is not an ignore one
        if (!in_array($column['name'], $ignoreColumns)) {
          $property = $column['name'];
          if (!empty($dao->$property)) {
            $index = $columnKey + 1;
            $indexArray[] = '%' . $index;
            $columnNames[] = $column['name'];
            $insertParams[$index] = array($dao->$property, $column['type']);
          }
        }
        $insertQuery = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $columnNames) . ') VALUES(' . implode(', ', $indexArray) . ')';
        try {
          CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
          // after insert, set is_processed and new_id in migrate file
          $query = 'SELECT * FROM ' . $tableName . ' ORDER BY ID DESC LIMIT 1';
          $result = CRM_Core_DAO::executeQuery($query);
          if ($result->fetch()) {
            $migrateTableName = $this->generateMigrateTableName($tableName);
            $updateQuery = 'UPDATE ' . $migrateTableName . ' SET is_processed = %1, new_id = %2 WHERE id = %3';
            $updateParams = array(
              1 => array(1, 'Integer',),
              2 => array($result->id, 'Integer',),
              3 => array($dao->id, 'Integer',),
            );
            CRM_Core_DAO::executeQuery($updateQuery, $updateParams);
          }
        } catch (Exception $ex) {
          $this->_logger->logMessage('Warning', 'Could not add custom data in table ' . $tableName . ', error from CRM_Core_DAO: ' . $ex->getMessage());
        }
      }
    }
  }

  /**
   * Method to check if custom data already exists
   *
   * @param $dao
   * @param $tableName
   * @param $columns
   * @return bool
   */
  protected function customDataExists($dao, $tableName, $columns) {
    $whereClauses = array(1 => 'entity_id = %1');
    $index = 1;
    $whereParams = array(1 => array($dao->entity_id, 'Integer'));
    foreach ($columns as $columnKey => $columnValues) {
      if ($columnValues['name'] != 'entity_id') {
        $property = $columnValues['name'];
        if (!empty($dao->$property)) {
          $index++;
          $whereClauses[] = $columnValues['name'] . ' = %' . $index;
          $whereParams[$index] = array($dao->$property, $columnValues['type']);
        }
      }
    }
    $query = "SELECT COUNT(*) FROM ".$tableName." WHERE ".implode(' AND ', $whereClauses);
    $customDataCount = CRM_Core_DAO::singleValueQuery($query, $whereParams);
    if ($customDataCount != 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Method to get the column type for the query
   *
   * @param $columnName
   * @param $tableName
   * @return string
   */
  protected function getCustomColumnType($columnName, $tableName) {
    $dbName = CRM_Core_DAO::getDatabaseName();
    $query = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
      WHERE table_name = '".$tableName."' AND COLUMN_NAME = '".$columnName."' AND TABLE_SCHEMA = '".$dbName."'";
    $dataType = strtolower(CRM_Core_DAO::singleValueQuery($query));
    switch ($dataType) {
      case 'int':
        return 'Integer';
        break;
      case 'tinyint':
        return 'Integer';
        break;
      case 'decimal':
        return 'Money';
        break;
      default:
        return 'String';
        break;
    }
  }

  /**
   * Method to find or create custom group
   *
   * @param $sourceData
   * @return array|bool
   */
  protected function createCustomGroupIfNotExists($sourceData) {
    try {
      $customGroup = civicrm_api3('CustomGroup', 'getsingle', array(
        'name' => $sourceData['name'],
         'extends' => $sourceData['extends'],
        ));
      $this->createCustomFieldsIfNotExist($customGroup['name'], $customGroup['extends']);
      return $customGroup;
    }
    catch (CiviCRM_API3_Exception $ex) {
      $createParams = $this->_sourceData;
      unset($createParams['id']);
      unset($createParams['new_custom_id']);
      foreach ($createParams as $createParamKey => $createParam) {
        if (is_array($createParam)) {
          unset($createParams[$createParamKey]);
        }
      }
      unset($createParams['new_custom_group_id']);
      // get new contact id for created if possible
      if (!isset($createParams['created_id']) ||empty($createParams['created_id'])) {
        $createParams['created_id'] = CRM_Core_Session::singleton()->get('userID');
      }
      if (!isset($createParams['created_date'])) {
        $createParams['created_date'] = date('Ymd');
      }
      try {
        $customGroup = civicrm_api3('CustomGroup', 'create', $createParams);
        $this->createCustomFieldsIfNotExist($customGroup['values'][$customGroup['id']]['name'], $customGroup['values'][$customGroup['id']]['extends']);
        return $customGroup['values'][$customGroup['id']];
      }
      catch (CiviCRM_API3_Exception $ex) {
        return FALSE;
      }
    }
  }
  /**
   * Method to find or create custom fields for custom group
   *
   * @param $customGroupName
   * @param $extends
   */
  protected function createCustomFieldsIfNotExist($customGroupName, $extends) {
    // first find all source fields for custom group
    $query = "SELECT id FROM forumzfd_custom_group WHERE name = %1 AND extends = %2";
    $sourceCustomGroupId = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($customGroupName, 'String'),
      2 => array($extends, 'String'),
    ));
    if ($sourceCustomGroupId) {
      $query = "SELECT * FROM forumzfd_custom_field WHERE custom_group_id = %1";
      $sourceCustomFields = CRM_Core_DAO::executeQuery($query, array(
        1 => array($sourceCustomGroupId, 'Integer'),
      ));
      while ($sourceCustomFields->fetch()) {
        if ($this->customFieldExists($customGroupName, $sourceCustomFields->name) == FALSE) {
          $this->createCustomField($customGroupName, $extends, $sourceCustomFields);
        }
      }
    }
  }

  /**
   * Method to create custom field if not exists yet
   * @param $customGroupName
   * @param $extends
   * @param $sourceCustomFields
   */
  protected function createCustomField($customGroupName, $extends, $sourceCustomFields) {
    // find new custom group
    $query = "SELECT id FROM civicrm_custom_group WHERE extends = %1 AND name = %2";
    $newCustomGroupId = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($extends, 'String'),
      2 => array($customGroupName, 'String'),
    ));
    if ($newCustomGroupId) {
      $createParams = get_object_vars($sourceCustomFields);
      $removes = array('id', 'custom_group_id', 'N', 'target_custom_group_id', 'new_custom_field_id');
      // remove all elements starting with '_' and $removes
      foreach ($createParams as $createParamKey => $createParamValue) {
        if (substr($createParamKey, 0, 1) == '_') {
          unset($createParams[$createParamKey]);
        }
        if (in_array($createParamKey, $removes)) {
          unset($createParams[$createParamKey]);
        }
        // remove all empty ones
        if (empty($createParamValue)) {
          unset($createParams[$createParamKey]);
        }
      }
      $createParams['custom_group_id'] = $newCustomGroupId;
      civicrm_api3('CustomField', 'create', $createParams);
    }
  }

  /**
   * Method to check if custom field exists
   *
   * @param $customGroupName
   * @param $customFieldName
   * @return bool
   */
  protected function customFieldExists($customGroupName, $customFieldName) {
    try {
      $count = $customFieldId = civicrm_api3('CustomField', 'getcount', array(
        'custom_group_id' => $customGroupName,
        'name' => $customFieldName,
      ));
      if ($count == 0) {
        return FALSE;
      } else {
        return TRUE;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
  }

  /**
   * Getter for assignee record type id for activity
   *
   * @return null
   */
  protected function getAssigneeRecordTypeId() {
    return $this->_assigneeRecordTypeId;
  }

  /**
   * Getter for source record type id for activity
   *
   * @return null
   */
  protected function getSourceRecordTypeId() {
    return $this->_sourceRecordTypeId;
  }

  /**
   * Getter for target record type id for activity
   *
   * @return null
   */
  protected function getTargetRecordTypeId() {
    return $this->_targetRecordTypeId;
  }

  /**
   * Getter for completed activity status id
   *
   * @return null
   */
  protected function getCompletedActivityStatusId() {
    return $this->_completedActivityStatusId;
  }

  /**
   * Method to get the new financial type id with the old one
   *
   * @param $financialTypeId
   * @return array|null
   */
  protected function convertFinancialType($financialTypeId) {
    $newFinancialTypeId = CRM_Migration_Config::singleton()->getDefaultFinancialTypeId();
    $sourceFinancialTypes = array(
      1 => 'Spende',
      2 => 'Mitgliedsbeitrag',
      4 => 'Teilnahmegebür',
      5 => 'Sachzuwendung',
      6 => 'Friedenslaufspende',
      7 => 'Förderbeitrag',
      8 => 'Mitgliedsbeitrag-Organisationen',
      9 => 'Friedenslauf-Sponsoring',
      12 => 'Rücklastschrift',
      13 => 'Lastschriftfehler',
      16 => 'Geldauflage',
      18 => 'Spende an Stiftung',
      19 => 'Spende der Stiftung an e.V.'
    );
    if (isset($sourceFinancialTypes[$financialTypeId])) {
      $params = array(
        'name' => $sourceFinancialTypes[$financialTypeId],
        'return' => 'id'
      );
      try {
        $newFinancialTypeId = civicrm_api3('FinancialType', 'getvalue', $params);
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
      return $newFinancialTypeId;
    }


  }
}