<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from xml/schema/CRM/Core/LocBlock.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:95df7c366d4f5920663299d762183081)
 */

/**
 * Database access object for the LocBlock entity.
 */
class CRM_Core_DAO_LocBlock extends CRM_Core_DAO {
  const EXT = 'civicrm';
  const TABLE_ADDED = '2.0';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_loc_block';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique ID
   *
   * @var int
   */
  public $id;

  /**
   * @var int
   */
  public $address_id;

  /**
   * @var int
   */
  public $email_id;

  /**
   * @var int
   */
  public $phone_id;

  /**
   * @var int
   */
  public $im_id;

  /**
   * @var int
   */
  public $address_2_id;

  /**
   * @var int
   */
  public $email_2_id;

  /**
   * @var int
   */
  public $phone_2_id;

  /**
   * @var int
   */
  public $im_2_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_loc_block';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? ts('Loc Blocks') : ts('Loc Block');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'address_id', 'civicrm_address', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'email_id', 'civicrm_email', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'phone_id', 'civicrm_phone', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'im_id', 'civicrm_im', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'address_2_id', 'civicrm_address', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'email_2_id', 'civicrm_email', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'phone_2_id', 'civicrm_phone', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'im_2_id', 'civicrm_im', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Location Block ID'),
          'description' => ts('Unique ID'),
          'required' => TRUE,
          'where' => 'civicrm_loc_block.id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => '2.0',
        ],
        'address_id' => [
          'name' => 'address_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Address ID'),
          'where' => 'civicrm_loc_block.address_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Address',
          'html' => [
            'label' => ts("Address"),
          ],
          'add' => '2.0',
        ],
        'email_id' => [
          'name' => 'email_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Email ID'),
          'where' => 'civicrm_loc_block.email_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Email',
          'html' => [
            'label' => ts("Email"),
          ],
          'add' => '2.0',
        ],
        'phone_id' => [
          'name' => 'phone_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Phone ID'),
          'where' => 'civicrm_loc_block.phone_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Phone',
          'html' => [
            'label' => ts("Phone"),
          ],
          'add' => '2.0',
        ],
        'im_id' => [
          'name' => 'im_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('IM ID'),
          'where' => 'civicrm_loc_block.im_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_IM',
          'html' => [
            'label' => ts("IM"),
          ],
          'add' => '2.0',
        ],
        'address_2_id' => [
          'name' => 'address_2_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('IM 2 ID'),
          'where' => 'civicrm_loc_block.address_2_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Address',
          'html' => [
            'label' => ts("IM 2"),
          ],
          'add' => '2.0',
        ],
        'email_2_id' => [
          'name' => 'email_2_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Email 2 ID'),
          'where' => 'civicrm_loc_block.email_2_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Email',
          'html' => [
            'label' => ts("Email 2"),
          ],
          'add' => '2.0',
        ],
        'phone_2_id' => [
          'name' => 'phone_2_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Phone 2 ID'),
          'where' => 'civicrm_loc_block.phone_2_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_Phone',
          'html' => [
            'label' => ts("Phone 2"),
          ],
          'add' => '2.0',
        ],
        'im_2_id' => [
          'name' => 'im_2_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Instant Messenger 2 ID'),
          'where' => 'civicrm_loc_block.im_2_id',
          'table_name' => 'civicrm_loc_block',
          'entity' => 'LocBlock',
          'bao' => 'CRM_Core_DAO_LocBlock',
          'localizable' => 0,
          'FKClassName' => 'CRM_Core_DAO_IM',
          'html' => [
            'label' => ts("Instant Messenger 2"),
          ],
          'add' => '2.0',
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'loc_block', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'loc_block', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
