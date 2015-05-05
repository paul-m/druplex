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
use Silex\Provider\DoctrineServiceProvider;

class DruplexApplication extends Application {

  public function __construct($values) {
    parent::__construct($values);

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

    // Set up the database from Drupal's settings.
    global $databases;
    $database = $databases['default']['default'];
    $driver_map = array(
      'mysql' => 'pdo_mysql',
    );
    $this->register(new DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'   => $driver_map[$database['driver']],
            'host'      => $database['host'],
            'dbname'    => $database['database'],
            'user'      => $database['username'],
            'password'  => $database['password'],
            'port'     => $database['port'],
            'charset'   => 'utf8',
        ),
    ));

    $this->get('/api', function (DruplexApplication $app) {
      $connection = $app['db'];
      return new Response("I'm the api!");
    });
  }

}
