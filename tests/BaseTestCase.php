<?php
/**
 * Base Test Case class file
 *
 * @package Nerdery\Plugin
 */

namespace Nerdery\Tests;

/**
 * Base Test Case
 * 
 * @uses PHPUnit_Framework_TestCase
 * @package Nerdery\Plugin
 * @subpackage Tests
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Storage of object being tested
     *
     * @var object
     */
    protected $object;
}
