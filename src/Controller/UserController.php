<?php

/**
 * @file
 * Contains \Druplex\Controller\UserController.
 */

namespace Druplex\Controller;

use Druplex\DruplexApplication;
use Druplex\UserBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for manipulating Drupal 7 users through Silex framework.
 */
class UserController {

  /**
   * Based on an input user object, create a user object to be transmitted.
   *
   * We want to remove private information like email addresses and passwords.
   *
   * @param object $user
   *   Input user object.
   *
   * @return object
   *   Sanitized user object suitable for transmission over the internet.
   */
  protected static function sanitize($user) {
    $output_user = new \stdClass();
    foreach (array('uid', 'name') as $property) {
      $output_user->$property = $user->$property;
    }
    return $output_user;
  }

  /**
   * Automatically abort if required fields are not present in the request.
   *
   * @param DruplexApplication $app
   *   The application.
   * @param Request $request
   *   The request to search for required fields.
   * @param string[] $required_fields
   *   A list of fields which are required.
   */
  protected function abortForRequiredFields(DruplexApplication $app, Request $request, array $required_fields) {
    $needed = array();
    foreach ($required_fields as $required) {
      if (!$request->get($required, FALSE)) {
        $needed[] = $required;
      }
    }
    if (count($needed) > 0) {
      $app->abort(
        400, 'These fields are required: ' . implode(', ', $needed)
      );
    }
  }

  /**
   * Query for a user based on UID.
   *
   * @param DruplexApplication $app
   *   The injected application.
   * @param string $uid
   *   The UID, gleaned from the request path.
   */
  public function getUser(DruplexApplication $app, $uid) {
    if (is_numeric($uid)) {
      $user = \user_load($uid);
      if ($user) {
        return $app->json(self::sanitize($user));
      }
    }
    $app->abort(404);
  }

  /**
   * Query for a user based on the value of an attached field.
   *
   * @param DruplexApplication $app
   *   The injected application.
   * @param string $fieldname
   *   Attached field machine name.
   * @param string $column
   *   Attached field column name.
   * @param string $value
   *   Attached field value for the query.
   */
  public function getUserByField(DruplexApplication $app, $fieldname, $column, $value) {
    $query = new \EntityFieldQuery();
    try {
      $query->entityCondition('entity_type', 'user')
        ->fieldCondition($fieldname, $column, $value, '=');
      $result = $query->execute();
    }
    catch (\Exception $e) {
      $app->abort(404, $e->getMessage());
    }
    if (isset($result['user'])) {
      $user = \user_load(reset($result['user'])->uid);
      $user = self::sanitize($user);
      return $app->json($user);
    }
    $app->abort(404);
  }

  /**
   * Obtain a one-time login URL for the given user.
   *
   * Clearly this is a big deal you should probably not actually send out. :-)
   *
   * @param DruplexApplication $app
   *   The injected application.
   * @param string $uid
   *   The user UID.
   */
  public function getUserUli(DruplexApplication $app, $uid) {
    if (FALSE === $user = \user_load($uid)) {
      $app->abort(404);
    }
    return $app->json(array(
        'user' => self::sanitize($user),
        'uli' => \user_pass_reset_url($user),
    ));
  }

  /**
   * Update a user with new field values.
   *
   * @param DruplexApplication $app
   *   The injected application.
   * @param string $uid
   *   The UID of the user to update.
   */
  public function putUser(DruplexApplication $app, $uid) {
    // Figure out if the user exists.
    $user = user_load($uid);
    if ($user) {
      $request = $app['request'];
      $user_builder = new UserBuilder($user);
      // Change non-attached fields.
      $user_builder->setFields($request);
      // Figure out if we can start changing non-attached fields.
      if ($field_name = $request->get('fieldname', FALSE)) {
        $this->abortForRequiredFields($app, $request, array('fieldcolumn', 'fieldvalue'));
        $field_column = $request->get('fieldcolumn');
        if ($user_builder->attachedFieldExists($field_name, $field_column)) {
          // Set the attached field.
          $user_builder->setAttachedField(
            $field_name, $field_column, $request->get('fieldvalue')
          );
        }
        else {
          // Field does not exist. Abort without telling the user why.
          $app->abort(400);
        }
      }
      if ($user_builder->changed()) {
        \user_save($user_builder->getUser());
      }
      return $app->json(UserController::sanitize(\user_load($uid)));
    }
    $app->abort(400);
  }

  /**
   * Create a new user based on request information.
   *
   * @param DruplexApplication $app
   *   The injected application.
   */
  public function postUser(DruplexApplication $app) {
    $request = $app['request'];
    // Make sure we have the minimum.
    $this->abortForRequiredFields($app, $request, array('name', 'mail'));
    // Make sure no one is trying to set a password.
    if ($request->get('pass', FALSE)) {
      $app->abort(400, 'Do not include a password for the user. One will be generated.');
    }

    // Look for a pre-existing email address.
    $query = new \EntityFieldQuery();
    $query->entityCondition('entity_type', 'user')
      ->propertyCondition('mail', $request->get('mail'), '=');
    $result = $query->execute();
    // Try to load the existing user.
    $user = NULL;
    if (isset($result['user'])) {
      $user = \user_load(reset($result['user'])->uid);
    }
    // If we loaded a user, then fail.
    if ($user) {
      $app->abort(409, 'A user with that email address already exists.');
    }

    // Re-initialize $user and build it from the request.
    $user = new \stdClass();
    $user_builder = new UserBuilder($user);
    $user_builder->setFields($request);
    $user = $user_builder->getUser();
    // Generate a random password, maximum length.
    $user->pass = \user_password(30);
    // Save the user.
    $user = \user_save($user);

    // \user_save() can return FALSE if the user wasn't saved.
    if ($user) {
      $response = new JsonResponse(UserController::sanitize($user), 201);
      // @todo: Make this work. POST requests should return a URI for the new
      // resource.
      $response->headers->add(array('Location' => 'uh'));
      return $response;
    }
    // Default fail.
    $app->abort(500, 'Unable to save new user.');
  }

}
