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
      // get forumzfd_value table name using the original custom table name
      $migrateTableName = $this->generateMigrateTableName($this->_sourceData['table_name']);
      $dao = $this->getCustomDataDao($migrateTableName);
      $columns = $this->getCustomDataColumns($dao, $this->_sourceData['table_name']);
      while ($dao->fetch()) {
        // find new contact id
        $newContactId = $this->findNewContactId($dao->entity_id);
        if ($newContactId) {
          $dao->entity_id = $newContactId;
          $this->insertCustomData($dao, $this->_sourceData['table_name'], $columns);
        } else {
          $this->_logger->logMessage('Error', 'Could not find or create a new contact for ' . $dao->entity_id . ' and table name '
            . $this->_sourceData['table_name'] . ', custom data not migrated.');
        }
      }
    }
    return FALSE;
  }

  /**
   * Implementation of method to validate if source data is good enough for note
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['table_name'])) {
      $this->_logger->logMessage('Error', 'Contact Custom Data has no table_name, not migrated.');
      return FALSE;
    }
    // create custom group and custom fields if necessary, error when not able to
    $created = $this->createCustomGroupIfNotExists($this->_sourceData);
    if ($created == FALSE) {
      $this->_logger->logMessage('Error', 'Could not find or create custom group with the name '.$this->_sourceData['table_name'].', custom data not migrated.');
      return FALSE;
    }
    return TRUE;
  }
}