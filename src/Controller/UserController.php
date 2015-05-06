<?php

namespace Druplex\Controller;

use Druplex\DruplexApplication;
use Druplex\User;
use Symfony\Component\HttpFoundation\Request;

class UserController {

  protected static function sanitize($user) {
    $output_user = new \stdClass();
    foreach (array('uid', 'name') as $property) {
      $output_user->$property = $user->$property;
    }
    return $output_user;
  }

  protected function abortForRequiredFields(DruplexApplication $app, Request $request, array $fields) {
    $needed = array();
    foreach ($fields as $required) {
      if (!$request->get($required, FALSE)) {
        $needed[] = $required;
      }
    }
    if (count($needed) > 0) {
      $app->abort(
        400,
        'These fields are required: ' . implode(', ', $needed)
      );
    }
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
      $request = $app['request'];
      $user_setter = new User($user);
      $user_setter->setFields($request);
      if ($field_name = $request->get('fieldname', FALSE)) {
        $this->abortForRequiredFields($app, $request, array('fieldcolumn', 'fieldvalue'));
        $user_setter->setAttachedField(
          $field_name,
          $request->get('fieldcolumn'),
          $request->get('fieldvalue')
        );
      }
      if ($user_setter->changed()) {
        \user_save($user_setter->getUser());
      }
      return $app->json(UserController::sanitize(\user_load($uid)));
    }
    $app->abort(400);
  }

}
