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

  public function testUli() {
    $client = $this->createClient($this->httpAuth());
    $crawler = $client->request('GET', $this->apiPrefix() . 'user/uli/1');
    $this->assertEquals(200, $client->getResponse()->getStatusCode());
    $uli = json_decode($client->getResponse()->getContent());
    $this->assertEquals(2, count((array) $uli));
    $this->assertNotEmpty($uli->uli);
    $this->assertNotEmpty($uli->user);

    $client = $this->createClient($this->httpAuth());
    $crawler = $client->request('GET', $this->apiPrefix() . 'user/uli/999999999999999');
    $this->assertEquals(404, $client->getResponse()->getStatusCode());
  }

  public function testUserPostRoundTrip() {
    $client = $this->createClient($this->httpAuth());
    $params = [
      'name' => 'paul',
      'mail' => 'foo@example.com',
    ];
    $crawler = $client->request('POST', $this->apiPrefix() . 'user', $params);
    $this->assertEquals(201, $client->getResponse()->getStatusCode());
    $user = json_decode($client->getResponse()->getContent());
    $this->assertNotEmpty($user);
    $this->assertEquals(2, count((array)$user));
    $this->assertNotEmpty($user->uid);
    $this->assertNotEmpty($user->name);
  }

}
