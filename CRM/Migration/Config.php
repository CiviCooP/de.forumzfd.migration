<?php
/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 18 April 2017
 * @license AGPL-3.0
 */
class CRM_Migration_Config {

  private static $_singleton;
  private $_defaultPostalIndividual = NULL;
  private $_defaultPostalOrganization = NULL;
  private $_defaultPostalHousehold = NULL;
  private $_defaultEmailIndividual = NULL;
  private $_defaultEmailOrganization = NULL;
  private $_defaultEmailHousehold = NULL;
  private $_defaultAddresseeIndividual = NULL;
  private $_defaultAddresseeOrganization = NULL;
  private $_defaultAddresseeHousehold = NULL;
  private $_customFieldsToIgnore = array();

  /**
   * Constructor method
   *
   * @param string $context
   */
  function __construct($context) {
    try {
      $this->_defaultEmailHousehold = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'email_greeting',
        'filter'=> 2,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultEmailIndividual = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'email_greeting',
        'filter'=> 1,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultEmailOrganization = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'email_greeting',
        'filter'=> 3,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultPostalHousehold = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'postal_greeting',
        'filter'=> 2,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultPostalIndividual = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'postal_greeting',
        'filter'=> 1,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultPostalOrganization = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'postal_greeting',
        'filter'=> 3,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultAddresseeHousehold = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'addressee',
        'filter'=> 2,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultAddresseeIndividual = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'addressee',
        'filter'=> 1,
        'is_default' => 1,
        'return' => 'value',
      ));
      $this->_defaultAddresseeOrganization = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'addressee',
        'filter'=> 3,
        'is_default' => 1,
        'return' => 'value',
      ));
    }
    catch (CiviCRM_API3_Exception $ex) {

    }
    $this->_customFieldsToIgnore = array(
      'custom_group_table_name' => array('custom_field_column_name',),
    );
  }

  /**
   * Getter for custom fields to ingore
   *
   * @param string $tableName
   * @return array
   */
  public function getCustomFieldsToIgnore($tableName) {
    if (isset($this->_customFieldsToIgnore[$tableName])) {
      return $this->_customFieldsToIgnore($tableName);
    }
  }
  /**
   * Getter for default addressee id household
   * @return array|null
   */
  public function getDefaultAddresseeHousehold() {
    return $this->_defaultAddresseeHousehold;
  }

  /**
   * Getter for default addressee id individual
   * @return array|null
   */
  public function getDefaultAddresseeIndividual() {
    return $this->_defaultAddresseeIndividual;
  }

  /**
   * Getter for default addressee id organization
   * @return array|null
   */
  public function getDefaultAddresseeOrganization() {
    return $this->_defaultAddresseeOrganization;
  }

  /**
   * Getter for default email greeting id household
   * @return array|null
   */
  public function getDefaultEmailHousehold() {
    return $this->_defaultEmailHousehold;
  }

  /**
   * Getter for default email greeting id individual
   * @return array|null
   */
  public function getDefaultEmailIndividual() {
    return $this->_defaultEmailIndividual;
  }

  /**
   * Getter for default email greeting id organization
   * @return array|null
   */
  public function getDefaultEmailOrganization() {
    return $this->_defaultEmailOrganization;
  }

  /**
   * Getter for default postal greeting id household
   * @return array|null
   */
  public function getDefaultPostalHousehold() {
    return $this->_defaultPostalHousehold;
  }

  /**
   * Getter for default postal greeting id individual
   * @return array|null
   */
  public function getDefaultPostalIndividual() {
    return $this->_defaultPostalIndividual;
  }

  /**
   * Getter for default postal greeting id organization
   * @return array|null
   */
  public function getDefaultPostalOrganization() {
    return $this->_defaultPostalOrganization;
  }


  /**
   * Singleton method
   *
   * @param string $context to determine if triggered from install hook
   * @return CRM_Migration_Config
   * @access public
   * @static
   */
  public static function singleton($context = null) {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Migration_Config($context);
    }
    return self::$_singleton;
  }
}
