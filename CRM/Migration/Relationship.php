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
    // set new relationship type as configured on live
    switch ($apiParams['relationship_type_id']) {
      case 3:
        $apiParams['relationship_type_id'] = 4;
        break;
      case 4:
        $apiParams['relationship_type_id'] = 5;
        break;
      case 5:
        $apiParams['relationship_type_id'] = 6;
        break;
      case 6:
        $apiParams['relationship_type_id'] = 7;
        break;
      case 7:
        $apiParams['relationship_type_id'] = 8;
        break;
      case 9:
        $apiParams['relationship_type_id'] = 3;
        break;
      case 20:
        $apiParams['relationship_type_id'] = 17;
        break;
      case 22:
        $apiParams['relationship_type_id'] = 18;
        break;
    }
    return $apiParams;
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
    // some relationship types are to be ignored
    $validRelationshipTypeIds = array(1, 2 ,3 ,4 ,5, 6, 7, 9, 20, 22);
    if (!in_array($this->_sourceData['relationship_type_id'], $validRelationshipTypeIds)) {
      $this->_logger->logMessage('Warning', 'Relationship type id '.$this->_sourceData['relationship_type_id'].' is to be ignored, relationship not migrated.');
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