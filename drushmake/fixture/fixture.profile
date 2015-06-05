<?php

function fixture_profile_details() {
  return array(
    'name' => 'Druplex Fixture',
    'description' => 'Automating deployments since 2015'
  );
}

/**
 * Implements hook_modules_installed().
 * 
 * We use this opportunity to set up our fixture, after all the dependencies
 * are met, specifically the modules.
 */
function fixture_modules_installed($modules) {
  $instance_name = 'field_druplex_text';
  field_create_field(array(
    'field_name' => $instance_name,
    'type' => 'text',
  ));
  field_create_instance(array(
    'field_name' => $instance_name,
    'entity_type' => 'user',
    'bundle' => 'user',
  ));
}
