<?php
/**
 * DataManager Test File
 *
 * @package Nerdery\Tests\Data\Manager
 */

namespace Nerdery\Tests\Data\Manager;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Data\Manager\DataManager;

/**
 * DataManager
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Data\Manager
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class DataManagerTest extends BaseTestCase
{
    /**
     * gateway
     *
     * @var mixed
     */
    protected $gateway;

    /**
     * hydrator
     *
     * @var mixed
     */
    protected $hydrator;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->gateway = $this->getMockBuilder('Nerdery\WordPress\Gateway')
            ->disableOriginalConstructor()
            ->getMock();

        $this->hydrator = $this->getMockBuilder('Nerdery\Data\Hydrator\Hydrator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new DataManager($this->gateway, $this->hydrator);
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
     * testConstruct
     *
     * @return void
     */
    public function testConstructNoArgs()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');
        $this->object = new DataManager();
    }

    /**
     * testConstructIncorrectObject
     *
     * @return void
     */
    public function testConstructIncorrectArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');
        $gateway = new \StdClass();

        $this->object = new DataManager($gateway);
    }

    /**
     * testConstructWithOnlyOneArgument
     *
     * @return void
     */
    public function testConstructWithOnlyOneArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 2 passed to');
        $gateway = $this->getMockBuilder('Nerdery\WordPress\Gateway')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new DataManager($gateway);
    }

    /**
     * testConstructWithCorrectArguments
     *
     * @return void
     */
    public function testConstructWithCorrectArguments()
    {
        $gateway = $this->getMockBuilder('Nerdery\WordPress\Gateway')
            ->disableOriginalConstructor()
            ->getMock();

        $hydrator = $this->getMockBuilder('Nerdery\Data\Hydrator\Hydrator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new DataManager($gateway, $hydrator);
    }

    /**
     * testGetGateway
     *
     * @return void
     */
    public function testGetGateway()
    {
        $this->assertEquals($this->gateway, $this->object->getGateway());
    }

    /**
     * testGetHydrator
     *
     * @return void
     */
    public function testGetHydrator()
    {
        $this->assertEquals($this->hydrator, $this->object->getHydrator());
    }
}
