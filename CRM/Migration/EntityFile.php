<?php

/**
 * Class for ForumZFD Entity File Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 Nov 2017
 * @license AGPL-3.0
 */
class CRM_Migration_EntityFile extends CRM_Migration_ForumZfd {
  private $_newActivityId = NULL;
  private $_newNoteId = NULL;

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      $queryParams = $this->setApiParams();
      // if not already exists
      $query = 'SELECT COUNT(*) FROM civicrm_entity_file WHERE entity_table = %1 AND entity_id = %2 AND file_id = %3';
      $count = CRM_Core_DAO::singleValueQuery($query, $queryParams);
      if ($count == 0) {
        $insert = 'INSERT INTO civicrm_entity_file (entity_table, entity_id, file_id) VALUES (%1, %2, %3)';
        CRM_Core_DAO::executeQuery($insert, $queryParams);
        $select = 'SELECT * FROM civicrm_entity_file WHERE entity_table = %1 AND entity_id = %2 AND file_id = %3';
        $dao = CRM_Core_DAO::executeQuery($select, $queryParams);
        return $dao->fetch();
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
    $queryParams = array(
      1 => array($this->_sourceData['entity_table'], 'String'),
      3 => array($this->_sourceData['file_id'], 'Integer'),
    );
    switch ($this->_sourceData['entity_table']) {
      case 'civicrm_activity':
        if ($this->_newActivityId) {
          $queryParams[2] = array($this->_newActivityId, 'Integer');
        }
        break;
      case 'civicrm_note':
        if ($this->_newNoteId) {
          $queryParams[2] = array($this->_newNoteId, 'Integer');
        }
        break;
      default:
        $queryParams[2] = array($this->_sourceData['entity_id'], 'Integer');
        break;
    }
    return $queryParams;
  }

  /**
   * Implementation of method to validate if source data is good enough for entity_tag
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['entity_id']) || empty($this->_sourceData['entity_id'])) {
      $this->_logger->logMessage('Error', 'EntityFile with id '.$this->_sourceData['id'].' has no entity_id, not migrated.');
      return FALSE;
    }
    if (!isset($this->_sourceData['entity_table']) || empty($this->_sourceData['entity_table'])) {
      $this->_logger->logMessage('Error', 'EntityFile with id '.$this->_sourceData['id'].' has no entity_table, not migrated.');
      return FALSE;
    }
    if (empty($this->_sourceData['file_id'])) {
      $this->_logger->logMessage('Error', 'EntityFile with id '.$this->_sourceData['id'].' has no file_id, not migrated.');
      return FALSE;
    }
    $query = 'SELECT COUNT(*)  FROM civicrm_file WHERE id = %1';
    $countFile = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($this->_sourceData['file_id'], 'Integer'),
    ));
    if ($countFile == 0) {
      $this->_logger->logMessage('Error', 'No file with id '.$this->_sourceData['file_id'].' found in civicrm_file, entity_file not migrated.');
      return FALSE;
    }
    if ($countFile > 1) {
      $this->_logger->logMessage('Error', 'More than one files with id '.$this->_sourceData['file_id'].' found in civicrm_file, entity_file not migrated.');
      return FALSE;
    }
    // check if activity exists if required
    if ($this->_sourceData['entity_table'] == 'civicrm_activity') {
      $this->_newActivityId = $this->findNewActivityId($this->_sourceData['entity_id']);
      CRM_Core_Error::debug('new act id', $this->_newActivityId);
      if (empty($this->_newActivityId)) {
        $this->_logger->logMessage('Warning', 'No activity with id '.$this->_sourceData['entity_id'].' found for entity_file with id '
          .$this->_sourceData['id'].', entity_file not migrated.');
        return FALSE;
      }
    }
    // check if note exists if required
    if ($this->_sourceData['entity_table'] == 'civicrm_note') {
      $this->_newNoteId = $this->findNewNoteId($this->_sourceData['entity_id']);
      if (empty($this->_newNoteId)) {
        $this->_logger->logMessage('Warning', 'No note with id '.$this->_sourceData['entity_id'].' found for entity_file with id '
          .$this->_sourceData['id'].', entity_file not migrated.');
        return FALSE;
      }
    }
    return TRUE;
  }
}