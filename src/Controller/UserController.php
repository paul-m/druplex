<?php

namespace Druplex\Controller;

use Druplex\DruplexApplication;

class UserController {

  protected static function sanitize($user) {
    $output_user = new \stdClass();
    foreach (array('uid', 'name') as $property) {
      $output_user->$property = $user->$property;
    }
    return $output_user;
  }

  public function getUser(DruplexApplication $app, $uid) {
    $user = \user_load($uid);
    if ($user) {
      return $app->json(self::sanitize($user));
    }
    $app->abort(404);
  }

  public function getUserByField(DruplexApplication $app, $fieldname, $column, $value) {
    $query = new \EntityFieldQuery();
    $query->entityCondition('entity_type', 'user')
      ->fieldCondition($fieldname, $column, $value, '=');
    $result = $query->execute();
    if (isset($result['user'])) {
      $user = \user_load(reset($result['user'])->uid);
      $user = self::sanitize($user);
      return $app->json($user);
    }
    $app->abort(400);
  }

  public function getUserUli(DruplexApplication $app, $uid) {
    if (FALSE === $user = \user_load($uid)) {
      $app->abort(404);
    }
    return $app->json(array(
      'user' => self::sanitize($user),
      'uli' => user_pass_reset_url($user)
    ));
  }

  public function putUser(DruplexApplication $app, $uid) {
    // Figure out if the user exists.
    $user = user_load($uid);
    if ($user) {
//      error_log(print_r($user, TRUE));
      $changed = FALSE;
      // Work on schema fields.
      $schema = \drupal_get_schema('users');
      $schema = array_keys($schema['fields']);

      $never_update_these = array('uid', 'init', 'data', 'pass', 'created');

      $request = $app['request'];
      // We rely on the before middleware to put JSON in the request.
      foreach ($schema as $field) {
        if (!in_array($field, $never_update_these)) {
          if ($new_value = $request->get($field, FALSE)) {
            $user->$field = $new_value;
            $changed = TRUE;
          }
        }
      }
      unset($schema);
      unset($never_update_these);

      // Work on attached fields.
      // ?fieldname=field_foo&fieldcolumn=value&fieldvalue=text
      if (
        !$request->get('fieldname', FALSE) ||
        !$request->get('fieldcolumn', FALSE) ||
        !$request->get('fieldvalue', FALSE)
      ) {
        $app->abort(400);
        return;
      }
      // Gather the fields and schemata.
      // FYI: Field API doesn't have CRUD.
      $schemata = \drupal_get_schema();
      $instances = \field_info_instances('user', 'user');
      $fieldname = $request->get('fieldname');
      foreach (array_keys($instances) as $field) {
        if ($field == $fieldname) {
          // Which column?
          $fieldcolumn = $request->get('fieldcolumn');
          $column = $fieldname . '_' . $fieldcolumn;
          // Does the column exist in the schemata?
          if (isset($schemata['field_data_' . $fieldname]['fields'][$column])) {
            // Set the proper language code.
            $language = \field_language('user', $user, $fieldname);
            // Note that we completely ignore the concept of deltas.
            $user->{$fieldname}[$language][0][$fieldcolumn] = $request->get('fieldvalue');
            $changed = TRUE;
          }
        }
      }
      if ($changed) {
        \user_save($user);
        $user = \user_load($user->uid);
        return $app->json(UserController::sanitize($user));
      }

      return $app->json(UserController::sanitize($user));
    }
    else {
      $app->abort(404, 'No user for that id.');
    }
    $app->abort(400, "Wait, what?");
  }

}
