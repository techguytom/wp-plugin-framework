<?php
/**
 * Mapper Test File
 *
 * @package Nerdery\Tests\Data\Mapper
 */

namespace Nerdery\Tests\Data\Mapper;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Data\Mapper\Mapper;

/**
 * Mapper
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Data\Mapper
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class MapperTest extends BaseTestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $map = array(
            'date_created' => 'dateCreated',
            'date_updated' => 'dateUpdated'
        );

        $this->object = $this->getObject($map);
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
     * Get an instance of the object under test (mocked)
     *
     * @param array $map Map to pass to construction of the mapper
     * @return void
     */
    public function getObject($map)
    {
        return $this->getMockForAbstractClass('Nerdery\Data\Mapper\Mapper', array($map));
    }

    /**
     * testConstructWithNoArgs
     *
     * @return void
     */
    public function testConstructWithNoArgs()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');
        $this->getMockForAbstractClass('Nerdery\Data\Mapper\Mapper', array());
    }

    /**
     * testConstructWithEmptyArray
     *
     * @return void
     */
    public function testConstructWithEmptyArray()
    {
        $this->setExpectedException('InvalidArgumentException', Mapper::ERROR_EMPTY_MAP_ARRAY);

        $map = array();
        $this->getMockForAbstractClass('Nerdery\Data\Mapper\Mapper', array($map));
    }

    /**
     * testConstructCorrect
     *
     * @return void
     */
    public function testConstructCorrect()
    {
        $map = array('foo' => 'bar');
        $object = $this->getObject($map);

        $this->assertEquals($map, $object->getColumnToPropertyMap());
    }

    /**
     * testMapArrayColumnToProperty
     *
     * @return void
     */
    public function testMapArrayColumnToProperty()
    {
        $sourceData = array(
            'id' => 1232,
            'date_created' => '20140401',
            'date_updated' => '20140411',
        );
        $expected = array(
            'id' => 1232,
            'dateCreated' => '20140401',
            'dateUpdated' => '20140411',
        );

        $this->assertEquals($expected, $this->object->mapArrayColumnToProperty($sourceData));
    }

    /**
     * testMapArrayPropertyToColumnIncorrectArgument
     *
     * The argument for mapArrayColumnToProperty must be an array
     *
     * @return void
     */
    public function testMapArrayPropertyToColumnIncorrectArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->object->mapArrayColumnToProperty(32);
    }

    /**
     * testMapArrayPropertyToColumn
     *
     * @return void
     */
    public function testMapArrayPropertyToColumn()
    {
        $sourceData = array(
            'id' => 1232,
            'dateCreated' => '20140401',
            'dateUpdated' => '20140411',
        );
        $expected = array(
            'date_created' => '20140401',
            'date_updated' => '20140411',
        );

        $this->assertEquals($expected, $this->object->mapArrayPropertyToColumn($sourceData));
    }

    /**
     * testMapArrayColumnToPropertyIncorrectArgument
     *
     * The argument for mapArrayPropertyToColumn must be an array
     *
     * @return void
     */
    public function testMapArrayColumnToPropertyIncorrectArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->object->mapArrayPropertyToColumn('fdafsa');
    }

    /**
     * testGetColumnByPropertyColumnNotMapped
     *
     * @return void
     */
    public function testGetColumnByPropertyColumnNotMapped()
    {
        $this->setExpectedException('UnexpectedValueException', "Property 'fdsa' does not exist in map.");

        $this->object->getColumnByProperty('fdsa');
    }

    /**
     * testGetColumnByProperty
     *
     * @return void
     */
    public function testGetColumnByProperty()
    {
        $expected = 'date_created';

        $this->assertEquals($expected, $this->object->getColumnByProperty('dateCreated'));
    }

    /**
     * testGetPropertyByColumnNotMapped
     *
     * @return void
     */
    public function testGetPropertyByColumnNotMapped()
    {
        $this->setExpectedException('UnexpectedValueException', "Column 'fdsa' does not exist in map.");

        $this->object->getPropertyByColumn('fdsa');
    }

    /**
     * testGetColumnByProperty
     *
     * @return void
     */
    public function testGetPropertyByColumn()
    {
        $expected = 'dateCreated';

        $this->assertEquals($expected, $this->object->getPropertyByColumn('date_created'));
    }
}
