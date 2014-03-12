<?php
/**
 * Plugin Test File
 *
 * @package Nerdery\Tests\Plugin
 */

namespace Nerdery\Tests\Plugin;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Plugin\Plugin;

/**
 * Plugin
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Plugin
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class PluginTest extends BaseTestCase
{
    /**
     * proxy double
     *
     * @var mixed
     */
    protected $proxy;

    /**
     * session double
     *
     * @var mixed
     */
    protected $session;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->object = new Plugin();

        // We need a Session double
        $this->session = $this->getMock('Symfony\Component\HttpFoundation\Session\Session');

        // We need a Proxy double
        $this->proxy = $this->getMock('Nerdery\WordPress\Proxy');

        // Add components to plugin object under test
        $this->object[Plugin::CONTAINER_KEY_WP_PROXY] = $this->proxy;
        $this->object[Plugin::CONTAINER_KEY_SESSION] = $this->session;
        $this->object[Plugin::CONTAINER_KEY_PLUGIN_SLUG] = 'abc';
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
     * testInitialize
     *
     * @return void
     */
    public function testInitializeShouldInitSessionAndAddActions()
    {
        // Assert the addAction method is called twice,
        // First time with 'admin_menu' and second with 'shutdown'
        $this->proxy->expects($this->at(0))
            ->method('addAction')
            ->with($this->equalTo('admin_menu'),
                $this->anything());

        $this->proxy->expects($this->at(1))
            ->method('addAction')
            ->with($this->equalTo('shutdown'),
                $this->anything());

        $this->proxy->expects($this->exactly(2))
            ->method('addAction');

        // Assert isStarted and start are called on the session object
        $this->session->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));

        $this->session->expects($this->once())
            ->method('start')
            ->will($this->returnValue(true));


        $plugin = $this->object->initialize();

        // Initialize method should return the plugin object
        $this->assertSame($this->object, $plugin);
    }

    /**
     * testRegisterAdminMenus
     *
     * @return void
     */
    public function testRegisterAdminMenus()
    {
        $this->proxy->expects($this->once())
            ->method('doAction')
            ->with($this->equalTo('abc_admin_menu'));

        $plugin = $this->object->registerAdminMenus();

        $this->assertSame($this->object, $plugin);
    }

    /**
     * testActivate
     *
     * @return void
     */
    public function testActivate()
    {
        $this->proxy->expects($this->once())
            ->method('doAction')
            ->with($this->equalTo('abc_activation'));

        $plugin = $this->object->activate();

        $this->assertSame($this->object, $plugin);
    }

    /**
     * testDeactivate
     *
     * @return void
     */
    public function testDeactivate()
    {
        $this->proxy->expects($this->once())
            ->method('doAction')
            ->with($this->equalTo('abc_deactivation'));

        $this->proxy->expects($this->once())
            ->method('flushRewriteRules')
            ->with($this->equalTo(true));

        $plugin = $this->object->deactivate();

        $this->assertSame($this->object, $plugin);
    }

    /**
     * testShutdown
     *
     * @return void
     */
    public function testShutdown()
    {
        $this->proxy->expects($this->once())
            ->method('flushRewriteRules')
            ->with($this->equalTo(true));

        $result = $this->object->shutdown();

        $this->assertNull($result);
    }

    /**
     * testRegisterRouteWithIncorrectArg
     *
     * @return void
     */
    public function testRegisterRouteWithIncorrectArg()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->object->registerRoute(1);
    }

    /**
     * testRegisterRouteWithNoRouter
     *
     * @return void
     */
    public function testRegisterRouteWithNoRouter()
    {
        $this->setExpectedException('UnexpectedValueException', 'Unable to set route');

        $this->object[Plugin::CONTAINER_KEY_ROUTER] = null;

        $route = $this->getMock('Nerdery\Plugin\Router\Route');

        $this->object->registerRoute($route);
    }

    /**
     * testRegisterRoute
     *
     * @return void
     */
    public function testRegisterRoute()
    {
        $route = $this->getMock('Nerdery\Plugin\Router\Route');
        $router = $this->getMockBuilder('Nerdery\Plugin\Router\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $router->expects($this->once())
            ->method('addRoute')
            ->with($this->callback(function ($arg) use ($route) {
                return $arg == $route;
            }));

        $this->object[Plugin::CONTAINER_KEY_ROUTER] = $router;

        $this->object->registerRoute($route);
    }

    /**
     * testGetSession
     *
     * @return void
     */
    public function testGetSession()
    {
        $this->assertSame($this->session, $this->object->getSession());
    }

    /**
     * testGetSlug
     *
     * @return void
     */
    public function testGetSlug()
    {
        $this->assertEquals('abc', $this->object->getSlug());
    }

    /**
     * testGetProxy
     *
     * @return void
     */
    public function testGetProxy()
    {
        $this->assertSame($this->proxy, $this->object->getProxy());
    }

    /**
     * testGetPluginWpName
     *
     * @return void
     */
    public function testGetPluginWpName()
    {
        $this->object[Plugin::CONTAINER_KEY_PLUGIN_WP_NAME] = 'MyWpPlugin';

        $this->assertEquals('MyWpPlugin', $this->object->getPluginWpName());
    }

    /**
     * testGetRouter
     *
     * @return void
     */
    public function testGetRouter()
    {
        $this->object[Plugin::CONTAINER_KEY_ROUTER] = 'hiho';

        $this->assertEquals('hiho', $this->object->getRouter());
    }

    /**
     * testGetRequest
     *
     * @return void
     */
    public function testGetRequest()
    {
        $this->object[Plugin::CONTAINER_KEY_REQUEST] = 'a request';

        $this->assertEquals('a request', $this->object->getRequest());
    }

    /**
     * testGetViewRenderer
     *
     * @return void
     */
    public function testGetViewRenderer()
    {
        $this->object[Plugin::CONTAINER_KEY_TWIG] = 'twig';

        $this->assertEquals('twig', $this->object->getViewRenderer());
    }

    /**
     * testRegister
     *
     * @return void
     */
    public function testRegister()
    {
        $this->object->register('a', 'b');

        $expected = 'b';
        $this->assertEquals($expected, $this->object->get('a'));
    }

    /**
     * testGetNull
     *
     * @return void
     */
    public function testGetNull()
    {
        $this->setExpectedException('InvalidArgumentException', 'Identifier "fdsafdsa" is not defined.');

        $value = $this->object->get('fdsafdsa');
    }

    public function testRun()
    {
        $this->object[Plugin::CONTAINER_KEY_PLUGIN_WP_NAME] = 'MyWpPlugin';

        $this->proxy->expects($this->at(0))
            ->method('addAction')
            ->with($this->equalTo('init'),
                $this->anything());

        $this->proxy->expects($this->at(1))
            ->method('addAction')
            ->with($this->equalTo('activate_MyWpPlugin'),
                $this->anything());

        $this->proxy->expects($this->at(2))
            ->method('addAction')
            ->with($this->equalTo('deactivate_MyWpPlugin'),
                $this->anything());

        $this->proxy->expects($this->exactly(3))
            ->method('addAction');

        $this->object->run();
    }
}
