<?php

namespace Druplex;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DerAlex\Silex\YamlConfigServiceProvider;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;

class DruplexApplication extends Application {

  public function __construct(
    $drupal_root
    ) {
    parent::__construct();

    $this['drupal_root'] = $drupal_root;
    /**
     * Environment.
     *
     * If we can pull the config from our known location, do so. Otherwise grab the
     * test config.
     */
    if (file_exists('/etc/drupalci/config.yaml')) {
      $config = '/etc/drupalci/config.yaml';
    }
    else {
      $config = __DIR__ . '/../config/config-test.yaml';
    }

    /**
     * Services.
     */
    $this->register(new YamlConfigServiceProvider($config));

    /**
     * Handling.
     */
    $this->error(function (\Exception $e, $code) use ($this) {
      if ($e instanceof HttpException) {
        return new Response($e->getMessage(), $e->getStatusCode());
      }
      return "Something went wrong. Please contact the DrupalCI team.";
    });

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
    $users = array();
    foreach ($this['config']['users'] as $username => $password) {
      $users[$username] = array(
        'ROLE_USER',
        $encoder->encodePassword($password, ''),
      );
    }
    $this->register(new SecurityServiceProvider());
    $this['security.firewalls'] = array(
      // Login URL is open to everybody.
      'default' => array(
        'pattern' => '^.*$',
        'http' => true,
        'stateless' => true,
        'users' => $users,
      ),
    );
    $this['security.access_rules'] = array(
      array('^.*$', 'ROLE_USER'),
    );

    /**
     * Routing.
     */
    $this['routes'] = $this->extend('routes', function (RouteCollection $routes, Application $this) {
      $loader = new YamlFileLoader(new FileLocator(__DIR__));
      $collection = $loader->load('routes.yml');
      $routes->addCollection($collection);
      return $routes;
    });
  }

}
