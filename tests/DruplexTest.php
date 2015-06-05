<?php

namespace Druplex\Tests;

use Druplex\Tests\DruplexTestBase;
use Druplex\DruplexApplication;

class DruplexTest extends DruplexTestBase {

  public function testThis() {
    $user = \user_load(1);
    $this->assertEquals(1, $user->uid);
  }
  
  public function testTheApp() {
    $client = $this->createClient($this->httpAuth());
    $crawler = $client->request('GET', $this->apiPrefix() . 'user/1');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
  }

}
