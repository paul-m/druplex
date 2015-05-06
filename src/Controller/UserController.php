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

  public function putUserField(DruplexApplication $app, $uid, $fieldname, $column, $value) {
    // See if the field exists.

    $instances = \field_info_instances('user', 'user');
    error_log(print_r($instances, TRUE));

    $field_map = \field_info_field_map();
    if (isset($field_map[$fieldname])) {
      if (isset($field_map[$fieldname]['bundles']['user'])) {
        unset($field_map);

$user = \user_load($uid);
$user->field_myfield[LANGUAGE_NONE][0]['value']= 5;
$edit = array($fieldname
);
\user_save($user);


      }
    }



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

}
