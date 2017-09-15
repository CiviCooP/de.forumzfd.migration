<?php

/**
 * Class for ForumZFD Membership Migration to CiviCRM
 *
 * @author Erik Hommel  <hommel@ee-atwork.nl
 * @date 27 July March 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Membership extends CRM_Migration_ForumZfd {

  /**
   * Method to migrate incoming data
   * 
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      try {
        $apiParams = $this->setApiParams();
        $created = civicrm_api3('Membership', 'create', $apiParams);
        return $created;
      } catch (CiviCRM_API3_Exception $ex) {
        $message = 'Could not add or update membership, error from API Membership create: ' . $ex->getMessage();
        $this->_logger->logMessage('Error', $message);
        return FALSE;
      }
    }
  }

  /**
   * Method to set the api parameters
   *
   * @return array
   */
  private function setApiParams() {
    $apiParams = $this->_sourceData;
    $remove = array('id', 'new_membership_id');
    foreach ($remove as $removeKey) {
      unset($apiParams[$removeKey]);
    }
    // remove *_options array
    foreach ($apiParams as $key => $apiParam) {
      if (is_array($apiParam)) {
        unset($apiParams[$key]);
      }
      if (empty($apiParam)) {
        unset($apiParams[$key]);
      }
    }
    // set new campaign id if required and found
    if (isset($apiParams['campaign_id'])) {
      if (empty($apiParams['campaign_id'])) {
        unset($apiParams['campaign_id']);
      } else {
        $newCampaignId = $this->findNewCampaignId($apiParams['campaign_id']);
        if ($newCampaignId) {
          $apiParams['campaign_id'] = $newCampaignId;
        } else {
          $this->_logger->logMessage('Warning', 'No new campaign found for membership '.$this->_sourceData['id']
            .' with campaign '.$apiParams['campaign_id'].', campaign removed from migrated membership');
          unset($apiParams['campaign_id']);
        }
      }
    }
    // ignore join and start date 0000-00-00
    if (isset($apiParams['join_date']) && $apiParams['join_date'] == '0000-00-00') {
      $apiParams['join_date'] = '1970-01-01';
    }
    if (isset($apiParams['start_date']) && $apiParams['start_date'] == '0000-00-00') {
      $apiParams['start_date'] = '1970-01-01';
    }
    return $apiParams;
  }


  /**
   * Implementation of method to validate if source data is good enough for membership
   *
   * @return bool
   */
  public function validSourceData() {

    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Membership '.$this->_sourceData['id'].' has no contact_id, not migrated');
      return FALSE;
    }
    if (!$this->validMembershipType() || !$this->validMembershipStatus()) {
      return FALSE;
    }
    // set new membership types
    try {
      $mitgliedTypeId = civicrm_api3('MembershipType', 'getvalue', array(
        'return' => 'id',
        'name' => 'Mitglied',
      ));
      $mitgliedsOrganisationTypeId = civicrm_api3('MembershipType', 'getvalue', array(
        'return' => 'id',
        'name' => 'Mitgliedsorganisation',
      ));
      $fordererTypeId = civicrm_api3('MembershipType', 'getvalue', array(
        'return' => 'id',
        'name' => 'Förderer',
      ));
      $altTypeId = civicrm_api3('MembershipType', 'getvalue', array(
        'return' => 'id',
        'name' => 'Alt',
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Could not find membership type for either Alt, Mitglied, Förderer or Mitgliedorganisation. Check you set up!');
      return FALSE;
    }
    // find new membership type id based on name
    switch ($this->_sourceData['membership_type_id']) {
      case 1:
        $this->_sourceData['membership_type_id'] = $mitgliedTypeId;
        break;
      case 2:
        $this->_sourceData['membership_type_id'] = $fordererTypeId;
        break;
      case 6:
        $this->_sourceData['membership_type_id'] = $mitgliedsOrganisationTypeId;
        break;
      default:
        $this->_sourceData['membership_type_id'] = $altTypeId;
        break;
    }
    return TRUE;
  }
}