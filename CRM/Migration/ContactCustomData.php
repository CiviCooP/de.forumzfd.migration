<?php

/**
 * Class for ForumZFD Contact Custom Data Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_ContactCustomData extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      // migrate data foreach table
      foreach ($this->_sourceData['table_names'] as $sourceTableName) {
        // get forumzfd_value table name using the original custom table name
        $migrateTableName = $this->generateMigrateTableName($sourceTableName);
        $dao = $this->getCustomDataDao($migrateTableName);
        $columns = $this->getCustomDataColumns($dao, $sourceTableName);
        while ($dao->fetch()) {
          $this->insertCustomData($dao, $sourceTableName, $columns);
          // todo update new_id in migration table
        }
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
    if (empty($this->_sourceData['note'])) {
      $this->_logger->logMessage('Error', 'Note is empty, not migrated. Source note id is '.$this->_sourceData['id']);
      return FALSE;
    }
    return TRUE;
  }
}