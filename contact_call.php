<?php

require_once 'contact_call.civix.php';
// phpcs:disable
use CRM_ContactCall_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function contact_call_civicrm_config(&$config) {
  _contact_call_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function contact_call_civicrm_install() {
  _contact_call_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function contact_call_civicrm_postInstall() {
  _contact_call_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function contact_call_civicrm_uninstall() {
  _contact_call_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function contact_call_civicrm_enable() {
  _contact_call_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function contact_call_civicrm_disable() {
  _contact_call_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function contact_call_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contact_call_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function contact_call_civicrm_entityTypes(&$entityTypes) {
  _contact_call_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function contact_call_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function contact_call_civicrm_navigationMenu(&$menu) {
//  _contact_call_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _contact_call_civix_navigationMenu($menu);
//}
/**
 * Assigns a greeting prefix to a new contact
 */
function _assignGreeting($objectId, $contactGender) {
  $prefix = 'Mx.';
  if ($contactGender == 'Female') {
    $prefix = 'Ms.';
  }
  elseif ($contactGender == 'Male') {
    $prefix = 'Mr.';
  }
  \Civi\Api4\Contact::update()
    ->addValue('prefix_id:name', $prefix)
    ->addWhere('id', '=', $objectId)
    ->execute();
}

/**
 * Schedules a call with a new contact
 */
function contact_call_civicrm_postCommit($op, $objectName, $objectId, &$objectRef) {
  if ($op == 'create' && $objectName == 'Individual') {
    $contact = \Civi\Api4\Contact::get()
      ->addSelect('created_date', 'gender_id:name')
      ->addWhere('id', '=', $objectId)
      ->execute()
      ->first();
    _assignGreeting($objectId, $contact['gender_id:name']);
    $date = new DateTime($contact['created_date']);
    $date->modify('+2 day');
    $callDate = $date->format('Y-m-d');
    \Civi\Api4\Activity::create()
      ->addValue('source_contact_id', 'user_contact_id')
      ->addValue('activity_type_id:name', 'Phone Call')
      ->addValue('activity_date_time', $callDate)
      ->addValue('status_id:name', 'Scheduled')
      ->addValue('target_contact_id', $objectId)
      ->addValue('subject', 'Welcome Call')
      ->execute();
  }
}
