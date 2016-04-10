<?php

namespace Druplex\Tests;

use Druplex\DruplexApplication;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group DruplexApplication
 * @coversDefaultClass Druplex\DruplexApplication
 */
class DruplexApplicationTest extends \PHPUnit_Framework_TestCase {

  public function providePrefix() {
    return array(
      array(
        FALSE, 'api', 'api'
      ),
      array(
        FALSE, '/api', 'api'
      ),
      array(
        TRUE, '/api/', 'api'
      ),
      array(
        FALSE, 'api/foof/yay', 'api'
      ),
      array(
        TRUE, '/api/foof/yay', 'api'
      ),
      array(
        FALSE, '/not_api/foof/yay', 'api'
      ),
      array(
        FALSE, '/api_not/foof/yay', 'api'
      ),
    );
  }

  /**
   * @covers ::createAppForPrefix
   * @dataProvider providePrefix
   */
  public function testCreateAppForPrefix($expected, $path, $prefix) {
    foreach(array('GET', 'POST', 'PATCH', 'DELETE') as $method) {
      $request = Request::create($path, $method);
      $app = DruplexApplication::createAppForPrefix($request, $prefix);
      if ($expected) {
        $this->assertTrue(is_a($app, 'Druplex\DruplexApplication'));
      }
      else {
        $this->assertNull($app);
      }
    }
  }

}
