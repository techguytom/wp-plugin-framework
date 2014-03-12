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
        $this->object = $this->getMockForAbstractClass('Nerdery\Data\Entity\Entity');
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

        $this->assertInstanceOf('Nerdery\Data\Entity\Entity', $this->object);
    }

    /**
     * testHasErrors
     *
     * @return void
     */
    public function testHasErrorsInitialState()
    {
        $this->assertFalse($this->object->hasErrors());
    }

    /**
     * testHasErrorsAfterAddingErrors
     *
     * @return void
     */
    public function testHasErrorsAfterAddingErrors()
    {
        $this->object->addError('Error #1');

        $this->assertTrue($this->object->hasErrors());
    }

    /**
     * testGetErrorsEmpty
     *
     * @return void
     */
    public function testGetErrorsEmpty()
    {
        $this->assertEquals(array(), $this->object->getErrors());
    }

    /**
     * testGetErrors
     *
     * @return void
     */
    public function testGetErrors()
    {
        $this->object->addError('Wrong');

        $expected = array(
            'Wrong',
        );

        $this->assertEquals($expected, $this->object->getErrors());
    }

    /**
     * testIsValidEmpty
     *
     * @return void
     */
    public function testIsValidEmpty()
    {
        $this->assertTrue($this->object->isValid());
    }

    /**
     * testIsValidAfterError
     *
     * @return void
     */
    public function testIsValidAfterError()
    {
        $this->object->addError('Something went wrong');

        $this->assertFalse($this->object->isValid());
    }
}
