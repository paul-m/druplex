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

// Druplex needs the database.
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);

// Make a Druplex app object.
$app = new DruplexApplication(DRUPAL_ROOT);
$request = Request::createFromGlobals();
$resolver = $app['resolver'];
// If we match a route from Druplex, use Druplex.
if ($resolver->getController($request) != FALSE) {
  $app->run($request);
}
// Otherwise, use Drupal.
else {
  unset($app);
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  menu_execute_active_handler();
}
