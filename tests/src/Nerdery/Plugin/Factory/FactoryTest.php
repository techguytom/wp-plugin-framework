<?php
/**
 * Factory Test File
 *
 * @package Nerdery\Tests\Plugin\Factory
 */

namespace Nerdery\Tests\Plugin\Factory;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Plugin\Factory\Factory;
use Nerdery\Plugin\Plugin;

/**
 * Factory
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Plugin\Factory
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class FactoryTest extends BaseTestCase
{
    /**
     * plugin
     *
     * @var mixed
     */
    protected $plugin;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->plugin = $this->getStubPlugin();

        $config = array(
            Factory::CONFIG_KEY_TEMPLATE => 'foo-templates',
        );

        $this->object = new Factory($config);
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
     * Create a stub of the Plugin object
     *
     * @return Plugin
     */
    public function getStubPlugin()
    {
        $plugin = $this->getMock('Nerdery\Plugin\Plugin');
        $plugin->data = array();

        $plugin->expects($this->any())
            ->method('offsetSet')
            ->will($this->returnCallback(function($key, $value) use ($plugin) {
                $plugin->data[$key] = $value;
            }));

        $plugin->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnCallback(function($key) use ($plugin) {
                $value = $plugin->data[$key];
                if (is_callable($value)) {
                    // Execute the callable
                    return $value($plugin);
                }
                return $value;
            }));

        $plugin->expects($this->any())
            ->method('factory')
            ->will($this->returnCallback(function($callable) {
                return $callable;
            }));

        return $plugin;
    }

    /**
     * testConstructNoArgs
     *
     * @return void
     */
    public function testConstructNoArgs()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->object = new Factory();
    }

    /**
     * testConstructor
     *
     * @return void
     */
    public function testConstructor()
    {
        $this->object = new Factory(array());

        $this->assertTrue($this->object instanceof Factory);
    }

    /**
     * testGetPluginFilename
     *
     * @return void
     */
    public function testGetPluginFilename()
    {
        $this->object = new Factory(array());

        // The 'plugin filename' is the name of the file that created the 
        // factory
        $this->assertContains('FactoryTest.php', $this->object->getPluginFilename());
    }

    /**
     * testValidateFailure
     *
     * @return void
     */
    public function testValidateFailure()
    {
        $this->setExpectedException('InvalidArgumentException', Factory::ERROR_REQUIRED_VIEW_TEMPLATE_PATH);
        $this->object = new Factory(array());
        $this->object->validate();
    }

    /**
     * testValidate
     *
     * @return void
     */
    public function testValidate()
    {
        $config = array(
            Factory::CONFIG_KEY_TEMPLATE => 'abcdef',
        );

        $this->object = new Factory($config);

        $this->assertTrue($this->object->validate());
    }

    /**
     * testRegisterDataServices
     *
     * @return void
     */
    public function testRegisterDataServicesHydrator()
    {
        $this->object->registerDataServices($this->plugin);

        $hydrator = $this->plugin->offsetGet(Plugin::CONTAINER_KEY_DATA_HYDRATOR);
        $this->assertInstanceOf('Nerdery\Data\Hydrator\Hydrator', $hydrator);
    }

    /**
     * testRegisterDataServicesGateway
     *
     * @return void
     */
    public function testRegisterDataServicesGateway()
    {
        $wpdb = $this->getMock('wpdb');

        $proxy = $this->getMock('Nerdery\WordPress\Proxy');
        $proxy->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnValue($wpdb));

        $this->plugin->offsetSet(Plugin::CONTAINER_KEY_WP_PROXY, $proxy);
        $this->plugin->offsetSet(Plugin::CONTAINER_KEY_PLUGIN_DB_PREFIX, 'prefix_');

        $this->object->registerDataServices($this->plugin);

        $gateway = $this->plugin->offsetGet(Plugin::CONTAINER_KEY_GATEWAY);
        $this->assertInstanceOf('Nerdery\WordPress\Gateway', $gateway);
    }

    /**
     * testRegisterDataServiceDb
     *
     * @return void
     */
    public function testRegisterDataServiceDb()
    {
        $wpdb = $this->getMock('wpdb');

        $proxy = $this->getMock('Nerdery\WordPress\Proxy');
        $proxy->expects($this->any())
            ->method('getDatabase')
            ->will($this->returnValue($wpdb));

        $this->plugin->offsetSet(Plugin::CONTAINER_KEY_WP_PROXY, $proxy);
        $this->plugin->offsetSet(Plugin::CONTAINER_KEY_PLUGIN_DB_PREFIX, 'prefix_');

        $this->object->registerDataServices($this->plugin);

        $dataManager = $this->plugin->offsetGet(Plugin::CONTAINER_KEY_DB);
        $this->assertInstanceOf('Nerdery\Data\Manager\DataManager', $dataManager);
    }

    /**
     * testMakePlugin
     *
     * @return void
     */
    public function testMakePlugin()
    {
        $plugin = $this->object->make();

        $this->assertInstanceOf('Nerdery\Plugin\Plugin', $plugin);

    }

    /**
     * testMakeProxy
     *
     * @return void
     */
    public function testMakeProxy()
    {
        $plugin = $this->object->make();

        $proxy = $plugin[Plugin::CONTAINER_KEY_WP_PROXY];
        $this->assertInstanceOf('Nerdery\WordPress\Proxy', $proxy);

        $anotherProxy = $plugin[Plugin::CONTAINER_KEY_WP_PROXY];
        $this->assertSame($proxy, $anotherProxy);
    }

    /**
     * testMakePluginBaseName
     *
     * @return void
     */
    public function testMakePluginBaseName()
    {
        $plugin = $this->object->make();

        $proxy = $this->getMock('Nerdery\WordPress\Proxy');
        $proxy->expects($this->any())
            ->method('pluginBasename')
            ->will($this->returnValue('Mah name'));

        $plugin[Plugin::CONTAINER_KEY_WP_PROXY] = $proxy;

        $pluginBasename = $plugin[Plugin::CONTAINER_KEY_PLUGIN_WP_NAME];
        $this->assertEquals('Mah name', $pluginBasename);
    }

    /**
     * testMakePluginUrl
     *
     * @return void
     */
    public function testMakePluginUrl()
    {
        $plugin = $this->object->make();

        $proxy = $this->getMock('Nerdery\WordPress\Proxy');
        $proxy->expects($this->any())
            ->method('pluginsUrl')
            ->will($this->returnValue('plugins/'));

        $plugin[Plugin::CONTAINER_KEY_WP_PROXY] = $proxy;

        $this->assertEquals('plugins/', $plugin[Plugin::CONTAINER_KEY_PLUGIN_URL]);
    }

    /**
     * testMakeSession
     *
     * @return void
     */
    public function testMakeSession()
    {
        $plugin = $this->object->make();

        $session = $plugin[Plugin::CONTAINER_KEY_SESSION];
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Session\Session', $session);
    }

    /**
     * testMakeRequest
     *
     * @return void
     */
    public function testMakeRequest()
    {
        $plugin = $this->object->make();

        $request = $plugin[Plugin::CONTAINER_KEY_REQUEST];
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request);
    }

    /**
     * testMakeRouter
     *
     * @return void
     */
    public function testMakeRouter()
    {
        $plugin = $this->object->make();

        $proxy = $this->getMock('Nerdery\WordPress\Proxy');
        $proxy->expects($this->any())
            ->method('addAction')
            ->will($this->returnValue(true));

        $plugin[Plugin::CONTAINER_KEY_WP_PROXY] = $proxy;

        $router = $plugin[Plugin::CONTAINER_KEY_ROUTER];
        $this->assertInstanceOf('Nerdery\Plugin\Router\Router', $router);
    }

    /**
     * testMakeTwig
     *
     * @return void
     */
    public function testMakeTwig()
    {
        @mkdir('foo-templates');
        $plugin = $this->object->make();

        $this->assertEquals('foo-templates', $plugin[Plugin::CONTAINER_KEY_TWIG_PATH]);

        $this->assertInstanceOf('Twig_Loader_Filesystem', $plugin[Plugin::CONTAINER_KEY_TWIG_LOADER]);
        $this->assertInstanceOf('Twig_Environment', $plugin[Plugin::CONTAINER_KEY_TWIG]);
        @rmdir('foo-templates');
    }
}
