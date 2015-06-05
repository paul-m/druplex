<?php

namespace Druplex\Tests;

use Silex\WebTestCase;
use Druplex\DruplexApplication;

abstract class DruplexTestBase extends WebTestCase {

  public function createApplication() {
    return new DruplexApplication([]);
  }

  protected function httpAuth() {
    return [
      'PHP_AUTH_USER' => 'paul',
      'PHP_AUTH_PW' => 'password',
    ];
  }
  
  protected function apiPrefix() {
    return 'api/';
  }

}
