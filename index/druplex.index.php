<?php

/**
 * @file
 * The PHP front controller to both Druplex API server and Drupal 7 proper.
 *
 * Replace your Drupal's index.php with this file.
 */

use Druplex\DruplexApplication;
use Symfony\Component\HttpFoundation\Request;

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

require_once __DIR__ . '/vendor/autoload.php';

// Druplex needs the Drupal.
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// If we match a route from Druplex, use Druplex.
$request = Request::createFromGlobals();
$path_array = explode('/', $request->getPathInfo());
if (isset($path_array[1]) && $path_array[1] == 'api') {
  // Make a Druplex app object.
  $app = new DruplexApplication(array('drupal_root' => DRUPAL_ROOT));
  $app->run($request);
}
// Otherwise, use Drupal.
else {
  unset($app);
  menu_execute_active_handler();
}
