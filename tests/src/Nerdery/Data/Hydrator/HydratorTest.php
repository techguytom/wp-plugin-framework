<?php
/**
 * Hydrator Test File
 *
 * @package Nerdery\Tests\Data\Hydrator
 */

namespace Nerdery\Tests\Data\Hydrator;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Data\Hydrator\Hydrator;

/**
 * Hydrator Test
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Data\Hydrator
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class HydratorTest extends BaseTestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
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
    public function testConstruct()
    {
        $this->_object = new Hydrator(array());

        $this->assertTrue($this->_object instanceof Hydrator);
    }

    /**
     * testConstructorInitializesColumnToPropertyMap
     *
     * @return void
     */
    public function testConstructorInitializesColumnToPropertyMap()
    {
        $entity = new \StdClass();
        $entity->b = 'm';

        $columnMap = array('a' => 'b');
        $this->_object = new Hydrator($columnMap);

        // In order to verify this worked, we need to test a side-effect
        $this->_object->hydrate($entity, array('a' => 'x'));
        $this->assertEquals('x', $entity->b);
    }

    /**
     * testSetColumnToPropertyMap
     *
     * @return void
     */
    public function testSetColumnToPropertyMap()
    {
        $entity = new \StdClass();
        $entity->b = 'm';

        $columnMap = array('a' => 'b');
        $this->_object = new Hydrator(array());
        $this->_object->setColumnToPropertyMap($columnMap);

        // In order to verify this worked, we need to test a side-effect
        $this->_object->hydrate($entity, array('a' => 'x'));
        $this->assertEquals('x', $entity->b);
    }

    public function testHydrate()
    {
        $entity = new \StdClass();
        $entity->name = '';
        $entity->title = '';
        $entity->tea = '';
        $entity->temperature = '';

        $data = array(
            'name' => 'Jean-Luc',
            'title' => 'Captain',
            'tea' => 'Earl Grey',
            'temperature' => 'hot',
            'age' => '100',
        );

        $this->_object = new Hydrator(array());
        $newEntity = $this->_object->hydrate($entity, $data);

        $this->assertEquals('Jean-Luc', $entity->name);
        $this->assertEquals('Captain', $entity->title);
        $this->assertEquals('Earl Grey', $entity->tea);
        $this->assertEquals('hot', $entity->temperature);

        // Age is ignored during hydration because it doesn't exist on the 
        // entity
        $this->assertFalse(isset($entity->age));

        // Verifies hydrate's return object is the hydrated entity
        $this->assertEquals('Jean-Luc', $newEntity->name);
    }

    /**
     * testValidatorWithEntityAsArray
     *
     * @expectedException InvalidArgumentException must
     * @return void
     */
    public function testValidatorWithEntityAsArray()
    {
        $entity = array();

        $data = array(
            'a' => 'b',
        );

        $this->_object = new Hydrator(array());
        $this->_object->hydrate($entity, $data);
    }
    
    /**
     * testValidatorWithEntityAsInt
     *
     * @expectedException InvalidArgumentException must
     * @return void
     */
    public function testValidatorWithEntityAsInt()
    {
        $entity = 32;

        $data = array(
            'a' => 'b',
        );

        $this->_object = new Hydrator(array());
        $this->_object->hydrate($entity, $data);
    }

    /**
     * testDehydrate
     *
     * @return void
     */
    public function testDehydrate()
    {
        $entity = new StubEntity();
        $entity->setName('James');
        $entity->setTitle('Doctor');

        $this->_object = new Hydrator(array());

        $expected = array(
            'name' => 'James',
            'title' => 'Doctor',
        );
        $data = $this->_object->dehydrate($entity);
        $this->assertEquals($expected, $data);
    }

    /**
     * testGetPropertyUsingEntityWithGetter
     *
     * @return void
     */
    public function testGetPropertyUsingEntityWithGetter()
    {
        // Note that I cannot mock this stub because the hydrator uses 
        // reflection which results in incorrect data values during dehydration 
        // (when mocking the objects)
        $entity = new StubEntityWithGetters();
        $entity->setName('yyyy');
        $entity->setTitle('zzzz');

        $this->_object = new Hydrator(array());

        $expected = array(
            'name' => 'yyyy',
            'title' => 'zzzz',
        );

        $data = $this->_object->dehydrate($entity);
        $this->assertEquals($expected, $data);
    }

    /**
     * testSetPropertyUsingEntityWithSetter
     *
     * @return void
     */
    public function testSetPropertyUsingEntityWithSetter()
    {
        $entity = new StubEntityWithGetters();

        $data = array(
            'name' => 'Matthew',
            'title' => 'Tax Collector',
        );

        $this->_object = new Hydrator(array());
        $this->_object->hydrate($entity, $data);

        $this->assertEquals('Matthew', $entity->getName());
    }

    /**
     * testSetPropertyUsingEntityWithSetterAndMappedColumn
     *
     * @return void
     */
    public function testSetPropertyUsingEntityWithSetterAndMappedColumn()
    {
        $entity = new StubEntityWithGetters();

        $data = array(
            'firstName' => 'Santa',
            'title' => 'Toymaker',
        );

        $map = array(
            'firstName' => 'name',
        );

        $this->_object = new Hydrator($map);
        $this->_object->hydrate($entity, $data);

        $this->assertEquals('Santa', $entity->getName());
    }
}

/**
 * StubEntity
 *
 * @package Nerdery\Tests\Data\Hydrator
 * @author Name <address@domain>
 * @version $Id$
 */
class StubEntity
{
    private $name;
    private $title;

    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }
}

/**
 * StubEntityWithGetters
 *
 * @package Nerdery\Tests\Data\Hydrator
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class StubEntityWithGetters
{
    private $name;
    private $title;

    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }

    public function setTitle($value)
    {
        $this->title = $value;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
