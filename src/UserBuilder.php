<?php

/**
 * @file
 * Believe it.
 */

namespace Druplex;

use Symfony\Component\HttpFoundation\Request;

class UserBuilder {

  protected $user = NULL;
  protected $changed = FALSE;

  public function __construct(\stdClass $user) {
    $this->user = $user;
  }

  public function getUser() {
    return $this->user;
  }

  public function changed() {
    return $this->changed;
  }

  /**
   * Change entity fields present in the schema, based on the request.
   *
   * @param Request $request
   *   A request object from which we can glean the fields to change.
   *
   * @return $this
   *   Return the factory object for a fluent interface.
   */
  public function setFields(Request $request) {
    // Work on schema fields.
    $schema = \drupal_get_schema('users');
    $schema = array_keys($schema['fields']);

    $never_update_these = array('uid', 'init', 'data', 'pass', 'created');

    foreach ($schema as $field) {
      if (!in_array($field, $never_update_these)) {
        if ($new_value = $request->get($field, FALSE)) {
          $this->user->$field = $new_value;
          $this->changed = TRUE;
        }
      }
    }
    return $this;
  }

  /**
   * Change field values for fields attached to the user entity.
   *
   * Note that we do not address the issue of field delta for multi-value
   * attachments.
   *
   * @param string $field_name
   *   The field's machine name.
   * @param string $field_column
   *   The field's column name to change. For text field, 'value'.
   * @param string $field_value
   *   The value to set for the named column in the named field.
   *
   * @return $this
   *   Return the factory object for a fluent interface.
   */
  public function setAttachedField($field_name, $field_column, $field_value) {
    // Work on attached fields.
    // ?fieldname=field_foo&fieldcolumn=value&fieldvalue=text
    // Gather the fields and schemata.
    // FYI: Field API doesn't have CRUD.
    $schemata = \drupal_get_schema();
    $instances = \field_info_instances('user', 'user');
    foreach (array_keys($instances) as $field) {
      if ($field == $field_name) {
        // Which column?
        $column = $field_name . '_' . $field_column;
        // Does the column exist in the schemata?
        if (isset($schemata['field_data_' . $field_name]['fields'][$column])) {
          // Set the proper language code.
          $language = \field_language('user', $this->user, $field_name);
          // Note that we completely ignore the concept of deltas.
          $this->user->{$field_name}[$language][0][$field_column] = $field_value;
          $this->changed = TRUE;
        }
      }
    }
    return $this;
  }

}
