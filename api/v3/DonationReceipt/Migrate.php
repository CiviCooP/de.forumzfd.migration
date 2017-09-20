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

  $daoSource = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_value_donation_receipt_41 WHERE is_processed = 0 LIMIT 2500');
  $donationReceipt = new CRM_Migration_DonationReceipt();
  while ($daoSource->fetch()) {
    // update processed
    $update = 'UPDATE forumzfd_value_donation_receipt_41 SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($update, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    if ($donationReceipt->migrateReceipt($daoSource) == TRUE) {
      $createCount++;
    } else {
      $logCount++;
    }
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more donation receipts to migrate';
  } else {
    $returnValues[] = $createCount.' donation receipts migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'DonationReceipt', 'Migrate');

}
