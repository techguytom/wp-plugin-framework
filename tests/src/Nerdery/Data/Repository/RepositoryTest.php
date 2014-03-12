<?php
/**
 * Repository Test File
 *
 * @package Nerdery\Tests\Data\Repository
 */

namespace Nerdery\Tests\Data\Repository;

use Nerdery\Tests\BaseTestCase;
use Nerdery\Data\Repository\Repository;

/**
 * Repository
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\Data\Repository
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class RepositoryTest extends BaseTestCase
{
    /**
     * dataManager
     *
     * @var mixed
     */
    protected $dataManager;

    /**
     * mapper
     *
     * @var mixed
     */
    protected $mapper;

    /**
     * entityPrototype
     *
     * @var mixed
     */
    protected $entityPrototype;

    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        // Set up stub objects that repository object requires
        $this->dataManager = $this->getStubDataManager();
        $this->mapper = $this->getStubMapper();
        $this->entityPrototype = $this->getStubEntityPrototype();

        // We have to create the mock object under test
        $this->object = $this->getMockForAbstractClass(
            'Nerdery\Data\Repository\Repository',
            array(),
            'Repository',
            false
        );

        // Repository interface requires the source method
        $this->object->expects($this->any())
            ->method('source')
            ->will($this->returnValue('mytablename'));

        // Okay now we can construct it
        $this->object->__construct(
            $this->dataManager,
            $this->mapper,
            $this->entityPrototype
        );
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
     * getStubDataManager
     *
     * @return void
     */
    public function getStubDataManager()
    {
        $dataManager = $this->getMockBuilder('Nerdery\Data\Manager\DataManager')
            ->disableOriginalConstructor()
            ->getMock();

        $hydrator = $this->getMockBuilder('Nerdery\Data\Hydrator\Hydrator')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new \StdClass();
        $entity->id = 1;
        $entity->name = 'somename';

        $hydrator->expects($this->any())
            ->method('hydrate')
            ->will($this->returnValue($entity));

        $record = array(
            'name' => 'somename',
        );

        $hydrator->expects($this->any())
            ->method('dehydrate')
            ->will($this->returnValue($record));
        
        $dataManager->expects($this->any())
            ->method('getHydrator')
            ->will($this->returnValue($hydrator));

        $gateway = $this->getMockBuilder('Nerdery\WordPress\Gateway')
            ->disableOriginalConstructor()
            ->getMock();

        $gateway->expects($this->any())
            ->method('transaction')
            ->will($this->returnCallback(function($callable) {
                return $callable(); 
                }
            ));

        //$wpdbal = new \StdClass();
        //$wpdbal->insert_id = 33;

        //$gateway->expects($this->any())
        //    ->method('getWpDbal')
        //    ->will($this->returnValue($wpdbal));

        $dataManager->expects($this->any())
            ->method('getGateway')
            ->will($this->returnValue($gateway));

        return $dataManager;
    }

    /**
     * getStubMapper
     *
     * @return void
     */
    public function getStubMapper()
    {
        $mapper = $this->getMock('Nerdery\Data\Mapper\MapperInterface');

        $mapper->expects($this->any())
            ->method('mapArrayPropertyToColumn')
            ->will($this->returnArgument(0));

        return $mapper;
    }

    /**
     * getStubEntityPrototype
     *
     * @return void
     */
    public function getStubEntityPrototype()
    {
        $entityPrototype = new \StdClass();

        return $entityPrototype;
    }

    /**
     * testConstructorNoArgs
     *
     * @return void
     */
    public function testConstructorNoArgs()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->getMockForAbstractClass('Nerdery\Data\Repository\Repository');
    }

    /**
     * testConstructorIncorrectArgument
     *
     * @return void
     */
    public function testConstructorIncorrectArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');

        $this->getMockForAbstractClass('Nerdery\Data\Repository\Repository', array(1));
    }

    /**
     * testConstructorMissingSecondArgument
     *
     * @return void
     */
    public function testConstructorMissingSecondArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 2 passed to');

        $dataManager = $this->getMockBuilder('Nerdery\Data\Manager\DataManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->getMockForAbstractClass('Nerdery\Data\Repository\Repository', array($dataManager));
    }

    /**
     * testConstructorIncorrectSecondArgument
     *
     * @return void
     */
    public function testConstructorIncorrectSecondArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 2 passed to');

        $dataManager = $this->getMockBuilder('Nerdery\Data\Manager\DataManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mapper = 'not a mapper';

        $this->getMockForAbstractClass(
            'Nerdery\Data\Repository\Repository',
            array($dataManager, $mapper)
        );
    }

    /**
     * testConstructorMissingThirdArgument
     *
     * @return void
     */
    public function testConstructorMissingThirdArgument()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Missing argument 3 for');

        $dataManager = $this->getMockBuilder('Nerdery\Data\Manager\DataManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mapper = $this->getMock('Nerdery\Data\Mapper\MapperInterface');

        $this->getMockForAbstractClass(
            'Nerdery\Data\Repository\Repository',
            array($dataManager, $mapper)
        );
    }

    /**
     * testConstructorCorrectly
     *
     * @return void
     */
    public function testConstructorCorrectly()
    {
        $this->object->__construct($this->dataManager, $this->mapper, $this->entityPrototype);

        $this->assertTrue($this->object instanceof Repository);
    }

    /**
     * testConstructorEntityNotAnObject
     *
     * @return void
     */
    public function testConstructorEntityNotAnObject()
    {
        $this->setExpectedException('InvalidArgumentException', Repository::ERROR_ENTITY_NOT_OBJECT);
        $this->object->__construct($this->dataManager, $this->mapper, 1);
    }

    /**
     * testConstructorSourceNotProvided
     *
     * @return void
     */
    public function testConstructorSourceNotProvided()
    {
        // We have to create the mock object under test
        $this->object = $this->getMockForAbstractClass(
            'Nerdery\Data\Repository\Repository',
            array(),
            'Repository',
            false
        );

        $this->object->expects($this->any())
            ->method('source')
            ->will($this->returnValue(null));

        $this->setExpectedException('UnexpectedValueException', Repository::ERROR_SOURCE_NOT_SET);
        $this->object->__construct($this->dataManager, $this->mapper, $this->entityPrototype);
    }

    /**
     * testGetGateway
     *
     * @return void
     */
    public function testGetGateway()
    {
        $gateway = $this->object->getGateway();

        $expected = $this->dataManager->getGateway();
        $this->assertEquals($expected, $gateway);
    }

    /**
     * testGetHydrator
     *
     * @return void
     */
    public function testGetHydrator()
    {
        $hydrator = $this->object->getHydrator();

        $expected = $this->dataManager->getHydrator();
        $this->assertEquals($expected, $hydrator);
    }

    /**
     * testGetEntityInstance
     *
     * @return void
     */
    public function testGetEntityInstance()
    {
        $entity = $this->object->getEntityInstance();

        $expected = new \StdClass();
        $this->assertEquals($expected, $entity);
    }

    /**
     * testGetTablePrefix
     *
     * @return void
     */
    public function testGetTablePrefix()
    {
        $gateway = $this->dataManager->getGateway();

        $gateway->expects($this->any())
            ->method('getTablePrefix')
            ->will($this->returnValue('table_prefix_'));

        $this->assertEquals('table_prefix_', $this->object->getTablePrefix());
    }

    /**
     * testHydrateResultSet
     *
     * @return void
     */
    public function testHydrateResultSet()
    {
        $data = array(
            array(
                'id' => 1,
                'name' => 'somename',
            ),
        );

        $resultSet = $this->object->hydrateResultSet($data);

        $hydratedEntity = new \StdClass();
        $hydratedEntity->id = 1;
        $hydratedEntity->name = 'somename';

        $expected = array(
            $hydratedEntity,
        );

        $this->assertEquals($expected, $resultSet);
    }

    /**
     * testHydrateResultSetNonArray
     *
     * @return void
     */
    public function testHydrateResultSetNonArray()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');
        $this->object->hydrateResultSet('a');
    }

    /**
     * testHydrateResultSetWhenNotAnArrayOfArrays
     *
     * @return void
     */
    public function testHydrateResultSetWhenNotAnArrayOfArrays()
    {
        $this->setExpectedException(
            'UnexpectedValueException', Repository::ERROR_RESULTSET_NOT_ARRAYS
        );

        $data = array(
            'a' => 1,
            'b' => 2,
        );

        $this->object->hydrateResultSet($data);
    }

    /**
     * testHydrateNonArray
     *
     * @return void
     */
    public function testHydrateNonArray()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 1 passed to');
        $data = 'some data';

        $this->object->hydrate($data);
    }

    /**
     * testPersistWhenMapperReturnsNoPrimaryKey
     *
     * @dataProvider mapperPrimaryKeyValueProvider 
     * @param string $keyValue Key value
     * @return void
     */
    public function testPersistWhenMapperReturnsNoPrimaryKey($keyValue)
    {
        $this->setExpectedException('UnexpectedValueException', 'No primary key is available');

        $entity = $this->getMock('Nerdery\Data\Entity\Entity');

        // We will use the dataprovider to test the various failing PK names
        $this->mapper->expects($this->any())
            ->method('getPrimaryKeyPropertyName')
            ->will($this->returnValue($keyValue));

        $result = $this->object->persist($entity);
    }

    /**
     * Provider for mapper primary key values
     *
     * @return void
     */
    public function mapperPrimaryKeyValueProvider()
    {
        return array(
            array(null),
            array(''),
        );
    }

    /**
     * testPersistWithInvalidEntity
     *
     * @return void
     */
    public function testPersistWithInvalidEntity()
    {
        $this->setExpectedException('UnexpectedValueException', 'Your entity is invalid');

        // Let's make a stub entity
        $entity = $this->getMock('Nerdery\Data\Entity\Entity');
        $entity->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(false));
        $entity->expects($this->any())
            ->method('getErrors')
            ->will($this->returnValue(array('Your entity is invalid')));

        $this->object->persist($entity);
    }

    /**
     * Test persist with insert
     *
     * @return void
     */
    public function testPersistWithInsert()
    {
        // Our mapper needs to return `id` as the primary key
        $this->mapper->expects($this->any())
            ->method('getPrimaryKeyPropertyName')
            ->will($this->returnValue('id'));

        // Let's make a stub entity with id null
        $entity = $this->getMock('Nerdery\Data\Entity\Entity');
        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        // Our gateway::insert() will return the id 33
        $gateway = $this->object->getGateway();
        $gateway->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(33));

        $result = $this->object->persist($entity);

        // If successful, it returns the id of the new record
        $this->assertEquals(33, $result);
    }

    /**
     * testPersistWithFailingInsert
     *
     * @return void
     */
    public function testPersistWithFailingInsert()
    {
        $this->setExpectedException('UnexpectedValueException', 'A gateway error occurred');

        // Our mapper needs to return `id` as the primary key
        $this->mapper->expects($this->any())
            ->method('getPrimaryKeyPropertyName')
            ->will($this->returnValue('id'));

        // Let's make a stub entity with id null
        $entity = $this->getMock('Nerdery\Data\Entity\Entity');
        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        // Our gateway::insert() will return the id 33
        $gateway = $this->object->getGateway();
        $gateway->expects($this->any())
            ->method('insert')
            ->will($this->returnValue(false));

        // When it throws the exception, it gets the error message from the 
        // gateway
        $gateway->expects($this->any())
            ->method('getError')
            ->will($this->returnValue('A gateway error occurred'));

        $result = $this->object->persist($entity);
    }

    /**
     * testPersistWithUpdate
     *
     * @return void
     */
    public function testPersistWithUpdate()
    {
        // Our mapper needs to return `id` as the primary key
        $this->mapper->expects($this->any())
            ->method('getPrimaryKeyPropertyName')
            ->will($this->returnValue('id'));

        // Let's make a stub entity with id 34
        $entity = $this->getMock('Nerdery\Data\Entity\Entity');
        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(34));

        // We need to add some methods to our stub gateway
        $gateway = $this->object->getGateway();
        $gateway->expects($this->any())
            ->method('update')
            ->will($this->returnValue(1));

        $result = $this->object->persist($entity);

        // If successful it will return the number of rows affected
        $this->assertSame(1, $result);
    }

    /**
     * testPersistWithFailingUpdate
     *
     * @return void
     */
    public function testPersistWithFailingUpdate()
    {
        $this->setExpectedException('UnexpectedValueException', 'A gateway error occurred');

        // Our mapper needs to return `id` as the primary key
        $this->mapper->expects($this->any())
            ->method('getPrimaryKeyPropertyName')
            ->will($this->returnValue('id'));

        // Stub an entity pretending it has id 35
        $entity = $this->getMock('Nerdery\Data\Entity\Entity');
        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(35));

        // We need to add some methods to our stub gateway
        $gateway = $this->object->getGateway();

        // If gateway::update() returns false, then it will throw an exception
        $gateway->expects($this->any())
            ->method('update')
            ->will($this->returnValue(false));

        // When it throws the exception, it gets the error message from the 
        // gateway
        $gateway->expects($this->any())
            ->method('getError')
            ->will($this->returnValue('A gateway error occurred'));

        $result = $this->object->persist($entity);
    }

    public function testDelete()
    {
        // We need to add some methods to our stub gateway
        $gateway = $this->object->getGateway();

        // Delete will return the number of affected rows
        $gateway->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(2));

        $result = $this->object->delete('tablename', array('name' => 'foobar'));

        $this->assertEquals(2, $result);
    }

    /**
     * testDeleteWithNonArray
     *
     * @return void
     */
    public function testDeleteWithNonArray()
    {
        $this->setExpectedException('PHPUnit_Framework_Error', 'Argument 2 passed to');

        // We need to add some methods to our stub gateway
        $gateway = $this->object->getGateway();

        // Delete will return the number of affected rows
        $gateway->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(2));

        $result = $this->object->delete('tablename', '1');

        $this->assertEquals(2, $result);
    }

    /**
     * testDeleteFailure
     *
     * @return void
     */
    public function testDeleteFailure()
    {
        $this->setExpectedException('UnexpectedValueException', 'A gateway error occurred');

        // We need to add some methods to our stub gateway
        $gateway = $this->object->getGateway();

        // If gateway::update() returns false, then it will throw an exception
        $gateway->expects($this->any())
            ->method('delete')
            ->will($this->returnValue(false));

        // When it throws the exception, it gets the error message from the 
        // gateway
        $gateway->expects($this->any())
            ->method('getError')
            ->will($this->returnValue('A gateway error occurred'));

        $result = $this->object->delete('tablename', array());
    }
}
