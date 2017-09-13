<?php

/**
 * Class for ForumZFD Address Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Address extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      if ($this->contactExists($this->_sourceData['contact_id'])) {
        $apiParams = $this->setApiParams();
        try {
          $newAddress = civicrm_api3('Address', 'create', $apiParams);
          return $newAddress;
        }
        catch (CiviCRM_API3_Exception $ex) {
          $this->_logger->logMessage('Error', 'Could not create or update address '.$this->_sourceData['id'].' '
            .$this->_sourceData['street_address'].' for contact '.$this->_sourceData['contact_id']
            .'. Error from API Address create: '.$ex->getMessage());
        }
      } else {
        $this->_logger->logMessage('Error', 'Could not find a contact with contact_id '
          .$this->_sourceData['contact_id'].' for address, not migrated.');
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
    $removes = array('master_id', 'new_address_id', 'id', '*_options', 'is_processed');
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
   * Implementation of method to validate if source data is good enough for address
   *
   * @return bool
   */
  public function validSourceData() {

    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Address has no contact_id, address not migrated. Source data is '.implode(';', $this->_sourceData));
      return FALSE;
    }

    if (!$this->validLocationType()) {
      return FALSE;
    }

    if (!empty($this->_sourceData['country_id'])) {
      try {
        $count = civicrm_api3('Country', 'getcount', array('id' => $this->_sourceData['country_id']));
        if ($count != 1) {
          $this->_logger->logMessage('Warning', 'Address with contact_id ' . $this->_sourceData['contact_id']
            . ' does not have a valid country_id (' . $count . ' of ' . $this->_sourceData['country_id']
            . 'found), address created without country');
          $this->_sourceData['country_id'] = 0;
        }
      } catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Warning', 'Error retrieving country from CiviCRM for address with contact_id '
          . $this->_sourceData['contact_id'] . ' and country_id' . $this->_sourceData['country_id']
          . ', address migrated without country. Error from API Country getcount : ' . $ex->getMessage());
        $this->_sourceData['country_id'] = 0;
      }
    }
    return TRUE;
  }

  /**
   * Method to fix all master ids once all addresses have been migrated
   */
  public static function fixMasterIds() {
    $daoSource = CRM_Core_DAO::executeQuery("SELECT new_address_id, master_id FROM forumzfd_address WHERE master_id IS NOT NULL");
    while ($daoSource->fetch()) {
      // get new address id of master
      $sql = "SELECT new_address_id FROM forumzfd_address WHERE id = %1";
      $newMasterId = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($daoSource->master_id, 'Integer'),));
      // update master in address
      if (!empty($newMasterId)) {
        $update = 'UPDATE civicrm_address SET master_id = %1 WHERE id = %2';
        CRM_Core_DAO::executeQuery($update, array(
          1 => array($newMasterId, 'Integer'),
          2 => array($daoSource->new_address_id, 'Integer'),
        ));
      }
    }
  }
}