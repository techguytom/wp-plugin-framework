<?php
/**
 * File GatewayTest.php
 *
 * @package Nerdery\WordPress\Gateway
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Tests\WordPress;

use Exception;
use Nerdery\WordPress\Gateway;

/**
 * Class GatewayTest
 *
 * @package Nerdery\WordPress\Gateway
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class GatewayTest extends \PHPUnit_Framework_TestCase
{
    const PLUGIN_PREFIX = 'plugin_';
    const WPDB_PREFIX = 'wpdb_';

    /**
     * Data provider types
     *
     * @var array
     */
    private $types = array(
        'integer' => array(1),
        'boolean' => array(true),
        'array' => array(array()),
        'string' => array('foo'),
        'null' => array(null),
        // Also callable, set in setup
    );

    /** @var Gateway */
    private $gateway;

    /**
     * Set up the test suite
     */
    public function setUp()
    {
        $wpdb = $this->setUpWpdb();
        $this->gateway = new Gateway($wpdb, self::PLUGIN_PREFIX);
        $this->types['callable'] = function() {};
    }

    /**
     * Tear down the test suite
     */
    public function tearDown()
    {
    }

    /**
     * Stub the $wpdb member
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function setUpWpdb()
    {
        $wpdb = $this->getMock(
            'wpdb',
            array(
                'prepare',
                'query',
                'get_row',
                'get_results',
                'update',
                'insert',
                'delete',
            )
        );

        $wpdb->prefix = self::WPDB_PREFIX;
        $wpdb->last_error = '';
        $wpdb->dbh = 'handle';

        $wpdb->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue(''));

        $wpdb->expects($this->any())
            ->method('query')
            ->will($this->returnValue(array()));

        $wpdb->expects($this->any())
            ->method('get_row')
            ->will($this->returnValue(array()));

        $wpdb->expects($this->any())
            ->method('get_results')
            ->will($this->returnValue(array()));

        $wpdb->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(1));

        $wpdb->expects($this->any())
            ->method('update')
            ->will($this->returnValue(1));

        $wpdb->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(1));

        return $wpdb;
    }

    public function nonStringArgumentProvider()
    {
        $types = $this->types;
        unset($types['string']);

        return $types;
    }

    public function nonArrayArgumentProvider()
    {
        $types = $this->types;
        unset($types['array']);

        return $types;
    }

    public function nonCallableArgumentProvider()
    {
        $types = $this->types;
        unset($types['callable']);

        return $types;
    }

    /**
     * Test contructor
     *
     * @return void
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Nerdery\WordPress\Gateway', $this->gateway);
    }

    /**
     * testConstructPrefixAsArrayException
     *
     * @expectedException Exception
     *
     * @return void
     */
    public function testConstructPrefixAsArrayException()
    {
        $wpdb = $this->setUpWpdb();
        $gateway = new Gateway($wpdb, array());
    }

    /**
     * Test can access wp dbal
     *
     * @return void
     */
    public function testCanGetWpDBAL()
    {
        $this->assertInstanceOf('wpdb', $this->gateway->getWpDbal(), 'oops');
    }

    /**
     * Test can composite table prefix
     *
     * @return void
     */
    public function testCanGetTableprefix()
    {
        $this->expectOutputString(self::WPDB_PREFIX.self::PLUGIN_PREFIX);
        print $this->gateway->getTablePrefix();
    }

    /**
     * Test wpdb argument cleaning
     *
     * @return void
     */
    public function testCleanArguments()
    {
        $arguments = array(
            'red' => null,
            'green' => true,
            'blue' => false,
        );

        $expectedResult = array(
            'red' => 'NULL',
            'green' => 1,
            'blue' => 0,
        );

        $result = $this->gateway->cleanArguments($arguments);

        $this->assertEquals($expectedResult, $result, 'Arrays are equal.');
    }

    /**
     * Test query prepare
     *
     * @return void
     */
    public function testPrepareQuery()
    {
        $result = $this->gateway->prepareQuery('', array());
        $this->assertTrue(is_string($result));
    }

    /**
     * testPrepareQueryRequiresSQL
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \Nerdery\WordPress\Gateway::ERROR_SQL_MUST_BE_STRING
     */
    public function testPrepareQueryRequiresSQL()
    {
        $this->gateway->prepareQuery(null, array());
    }

    /**
     * testPrepareQueryRequiresArrayArguments
     *
     * @expectedException \PHPUnit_Framework_Error
     * @dataProvider nonArrayArgumentProvider
     */
    public function testPrepareQueryRequiresArrayArguments($a)
    {
        $this->gateway->prepareQuery('', $a);
    }

    /**
     * Test test query pass through
     *
     * @return void
     */
    public function testQuery()
    {
        $result = $this->gateway->query('');
        $this->assertTrue(is_array($result));
    }

    /**
     * Test fetch row pass through
     *
     * @return void
     */
    public function testFetchRow()
    {
        $result = $this->gateway->fetchRow('');
        $this->assertTrue(is_array($result));
    }

    /**
     * Test fetch rows pass through
     *
     * @return void
     */
    public function testFetchRows()
    {
        $result = $this->gateway->fetchRows('');
        $this->assertTrue(is_array($result));
    }

    /**
     * Test insert only accepts string values for $tablename
     *
     * @dataProvider nonStringArgumentProvider
     */
    public function testInsertTablename($a)
    {
        $this->setExpectedException('InvalidArgumentException', Gateway::TABLE_NAME_MUST_BE_A_STRING);
        $this->gateway->insert($a, array());
    }

    /**
     * Test insert
     *
     */
    public function testInsert()
    {
        $result = $this->gateway->insert('', array());
        $this->assertTrue(is_int($result));
    }

    /**
     * Test update only accepts string values for $tablename
     *
     * @dataProvider nonStringArgumentProvider
     */
    public function testUpdateTablename($a)
    {
        $this->setExpectedException('InvalidArgumentException', Gateway::TABLE_NAME_MUST_BE_A_STRING);
        $this->gateway->update($a, array(), array());
    }

    /**
     * Test update
     *
     * @return void
     */
    public function testUpdate()
    {
        $result = $this->gateway->update('', array(), array());
        $this->assertTrue(is_int($result));
    }

    /**
     * Test delete only accepts string values for $tablename
     *
     * @dataProvider nonStringArgumentProvider
     *
     * @param $a
     *
     * @return void
     */
    public function testDeleteTablename($a)
    {
        $this->setExpectedException('InvalidArgumentException', Gateway::TABLE_NAME_MUST_BE_A_STRING);
        $this->gateway->delete($a, array());
    }

    /**
     * Test delete pass through
     *
     * @return void
     */
    public function testDelete()
    {
        $result = $this->gateway->delete('', array());
        $this->assertTrue(is_int($result));
    }

    /**
     * Test get last error pass through
     *
     * @return void
     */
    public function testLastError()
    {
        $result = $this->gateway->getError();
        $this->assertTrue(is_string($result));
    }

    /**
     * Test that we can access handle in wpdb
     *
     * @return void
     */
    public function testCanAccessDBH()
    {
        $result = $this->gateway->getDbHandle();
        $this->assertTrue($result === 'handle');
    }

    /**
     * Test that the argument for transaction must be a callable
     *
     * @dataProvider nonCallableArgumentProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage \Nerdery\WordPress\Gateway::ERROR_MUST_BE_CALLABLE
     */
    public function testTransactionArgumentMustBeCallable($a)
    {
        $this->gateway->transaction($a);
    }

    /**
     * Test that transaction passes callback through
     */
    public function testTransaction()
    {
        $result = $this->gateway->transaction(function() {
            return 'worked!';
        });

        $this->assertEquals($result, 'worked!');
    }

    /**
     * Test that transaction handles internal exceptions by rollback
     *
     * @expectedException \Exception
     * @expectedExceptionMessage \Nerdery\WordPress\Gateway::ERROR_DB_ROLLBACK
     */
    public function testTransactionHandlesCallbackExceptions()
    {
        $this->gateway->transaction(function() {
            throw new Exception('whoops');
        });
    }
}
