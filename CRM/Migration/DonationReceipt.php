<?php

/**
 * Class for ForumZFD Donation Receipt Migration to CiviCRM
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 September 2017
 * @license AGPL-3.0
 */
class CRM_Migration_DonationReceipt {
  private $_donationReceiptColumns = array();
  private $_itemColumns = array();
  private $_donationReceiptTable = NULL;
  private $_itemTable = NULL;
  private $_logger = NULL;
  private $_receiptReceiptIdColumn = NULL;
  private $_itemReceiptIdColumn = NULL;

  /**
   * CRM_Migration_DonationReceipt constructor.
   *
   * @param string $entityName
   * @throws Exception when error from api
   */
  public function __construct($entityName) {
    // get table names
    try {
      $this->_donationReceiptTable = civicrm_api3('CustomGroup', 'getvalue', array(
        'name' => 'zwb_donation_receipt',
        'return' => 'table_name',
      ));
      $this->_itemTable = civicrm_api3('CustomGroup', 'getvalue', array(
        'name' => 'zwb_donation_receipt_item',
        'return' => 'table_name',
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find custom groups for donation receipt in ' . __METHOD__
        . ', error from API CustomGroup getvalue: ' . $ex->getMessage());
    }
    // donation receipt fields
    $oldCustomFields = array(
      'status' => 'status_237',
      'type' => 'type_238',
      'issued_on' => 'issued_on_239',
      'issued_by' => 'issued_by_240',
      'original_file' => 'original_file_241',
      'date_from' => 'date_from_242',
      'date_to' => 'date_to_243',
      'display_name' => 'display_name_244',
      'contact_type' => 'contact_type_245',
      'gender' => 'gender_246',
      'prefix' => 'prefix_247',
      'postal_greeting_display' => 'postal_greeting_display_248',
      'email_greeting_display' => 'email_greeting_display_249',
      'addressee_display' => 'addressee_display_250',
      'street_address' => 'street_address_251',
      'supplemental_address_1' => 'supplemental_address_1_252',
      'supplemental_address_2' => 'supplemental_address_2_253',
      'postal_code' => 'postal_code_254',
      'city' => 'city_255',
      'country' => 'country_256',
      'shipping_addressee_display' => 'shipping_addressee_display_257',
      'shipping_street_address' => 'shipping_street_address_258',
      'shipping_supplemental_address_1' => 'shipping_supplemental_address_1_259',
      'shipping_supplemental_address_2' => 'shipping_supplemental_address_2_260',
      'shipping_postal_code' => 'shipping_postal_code_261',
      'shipping_city' => 'shipping_city_262',
      'shipping_country' => 'shipping_country_263',
    );
    $this->_logger = new CRM_Migration_Logger($entityName);
    try {
      $customFields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => 'zwb_donation_receipt',
        'options' => array('limit' => 0),
      ));
      foreach ($customFields['values'] as $customField) {
        if ($customField['name'] == 'receipt_id') {
          $this->_receiptReceiptIdColumn = $customField['column_name'];
        }
        if (isset($oldCustomFields[$customField['name']])) {
          $this->_donationReceiptColumns[$customField['name']] = array(
            'old' => $oldCustomFields[$customField['name']],
            'new' => $customField['column_name'],
          );
        }
      }
      // donation receipt item fields
      $oldCustomFields = array(
        'status' => 'status_264',
        'type' => 'type_265',
        'issued_in' => 'issued_in_266',
        'issued_on' => 'issued_on_267',
        'issued_by' => 'issued_by_268',
        'total_amount' => 'total_amount_269',
        'financial_type_id' => 'financial_type_id_270',
        'non_deductible_amount' => 'non_deductible_amount_271',
        'currency' => 'currency_272',
        'receive_date' => 'receive_date_273',
        'contribution_hash' => 'contribution_hash_274',
      );

      $customFields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => 'zwb_donation_receipt_item',
        'options' => array('limit' => 0),
      ));
      foreach ($customFields['values'] as $customField) {
        if ($customField['name'] == 'receipt_id') {
          $this->_itemReceiptIdColumn = $customField['column_name'];
        }
        if (isset($oldCustomFields[$customField['name']])) {
          $this->_itemColumns[$customField['name']] = array(
            'old' => $oldCustomFields[$customField['name']],
            'new' => $customField['column_name'],
          );
        }
      }
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find custom fields for custom groups zwb_donation_receipt or zwb_donation_receipt_items in '
        . __METHOD__ . ', error from CustomField Get API: ' . $ex->getMessage());
    }
  }

  /**
   * Method to migrate donation receipt
   *
   * @param $daoSource
   * @return bool
   */
  public function migrateReceipt($daoSource) {
    $numericFields = array('issued_by', 'original_file');
    $insertFields = array();
    $insertValues = array('%1');
    $insertIndex = 1;
    $insertParams = array(1 => array($daoSource->entity_id, 'Integer'));
    foreach ($this->_donationReceiptColumns as $columnName => $columnData) {
      if (isset($daoSource->$columnData['old'])) {
        $insertIndex++;
        $insertFields[] = $columnData['new'];
        $insertValues[] = '%'.$insertIndex;
        if (in_array($columnName, $numericFields)) {
          $insertParams[$insertIndex] = array($daoSource->$columnData['old'], 'Integer');
        } else {
          $insertParams[$insertIndex] = array(CRM_Core_DAO::escapeString($daoSource->$columnData['old']), 'String');
        }
      }
    }
    $contactCount = civicrm_api3('Contact', 'getcount', array('id' => $daoSource->entity_id));
    if ($contactCount > 0) {
      $insertQuery = 'INSERT INTO ' . $this->_donationReceiptTable . ' (entity_id, ' . implode(', ', $insertFields)
        . ') VALUES(' . implode(', ', $insertValues) . ')';
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
      return TRUE;
    } else {
      $this->_logger->logMessage('Error', 'Could not find contact '.$daoSource->entity_id.', donation receipt not migrated');
      return FALSE;
    }
  }

  /**
   * Method to migrate donation receipt item
   *
   * @param $daoSource
   * @return bool
   */
  public function migrateItem($daoSource) {
    $query = 'SELECT new_contribution_id FROM forumzfd_contribution WHERE id = %1';
    $newContributionId = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($daoSource->entity_id, 'Integer'),
    ));
    if ($newContributionId) {
      $insertParams[1] = array($newContributionId, 'Integer');
      $insertIndex = 1;
      $insertFields = array();
      $insertValues = array('%1');
      $receiptId = $this->findReceiptIdForItem($newContributionId);
      if ($receiptId) {
        $insertIndex++;
        $insertFields[$insertIndex] = $this->_itemReceiptIdColumn;
        $insertValues[$insertIndex] = '%'.$insertIndex;
        $insertParams[$insertIndex] = array($receiptId, 'Integer');
      }
      $numericFields = array('issued_by', 'issued_in', 'financial_type_id',);
      $decimalFields = array('total_amount', 'non_deductible_amount',);
      foreach ($this->_itemColumns as $columnName => $columnData) {
        if (isset($daoSource->$columnData['old'])) {
          $insertIndex++;
          $insertFields[] = $columnData['new'];
          $insertValues[] = '%'.$insertIndex;
          if (in_array($columnName, $numericFields)) {
            $insertParams[$insertIndex] = array($daoSource->$columnData['old'], 'Integer');
          } elseif (in_array($columnName, $decimalFields)) {
            $insertParams[$insertIndex] = array($daoSource->$columnData['old'], 'Money');
          } else {
            $insertParams[$insertIndex] = array(CRM_Core_DAO::escapeString($daoSource->$columnData['old']), 'String');
          }
        }
      }
      $insertQuery = 'INSERT INTO ' . $this->_itemTable . ' (entity_id, ' . implode(', ', $insertFields)
        . ') VALUES(' . implode(', ', $insertValues) . ')';
      CRM_Core_DAO::executeQuery($insertQuery, $insertParams);
      return TRUE;
    } else {
      $this->_logger->logMessage('Error', 'Could not find contribution with id '.$daoSource->entity_id.', donation receipt item not migrated');
      return FALSE;
    }
  }

  /**
   * Method to find the receipt id by checking the receive date of the item for the contact
   *
   * @param $contributionId
   * @return bool|int
   */
  private function findReceiptIdForItem($contributionId) {
  // first get contact_id of contribution
    $contribution = civicrm_api3('Contribution', 'getsingle', array(
      'id' => $contributionId,
      'return' => array("contact_id", "receive_date"),
    ));
    $receiveYear = date('Y', strtotime($contribution['receive_date']));
    $query = 'SELECT '.$this->_receiptReceiptIdColumn.' FROM '.$this->_donationReceiptTable.' WHERE YEAR('
      .$this->_donationReceiptColumns['date_from']['new'].') = %1 AND entity_id = %2';
    $receiptId = CRM_Core_DAO::singleValueQuery($query, array(
      1 => array($receiveYear, 'String'),
      2 => array($contribution['contact_id'], 'Integer'),
    ));
    if ($receiptId) {
      return $receiptId;
    } else {
      return FALSE;
    }
  }
}