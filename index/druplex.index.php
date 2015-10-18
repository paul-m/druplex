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

// Pull in Composer-managed namespaces.
require_once __DIR__ . '/vendor/autoload.php';

// Druplex needs a booted Drupal.
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

// Get request path.
$request = Request::createFromGlobals();
$path_array = explode('/', $request->getPathInfo());

// If we match a route from Druplex, use Druplex.
$druplex_path = isset($druplex['api_prefix']) ? $druplex['api_prefix'] : 'api';
if (
  isset($path_array[1]) &&
  $path_array[1] == $druplex_path &&
  (!variable_get('maintenance_mode', TRUE))
  ) {
  // Make a Druplex app object.
  $app = new DruplexApplication(array('drupal_root' => DRUPAL_ROOT));
  $app->run($request);
}
// Otherwise, use Drupal.
else {
  unset($druplex);
  unset($druplex_path);
  unset($path_array);
  unset($request);
  menu_execute_active_handler();
}
