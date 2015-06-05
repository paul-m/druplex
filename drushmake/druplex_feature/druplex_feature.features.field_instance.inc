<?php
/**
 * @file
 * druplex_feature.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function druplex_feature_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'user-user-field_druplex_test'
  $field_instances['user-user-field_druplex_test'] = array(
    'bundle' => 'user',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'user',
    'field_name' => 'field_druplex_test',
    'label' => 'druplex_test',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => 0,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 7,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('druplex_test');

  return $field_instances;
}
