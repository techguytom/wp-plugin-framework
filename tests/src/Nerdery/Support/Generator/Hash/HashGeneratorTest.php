<?php
/**
 * HashGenerator Test File
 *
 * @package Nerdery\Tests\Support\Generator\Hash\HashGenerator
 */

namespace Nerdery\Tests\Support\Generator\Hash\HashGenerator;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Support\Generator\Hash\HashGenerator;

/**
 * HashGenerator
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Support\Generator\Hash\HashGenerator
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class HashGeneratorTest extends BaseTestCase
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
     * testGenerate
     *
     * @return void
     */
    public function testGenerate()
    {
        $this->object = new HashGenerator();

        $hash = $this->object->generate('a');
        $this->assertContains('a$', $hash);
    }
}
