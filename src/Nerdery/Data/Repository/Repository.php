<?php
/**
 * File Repository.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Repository;

use Nerdery\Data\Entity\EntityInterface;
use Nerdery\Data\Manager\DataManager;
use Nerdery\Data\Hydrator\Hydrator;
use Nerdery\Data\Mapper\MapperInterface;
use Nerdery\WordPress\Gateway;
use Exception;
use UnexpectedValueException;
use InvalidArgumentException;

/**
 * Class Repository
 *
 * @package ClogCulprits\Repository
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
abstract class Repository implements RepositoryInterface
{
    /*
     * Constants
     */
    const ERROR_SOURCE_NOT_SET = 'A valid data source must be defined using self::source()';
    const ERROR_ENTITY_NOT_OBJECT = 'Repository requires entity parameter to be an object.';
    const ERROR_RESULTSET_NOT_ARRAYS = 'Result set must be an array of arrays.';
    const ERROR_NO_PRIMARY_KEY_COLUMN_FOUND = 'No primary key is available on the entity %s using mapper %s.';
    const SQL_DATE_STAMP = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    private $source;

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var object
     */
    private $entityPrototype;

    /**
     * Constructor
     *
     * @param DataManager $dataManager
     * @param MapperInterface $mapper
     * @param object $entityPrototype
     *
     * @throws InvalidArgumentException If $entity argument is not an object
     * @throws UnexpectedValueException If $this->source is null
     */
    public function __construct(DataManager $dataManager, MapperInterface $mapper, $entityPrototype)
    {
        $this->dataManager = $dataManager;
        $this->mapper = $mapper;
        $this->entityPrototype = $entityPrototype;

        // Configure the hydrator using the data mapper
        $hydrator = $this->getDataManager()->getHydrator();
        $mapper = $this->getMapper();
        $hydrator->setColumnToPropertyMap(
            $mapper->getColumnToPropertyMap()
        );

        if (!is_object($entityPrototype)) {
            throw new InvalidArgumentException(self::ERROR_ENTITY_NOT_OBJECT);
        }

        $this->source = $this->source();

        if (null === $this->source) {
            throw new UnexpectedValueException(self::ERROR_SOURCE_NOT_SET);
        }
    }

    /**
     * Get the Data Manager
     *
     * @return DataManager
     */
    public function getDataManager()
    {
        return $this->dataManager;
    }

    /**
     * Get the gateway
     *
     * @return Gateway
     */
    public function getGateway()
    {
        $dataManager = $this->getDataManager();
        $gateway = $dataManager->getGateway();

        return $gateway;
    }

    /**
     * Get the entity Hydrator
     *
     * @return Hydrator
     */
    public function getHydrator()
    {
        $dataManager = $this->getDataManager();
        $hydrator = $dataManager->getHydrator();

        return $hydrator;
    }

    /**
     * Get the mapper
     *
     * @return MapperInterface
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Get a new instance of the entity prototype
     *
     * @return object
     */
    public function getEntityInstance()
    {
        $className = get_class($this->entityPrototype);
        $entity = new $className();

        return $entity;
    }

    /**
     * Get the table prefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        $gateway = $this->getGateway();
        $prefix = $gateway->getTablePrefix();

        return $prefix;
    }

    /**
     * Hydrate entity(ies)
     *
     * @param array $resultArray
     *
     * @throws \Exception
     * @return array|object
     */
    public function hydrateResultSet(array $resultArray)
    {
        $result = array();

        $firstRow = reset($resultArray);
        if (!is_array($firstRow)) {
            throw new UnexpectedValueException(self::ERROR_RESULTSET_NOT_ARRAYS);
        }

        foreach ($resultArray as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    /**
     * Hydrate
     *
     * @param array $dataArray
     *
     * @return object
     */
    public function hydrate(array $dataArray)
    {
        $hydrator = $this->getHydrator();
        $entity = $hydrator->hydrate(
            $this->getEntityInstance(),
            $dataArray
        );

        return $entity;
    }

    /**
     * Persist an entity
     *
     * @param EntityInterface $entity
     *
     * @throws \Exception
     * @return false|int
     */
    public function persist(EntityInterface $entity)
    {
        if (false === $entity->isValid()) {
            $errors = implode('\r\n', $entity->getErrors());
            throw new UnexpectedValueException($errors);
        }

        $mapper = $this->getMapper();
        $hydrator = $this->getHydrator();
        $dataArray = $hydrator->dehydrate($entity);
        $dataArray = $mapper->mapArrayPropertyToColumn($dataArray);
        $tableName = $this->getSource();

        $primaryKeyName = $mapper->getPrimaryKeyPropertyName();

        if (null === $primaryKeyName || trim($primaryKeyName) == '') {
            throw new UnexpectedValueException(
                sprintf(self::ERROR_NO_PRIMARY_KEY_COLUMN_FOUND, get_class($entity), get_class($mapper))
            );
        }

        $primaryKeyGetter = 'get' . ucfirst($primaryKeyName);

        $primaryKeyValue = $entity->$primaryKeyGetter();

        if (null !== $primaryKeyValue) {
            $result = $this->update(
                $tableName,
                $dataArray,
                array($primaryKeyName => $primaryKeyValue)
            );

            return $result;
        }

        /*
         * WordPresses DBAL has trouble with null properties, when it
         * sends them to the database server as SQL it sends an empty
         * string which is not a valid column value. To prevent this
         * we check for an empty string here, and if found simply unset
         * the key.
         */
        if (empty($dataArray[$primaryKeyName])) {
            unset($dataArray[$primaryKeyName]);
        }

        $gateway = $this->getGateway();
        $repository = $this;

        $id = $gateway->transaction(
            function () use ($repository, $tableName, $dataArray) {
                return $repository->insert($tableName, $dataArray);
            }
        );

        $entity->setId($id);

        return $id;
    }

    /**
     * Update
     *
     * @param string $tableName
     * @param array $data
     * @param array $where
     *
     * @throws \Exception
     * @return int Number of records affected
     */
    public function update($tableName, array $data, array $where)
    {
        $gateway = $this->getGateway();
        $result = $gateway->update($tableName, $data, $where);

        if (false === $result) {
            throw new UnexpectedValueException($gateway->getError());
        }

        return $result;
    }

    /**
     * Insert
     *
     * @param string $tableName
     * @param array $data
     *
     * @throws \Exception If insert query operation fails.
     * @return int Id of inserted record
     */
    public function insert($tableName, array $data)
    {
        $gateway = $this->getGateway();
        $result = $gateway->insert($tableName, $data);

        if (false === $result) {
            throw new UnexpectedValueException($gateway->getError());
        }

        return $result;
    }

    /**
     * Delete
     *
     * @param string $tableName
     * @param array $where
     *
     * @throws \Exception
     * @return false|int
     */
    public function delete($tableName, array $where)
    {
        $gateway = $this->getGateway();
        $result = $gateway->delete($tableName, $where);

        if (false === $result) {
            throw new UnexpectedValueException($gateway->getError());
        }

        return $result;
    }

    /**
     * Get the data source
     *
     * @return string
     */
    protected function getSource()
    {
        $prefix = $this->getTablePrefix();
        $tableName = $prefix . $this->source;

        return $tableName;
    }
}
