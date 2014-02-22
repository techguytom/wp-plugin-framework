<?php
/**
 * File ControllerTest.php
 *
 * @uses
 * @package Nerdery\Tests\Plugin\Controller
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Tests\Plugin\Controller;

use Nerdery\Plugin\Controller\Controller;

/**
 * Class ControllerTest
 *
 * @package Nerdery\Tests\Plugin\Controller
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    const CONTROLLER_CLASS = 'Nerdery\Plugin\Controller\Controller';

    /**
     * @var \Nerdery\Plugin\Controller\Controller
     */
    private $controller;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $plugin = $this->getMock('Nerdery\Plugin\Plugin', array('addAction'));
        $proxy = $this->getMock('Nerdery\WordPress\Proxy', array('addAction'));

        $plugin['wp-proxy'] = $proxy;
        $plugin['plugin.slug'] = null;

        $this->controller = $this->getMockForAbstractClass(
            self::CONTROLLER_CLASS,
            array(
                $plugin,
            )
        );
    }

    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(self::CONTROLLER_CLASS, $this->controller);
    }
}
