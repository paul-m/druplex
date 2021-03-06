<?php

namespace Druplex\Tests;

use Druplex\Tests\DruplexTestBase;
use Druplex\DruplexApplication;

class DruplexTest extends DruplexTestBase {

  /**
   * An array of UIDs to delete from the fixture after the tests are done.
   * @var array
   */
  protected $delete_these_uids = [];

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
      'name' => 'round_trip',
      'mail' => 'round_trip@example.com',
    ];
    $client->request('POST', $this->apiPrefix() . 'user', $params);
    $this->assertEquals(201, $client->getResponse()->getStatusCode());
    $user = json_decode($client->getResponse()->getContent());
    $this->assertNotEmpty($user);
    $this->assertEquals(2, count((array) $user));
    $this->assertNotEmpty($user->uid);
    $this->assertNotEmpty($user->name);
    $this->delete_these_uids[] = $user->uid;

    $client = $this->createClient($this->httpAuth());
    $crawler = $client->request('GET', $this->apiPrefix() . 'user/' . $user->uid);
    $round_trip_user = json_decode($client->getResponse()->getContent());
    $this->assertEquals($user->uid, $round_trip_user->uid);
    $this->assertEquals($params['name'], $round_trip_user->name);
  }

  public function testPutUserField() {
    // Create the user.
    $params = [
      'name' => 'put_field',
      'mail' => 'put_field@example.com',
    ];
    $client = $this->createClient($this->httpAuth());
    $client->request('POST', $this->apiPrefix() . 'user', $params);
    $api_user = json_decode($client->getResponse()->getContent());
    $this->delete_these_uids[] = $api_user->uid;

    $user = \user_load($api_user->uid);
    $this->assertObjectHasAttribute('field_druplex_test', $user);
    $this->assertNull($user->field_druplex_test['und'][0]['value']);
    unset($user);

    // Set up the new field params.
    $field_params = [
      'fieldname' => 'field_druplex_test',
      'fieldcolumn' => 'value',
      'fieldvalue' => 'test_value',
    ];
    // Perform the PUT.
    $client = $this->createClient($this->httpAuth());
    $client->request('PUT', $this->apiPrefix() . 'user/' . $api_user->uid, $field_params);

    // Verify that it happened.
    $user = \user_load($api_user->uid);
    $this->assertEquals($field_params['fieldvalue'], $user->field_druplex_test['und'][0]['value']);
  }

  public function testPutUserFieldBadField() {
    // Create the user.
    $params = [
      'name' => 'put_bad_field',
      'mail' => 'put_bad_field@example.com',
    ];
    $client = $this->createClient($this->httpAuth());
    $client->request('POST', $this->apiPrefix() . 'user', $params);
    $api_user = json_decode($client->getResponse()->getContent());
    $this->delete_these_uids[] = $api_user->uid;

    $user = \user_load($api_user->uid);
    $this->assertObjectHasAttribute('field_druplex_test', $user);
    $this->assertNull($user->field_druplex_test['und'][0]['value']);
    unset($user);

    // Set up the new field params with a bad field name.
    $field_params = [
      'fieldname' => 'not_field_druplex_test',
      'fieldcolumn' => 'value',
      'fieldvalue' => 'test_value',
    ];
    // Perform the PUT.
    $client = $this->createClient($this->httpAuth());
    $client->request('PUT', $this->apiPrefix() . 'user/' . $api_user->uid, $field_params);
    // Verify that we got 400.
    $this->assertEquals(400, $client->getResponse()->getStatusCode());

    // Set up the new field params with a bad column name.
    $field_params = [
      'fieldname' => 'field_druplex_test',
      'fieldcolumn' => 'not_value',
      'fieldvalue' => 'test_value',
    ];
    // Perform the PUT.
    $client = $this->createClient($this->httpAuth());
    $client->request('PUT', $this->apiPrefix() . 'user/' . $api_user->uid, $field_params);
    // Verify that we got 400.
    $this->assertEquals(400, $client->getResponse()->getStatusCode());
  }

  public function tearDown() {
    // Remove fixture users we generated during the test.
    if (count($this->delete_these_uids)) {
      \user_delete_multiple($this->delete_these_uids);
      $this->delete_these_uids = [];
    }
    parent::tearDown();
  }

}
