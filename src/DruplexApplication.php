<?php

namespace Druplex;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Druplex\Controller\UserController;

/**
 * The Druplex application class.
 *
 * This class assumes that Drupal 7 has been bootstrapped but not set to handle
 * a request yet. It uses the $druplex global variable in settings.php for
 * configuration.
 */
class DruplexApplication extends Application {

  public function __construct($values) {
    parent::__construct($values);

    global $druplex;
    $this['debug'] = isset($druplex['debug']) ? $druplex['debug'] : FALSE;
    $this['api_prefix'] = isset($druplex['api_prefix']) ? $druplex['api_prefix'] : '/api';

    $this['debug'] = TRUE;

    // Set up JSON as a middleware.
    $this->before(function (Request $request, Application $this) {
      if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
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

    // Security definition.
    $encoder = new MessageDigestPasswordEncoder();
    $users['paul'] = array(
      'ROLE_USER',
      $encoder->encodePassword('password', ''),
    );
    $pattern = '^' . $this['api_prefix'];
    $this->register(new SecurityServiceProvider());
    $this['security.firewalls'] = array(
        // Login URL is open to everybody.
        'default' => array(
            'pattern' => $pattern,
            'http' => true,
            'stateless' => true,
            'users' => $users,
        ),
    );
    $this['security.access_rules'] = array(
        array($pattern, 'ROLE_USER'),
    );

    $controller = new UserController;
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
