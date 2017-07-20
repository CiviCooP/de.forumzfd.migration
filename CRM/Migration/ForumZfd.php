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
      'campaign',
      'contact',
      'contact_custom_data',
      'contribution',
      'email',
      'employer',
      'entity_tag',
      'event',
      'group',
      'groupcontact',
      'note',
      'option_value',
      'participant',
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
      try {
        $count = civicrm_api3('MembershipType', 'getcount', array('id' => $this->_sourceData['membership_type_id']));
        if ($count != 1) {
          $this->_logger->logMessage('Error', $this->_entity.' with contact_id ' . $this->_sourceData['contact_id']
            . ' does not have a valid membership_type_id (' . $count . ' of ' . $this->_sourceData['membership_type_id']
            . 'found), not migrated');
          return FALSE;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Error retrieving membership type id from CiviCRM for '.$this->_entity
          .' with contact_id '. $this->_sourceData['contact_id'] . ' and membership_type_id'
          . $this->_sourceData['membership_type_id']
          . ', ignored. Error from API MembershipType getcount : ' . $ex->getMessage());
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
      return CRM_Core_DAO::executeQuery("SELECT * FROM ".$tableName);
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
    $daoProperties = get_object_vars($dao);
    foreach ($daoProperties as $daoProperty) {
      if (CRM_Core_DAO::checkFieldExists($tableName, $daoProperty)) {
        $column = array(
          'name' => $daoProperty,
          'type' => $this->getCustomColumnType($daoProperty, $tableName),
        );
        $columns[] = $column;
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
    $indexArray = array();
    $insertParams = array();
    foreach ($columns as $columnKey => $column) {
      $indexArray[] = '%'.$columnKey;
      $insertParams[$columnKey] = array($column['name'], $column['type']);
    }
    $insertQuery = 'INSERT INTO '.$tableName.' ('.implode(', ', $columns).') VALUES('.implode(', ',$indexArray).')';
    try {
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
      return CRM_Core_DAO::singleValueQuery('SELECT MAX(id) FROM '.$tableName);
    }
    catch (Exception $ex) {
      throw new Exception('Could not add custom data in table '.$tableName.', error from CRM_Core_DAO: '.$ex->getMessage());
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
    $query = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
      WHERE table_name = ".$tableName." AND COLUMN_NAME = ".$columnName;
    $dataType = strtolower(CRM_Core_DAO::singleValueQuery($query));
    switch ($dataType) {
      case 'int':
        return 'String';
        break;
      case 'decimal':
        return 'Money';
        break;
      default:
        return 'String';
        break;
    }
  }
}