<?php
/**
 * Data Entity Test File
 *
 * @package Nerdery\Tests\Data\Entity
 */

namespace Nerdery\Tests\Data\Entity;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Data\Entity\Entity;

/**
 * EntityTest
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Data\Entity
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class EntityTest extends BaseTestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->_object = $this->getMockForAbstractClass('Nerdery\Data\Entity\Entity');
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

        $this->assertInstanceOf('Nerdery\Data\Entity\Entity', $this->_object);
    }

    /**
     * testHasErrors
     *
     * @return void
     */
    public function testHasErrorsInitialState()
    {
        $this->assertFalse($this->_object->hasErrors());
    }

    /**
     * testHasErrorsAfterAddingErrors
     *
     * @return void
     */
    public function testHasErrorsAfterAddingErrors()
    {
        $this->_object->addError('Error #1');

        $this->assertTrue($this->_object->hasErrors());
    }

    /**
     * testGetErrorsEmpty
     *
     * @return void
     */
    public function testGetErrorsEmpty()
    {
        $this->assertEquals(array(), $this->_object->getErrors());
    }

    /**
     * testGetErrors
     *
     * @return void
     */
    public function testGetErrors()
    {
        $this->_object->addError('Wrong');

        $expected = array(
            'Wrong',
        );

        $this->assertEquals($expected, $this->_object->getErrors());
    }

    /**
     * testIsValidEmpty
     *
     * @return void
     */
    public function testIsValidEmpty()
    {
        $this->assertTrue($this->_object->isValid());
    }

    /**
     * testIsValidAfterError
     *
     * @return void
     */
    public function testIsValidAfterError()
    {
        $this->_object->addError('Something went wrong');

        $this->assertFalse($this->_object->isValid());
    }
}
