<?php

/**
 * DonationReceipt.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donation_receipt_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $createCount = 0;
  $logCount = 0;
  // migrate donation receipts (all of them as there are only about 4500)
  $daoReceipt = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_value_donation_receipt_41');
  $donationReceipt = new CRM_Migration_DonationReceipt('donation_receipt');
  while ($daoReceipt->fetch()) {
    if ($donationReceipt->migrateReceipt($daoReceipt) == TRUE) {
      $createCount++;
    } else {
      $logCount++;
    }
  }
  $returnValues[] = $createCount.' donation receipts migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  return civicrm_api3_create_success($returnValues, $params, 'DonationReceipt', 'Migrate');
}
