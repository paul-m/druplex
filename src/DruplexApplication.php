<?php

/**
 * @file
 * Contains \Druplex\DruplexApplication.
 */

namespace Druplex;

use Druplex\Controller\UserController;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * The Druplex application class.
 *
 * This class assumes that Drupal 7 has been bootstrapped but not set to handle
 * a request yet. It uses the $druplex global variable in settings.php for
 * configuration.
 */
class DruplexApplication extends Application {

  /**
   * Constructor.
   *
   * In order to keep the modifications to index.php small and readable, we do
   * all the initialization for the Druplex app here.
   *
   * @param array $values
   *   Parameter values or objects for the container.
   *
   * @global array $druplex
   *   The global settings for Druplex, based on the Drupal settings.php file.
   */
  public function __construct(array $values) {
    parent::__construct($values);

    global $druplex;
    $this['debug'] = isset($druplex['debug']) ? $druplex['debug'] : FALSE;
    $this['api_prefix'] = isset($druplex['api_prefix']) ? $druplex['api_prefix'] : '/api';

    $this['debug'] = TRUE;

    // Honor Drupal's maintenance_mode.
    $this->before(function (Request $request, Application $this) {
      // Unless your site profile sets maintenance mode explicitly, it will not
      // exist. So even though it would be better to default to TRUE here, we
      // have to default to FALSE.
      $maintenance = \variable_get('maintenance_mode', FALSE);
      if ($maintenance) {
        $this->abort(503, 'Site under maintenance.');
      }
    });

    // Set up JSON as a middleware.
    $this->before(function (Request $request, Application $this) {
      if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), TRUE);
        $request->request->replace(is_array($data) ? $data : array());
      }
    });

    // Make sure we wrap JSONP in a callback if present.
    $this->after(function (Request $request, Response $response) {
      if ($response instanceof JsonResponse) {
        $callback = $request->get('callback', '');
        if ($callback) {
          $response->setCallback($callback);
        }
      }
    });

    // Glean an API user from settings.
    // @todo: Hook this into Drupal user permissions somehow.
    $user = isset($druplex['api_user']) ? $druplex['api_user'] : 'paul';
    $password = isset($druplex['api_password']) ? $druplex['api_password'] : 'password';
    // Security definition.
    $encoder = new MessageDigestPasswordEncoder();
    $users[$user] = array(
      'ROLE_USER',
      $encoder->encodePassword($password, ''),
    );
    $pattern = '^' . $this['api_prefix'];
    $this->register(new SecurityServiceProvider());
    $this['security.firewalls'] = array(
      'default' => array(
        'pattern' => $pattern,
        'http' => TRUE,
        'stateless' => TRUE,
        'users' => $users,
      ),
    );
    $this['security.access_rules'] = array(
      array($pattern, 'ROLE_USER'),
    );

    // @todo: Until we can specify this another way without loading any files
    // or requiring routes in settings.php, we'll just define this explicitly
    // with an instantiated controller.
    $controller = new UserController();
    $this->post(
      $this['api_prefix'] . '/user',
      array($controller, 'postUser')
    );
    $this->get(
      $this['api_prefix'] . '/user/{uid}',
      array($controller, 'getUser')
    );
    $this->put(
      $this['api_prefix'] . '/user/{uid}',
      array($controller, 'putUser')
    );
    $this->get(
      $this['api_prefix'] . '/user/field/{fieldname}/{column}/{value}',
      array($controller, 'getUserByField')
    );
    $this->get(
      $this['api_prefix'] . '/user/uli/{uid}',
      array($controller, 'getUserUli')
    );
  }

}
