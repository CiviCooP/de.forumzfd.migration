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
    $removes = array('new_relationship_id', 'name_a_b', 'name_b_a', 'id', 'is_processed', 'case_id');
    foreach ($this->_sourceData as $key => $value) {
      if (in_array($key, $removes)) {
        unset($apiParams[$key]);
      }
      if (is_array($value)) {
        unset($apiParams[$key]);
      }
    }
    // replace employee relationship type id
    if ($apiParams['relationship_type_id'] == 4) {
      $apiParams['relationship_type_id'] = 5;
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

    // find new relationship type id with name_a_b and name_b_a from source data
    $newRelationshipTypeId = $this->findRelationshipTypeIdWithNames();
    if ($newRelationshipTypeId) {
      $this->_sourceData['relationship_type_id'] = $newRelationshipTypeId;
    } else {
      $this->_logger->logMessage('Error', 'Could not find a relationship type with name_a_b '.$this->_sourceData['name_a_b']
        .' and name_b_a '.$this->_sourceData['name_b_a'].', relationship not migrated.');
      return FALSE;
    }

    if (!$this->validRelationshipType()) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to find the new relationship type id
   *
   * return int|bool
   */
  private function findRelationshipTypeIdWithNames() {
    try {
      return civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => $this->_sourceData['name_a_b'],
        'name_b_a' => $this->_sourceData['name_b_a'],
        'return' => 'id',
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
    }
    return FALSE;
  }
}