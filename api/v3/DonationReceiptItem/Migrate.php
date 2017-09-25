<?php

/**
 * DonationReceiptItem.Migrate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donation_receipt_item_Migrate($params) {
  set_time_limit(0);
  $returnValues = array();
  $createCount = 0;
  $logCount = 0;
  $daoItem = CRM_Core_DAO::executeQuery('SELECT * FROM forumzfd_value_donation_receipt_item_42 WHERE is_processed = 0 LIMIT 5000');
  $donationReceipt = new CRM_Migration_DonationReceipt('donation_receipt_item');
  while ($daoItem->fetch()) {
    // update is processed
    $update = 'UPDATE forumzfd_value_donation_receipt_item_42 SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($update, array(
      1 => array(1, 'Integer'),
      2 => array($daoItem->id, 'Integer'),
    ));
    if ($donationReceipt->migrateItem($daoItem) == TRUE) {
      $createCount++;
    } else {
      $logCount++;
    }
  }
  $returnValues[] = $createCount . ' donation receipts items migrated to CiviCRM, ' . $logCount . ' with logged errors that were not migrated';
  return civicrm_api3_create_success($returnValues, $params, 'DonationReceiptItem', 'Migrate');
}
