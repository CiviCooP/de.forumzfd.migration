<?php

/**
 * Class for ForumZFD Contribution Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 14 June 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Contribution extends CRM_Migration_ForumZfd {
  private $_contributionData = array();

  /**
   * Method to migrate incoming data
   *
   * @return bool|array
   */
  public function migrate() {
    if ($this->validSourceData()) {
      // set generic data
      $this->generateContributionData();
      try {
        $newContribution = civicrm_api3('Contribution', 'create', $this->_contributionData);
        return $newContribution['values'][$newContribution['id']];
      }
      catch (CiviCRM_API3_Exception $ex) {
        $this->_logger->logMessage('Error', 'Could not create contribution ID '.$this->_sourceData['id']
          .', error from API Contribution create');
        return FALSE;
      }
    }
    return FALSE;
  }

  /**
   * Implementation of method to validate if source data is good enough for contribution
   *
   * @return bool
   * @throws Exception when required custom table not found
   */
  public function validSourceData() {
    if (!isset($this->_sourceData['contact_id'])) {
      $this->_logger->logMessage('Error', 'Contribution has no contact_id, not migrated. Contribution id is '
        .$this->_sourceData['id']);
      return FALSE;
    }
    // find payment instrument
    try {
      $count = civicrm_api3('OptionValue', 'getcount', array(
        'option_group_id' => 'payment_instrument',
        'value' => $this->_sourceData['payment_instrument_id'],
      ));
      if ($count == 0) {
        $this->_logger->logMessage('Error', 'Could not find payment instrument ID ' . $this->_sourceData['payment_instrument_id']
          . ' for contribution ID ' . $this->_sourceData['id'] . ', not migrated.');
        return FALSE;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Could not find payment instrument ID '.$this->_sourceData['payment_instrument_id']
        .' for contribution ID '.$this->_sourceData['id'].', not migrated.');
      return FALSE;
    }
    // find contribution status
    try {
      $count = civicrm_api3('OptionValue', 'getcount', array(
        'option_group_id' => 'contribution_status',
        'value' => $this->_sourceData['contribution_status_id'],
      ));
      if ($count == 0) {
        $this->_logger->logMessage('Error', 'Could not find contribution status ID ' . $this->_sourceData['contribution_status_id']
          . ' for contribution ID ' . $this->_sourceData['id'] . ', not migrated.');
        return FALSE;
      }
    }
    catch (CiviCRM_API3_Exception $ex) {
      $this->_logger->logMessage('Error', 'Could not find contribution status ID '.$this->_sourceData['contribution_status_id']
        .' for contribution ID '.$this->_sourceData['id'].', not migrated.');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Method to generate generic contribution data
   *
   */
  private function generateContributionData() {
    $this->_contributionData = array(
      'contact_id' => $this->_sourceData['contact_id'],
      'financial_type_id' => $this->_sourceData['financial_type_id'],
      'payment_instrument_id' => $this->_sourceData['payment_instrument_id'],
      'receive_date' => $this->_sourceData['receive_date'],
      'currency' => $this->_sourceData['currency'],
      'contribution_status_id' => $this->_sourceData['contribution_status_id'],
      'campaign_id' => $this->_sourceData['campaign_id'],
      'check_number' => $this->_sourceData['check_number'],
    );
    if (empty($this->_sourceData['source'])) {
      $this->_contributionData['source'] = 'Migration 2017';
    } else {
      $this->_contributionData['source'] = $this->_sourceData['source'];
    }
    $emptyChecks = array('non_deductible_amount', 'total_amount', 'fee_amount', 'net_amount', 'trxn_id', 'invoice_id',
      'cancel_date', 'cancel_reason', 'receipt_date', 'thankyou_date', 'amount_level', 'is_pay_later', 'address_id',
      'tax_amount', 'creditnote_id', 'revenue_recognition_date', 'contribution_page_id');
    foreach ($emptyChecks as $emptyCheck) {
      if (isset($this->_sourceData[$emptyCheck]) && !empty($this->_sourceData[$emptyCheck])) {
        $this->_contributionData[$emptyCheck] = $this->_sourceData[$emptyCheck];
      }
    }
    return;
  }
}