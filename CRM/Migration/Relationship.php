<?php

/**
 * Class for ForumZFD Relationship Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 July 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Relationship extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      $apiParams = $this->setApiParams();
      // only if not already exists
      $count = civicrm_api3('Relationship', 'getcount', array(
        'contact_id_a' => $apiParams['contact_id_a'],
        'contact_id_b' => $apiParams['contact_id_b'],
        'relationship_type_id' => $apiParams['relationship_type_id']
      ));
      if ($count == 0) {
        try {
          $newRelationship = civicrm_api3('Relationship', 'create', $apiParams);
          return $newRelationship;
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Error', 'Could not create a relationship of type '.
            $apiParams['relationship_type_id']. ' between contact id '.$apiParams['contact_id_a'].
            ' and contact id '.$apiParams['contact_id_b'].'. Error from API Relationship create: '.$ex->getMessage());
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
    $removes = array('new_relationship_id', 'id', 'new_relationship_type_id', 'is_processed', 'case_id');
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
   * Method to check if relationship type is valid
   *
   * @return bool
   * @access private
   */

  private function validRelationshipType() {
    try {
      $count = civicrm_api3('RelationshipType', 'getcount', array(
        'id' => $this->_sourceData['relationship_type_id']));
      if ($count != 1) {
        $this->_logger->logMessage('Warning', 'Relationship with contact_id_a '.$this->_sourceData['contact_id_a']
          .' and contact_id_b '.$this->_sourceData['contact_id_b']. ' does not have a valid relationship_type_id ('
          .$count. ' of '.$this->_sourceData['relationship_type_id'].'found), not migrated');
        return FALSE;
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Error retrieving relationship_type_id from CiviCRM for relationship with contact_id_a '
        .$this->_sourceData['contact_id_a'].' and contact_id_b '.$this->_sourceData['contact_id_b']. ' and 
        relationship_type_id' . $this->_sourceData['relationship_type_id'] . ', not migrated. Error from API 
        RelationshipType getcount : ' . $ex->getMessage());
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Implementation of method to validate if source data is good enough for relationship
   *
   * @return bool
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['contact_id_a']) || empty($this->_sourceData['contact_id_a'])) {
      $this->_logger->logMessage('Error', 'Relationship has no contact_id_a, relationship not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }

    if (!isset($this->_sourceData['contact_id_b']) || empty($this->_sourceData['contact_id_b'])) {
      $this->_logger->logMessage('Error', 'Relationship has no contact_id_b, relationship not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }

    // find new contacs for contact_id_a and contact_id_b
    $newContactIdA = $this->findNewContactId($this->_sourceData['contact_id_a']);
    if ($newContactIdA) {
      $this->_sourceData['contact_id_a'] = $newContactIdA;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new contact for contact_id_a '.$this->_sourceData['contact_id_a']
        .' with source relationship '.$this->_sourceData['id'].' relationship not migrated.');
      return FALSE;
    }
    $newContactIdB = $this->findNewContactId($this->_sourceData['contact_id_b']);
    if ($newContactIdB) {
      $this->_sourceData['contact_id_b'] = $newContactIdB;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a new contact for contact_id_b '.$this->_sourceData['contact_id_b']
        .' with source relationship '.$this->_sourceData['id'].' relationship not migrated.');
      return FALSE;
    }

    if (!isset($this->_sourceData['new_relationship_type_id']) || empty($this->_sourceData['new_relationship_type_id'])) {
      $this->_logger->logMessage('Error', 'Could not find a new relationship type id for relationship_type_id '.$this->_sourceData['relationship_type_id']
        .' with source relationship '.$this->_sourceData['id'].' relationship not migrated.');
      return FALSE;
    } else {
      $this->_sourceData['relationship_type_id'] = $this->_sourceData['new_relationship_type_id'];
    }

    if (!$this->validRelationshipType()) {
      return FALSE;
    }
    return TRUE;
  }
}