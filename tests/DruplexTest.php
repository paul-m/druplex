<?php

namespace Druplex\Tests;

use Silex\WebTestCase;
use Druplex\DruplexApplication;

class DruplexTest extends WebTestCase {

  private function httpAuth() {
    return [
      'PHP_AUTH_USER' => 'paul',
      'PHP_AUTH_PW' => 'password',
    ];
  }
  
  public function testThis() {
    $user = \user_load(1);
    $this->assertEquals(1, $user->uid);
  }
  
  public function createApplication() {
    return new DruplexApplication([]);
  }
  
  public function testTheApp() {
    $client = $this->createClient($this->httpAuth());
    $crawler = $client->request('GET', 'api/user/1');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

}
