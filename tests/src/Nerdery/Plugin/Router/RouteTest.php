<?php
/**
 * File RouteTest.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Tests\Plugin\Router;
use Nerdery\Plugin\Router\Route;

/**
 * Class RouteTest
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    const URL = 'some/url';
    const CONTROLLER = 'controller_name';
    const ACTION = 'action_name';
    const HANDLER = 'controller_name:action_name';

    /**
     * @var \Nerdery\Plugin\Router\Route
     */
    private $routeObject;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->routeObject = new Route();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * handlerProvider
     *
     * @return array
     */
    public function handlerArgumentProvider()
    {
        return array(
            array(''),
            array('this.is.invalid'),
            array(1),
            array(null),
            array(function() {}),
            array(true),
            array(array()),
        );
    }

    public function testConstructWithUrl()
    {
        $this->assertInstanceOf(
            '\Nerdery\Plugin\Router\Route',
            new Route('some/url')
        );
    }

    public function testConstructWithHandler()
    {
        $this->assertInstanceOf(
            '\Nerdery\Plugin\Router\Route',
            new Route(null, 'controller:action')
        );
    }

    /**
     * Test invalid handler arguments
     *
     * @dataProvider handlerArgumentProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \Nerdery\Plugin\Router\Route::ERROR_INVALID_HANDLER
     */
    public function testInvalidHandler($handlerString)
    {
        $this->routeObject->setHandler($handlerString);
    }

    /**
     * Test a valid handler
     */
    public function testValidHandler()
    {
        $this->assertInstanceOf(
            '\Nerdery\Plugin\Router\Route',
            $this->routeObject->setHandler('valid:handler')
        );
    }

    public function testCanGetUrl()
    {
        $route = new Route(self::URL, self::HANDLER);
        $this->assertEquals(self::URL, $route->getUrl());
    }

    public function testCanGetController()
    {
        $route = new Route(self::URL, self::HANDLER);
        $this->assertEquals(self::CONTROLLER, $route->getControllerName());
    }

    public function testCanGetAction()
    {
        $route = new Route(self::URL, self::HANDLER);
        $this->assertEquals(self::ACTION, $route->getActionName());
    }
}

