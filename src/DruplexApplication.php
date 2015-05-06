<?php

namespace Druplex;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Silex\Application;
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
    $this['api_prefix'] = isset($druplex['api_prefix']) ? $druplex['api_prefix'] : 'api';


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

    $controller = new UserController;
    $this->get(
      $this['api_prefix'] . '/user/{uid}',
      array($controller, 'getUser')
    );
    // Field query. ?fieldname=field_something&fieldvalue=foo
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
