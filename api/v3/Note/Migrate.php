<?php
/**
 * EntityTag.Migrate API for ForumZFD migration of notes
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @date 1 March 2017
 * @license AGPL-3.0
 */
function civicrm_api3_note_Migrate($params) {
  // param migrate_type determines what will be selected, defaulted to contact
  $validTypes = array('contact', 'contribution', 'participant', 'relationship');
  if (!isset($params['migrate_type'])) {
    $entityTable = 'civicrm_contact';
  } else {
    if (in_array($params['migrate_type'], $validTypes)) {
      $entityTable = 'civicrm_'.$params['migrate_type'];
    } else {
      $entityTable = 'civicrm_contact';
    }
  }

  set_time_limit(0);
  $returnValues = array();
  $entity = 'note';
  $createCount = 0;
  $logCount = 0;
  $logger = new CRM_Migration_Logger($entity);
  $daoSource = CRM_Core_DAO::executeQuery("SELECT * FROM forumzfd_note WHERE entity_table = '{$entityTable}' AND is_processed = 0 ORDER BY entity_id LIMIT 1000");
  while ($daoSource->fetch()) {
    // update is_processed
    $update = 'UPDATE forumzfd_note SET is_processed = %1 WHERE id = %2';
    CRM_Core_DAO::executeQuery($update, array(
      1 => array(1, 'Integer'),
      2 => array($daoSource->id, 'Integer'),
    ));
    $civiNote = new CRM_Migration_Note($entity, $daoSource, $logger);
    $newNote = $civiNote->migrate();
    if ($newNote == FALSE) {
      $logCount++;
    } else {
      $createCount++;
    }
  }
  if (empty($daoSource->N)) {
    $returnValues[] = 'No more notes to migrate';
  } else {
    $returnValues[] = $createCount.' notes migrated to CiviCRM, '.$logCount.' with logged errors that were not migrated';
  }
  return civicrm_api3_create_success($returnValues, $params, 'Note', 'Migrate');
}

