<?php

/**
 * This bootstrap exists so that we can test using travis-ci.
 */

// Load in all the Composer-managed stuff.
require_once __DIR__ . '/vendor/autoload.php';

// Get Drupal. This basically echos Drupal's index.php file.
define('DRUPAL_ROOT', getcwd() . '/drupal');
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
// Druplex requires a booted Drupal 7. The travis build process will have
// provided one.
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
