<?php
/**
 * File Repository.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Repository;

use Nerdery\Data\Manager\DataManager;
use Nerdery\Data\Entity\Entity;
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
     * @param string $tableName
     * @param Entity $entity
     *
     * @throws \Exception
     * @return false|int
     */
    public function _persist($tableName, Entity $entity)
    {
        if (false === $entity->isValid()) {
            $errors = implode('\r\n', $entity->getErrors());
            throw new Exception($errors);
        }

        $dataArray = $this->getHydrator()->dehydrate($entity);
        $dataArray = $this->getMapper()->mapArrayPropertyToColumn($dataArray);

        $primaryKeyName = $entity->getIndexPropertyName();

        if (null !== $entity->getIndex()) {
            return $this->update(
                $tableName,
                $dataArray,
                array(
                    $primaryKeyName => $dataArray[$primaryKeyName]
                )
            );
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

        return $this->insert($tableName, $dataArray);
    }

    /**
     * Update
     *
     * @param string $tableName
     * @param array $data
     * @param array $where
     *
     * @throws \Exception
     * @return false|int
     */
    public function update($tableName, array $data, array $where)
    {
        $gateway = $this->getGateway();
        $result = $gateway->update($tableName, $data, $where);

        if (false === $result) {
            throw new Exception($gateway->getError());
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
     * @return false|int
     */
    public function insert($tableName, array $data)
    {
        $gateway = $this->getGateway();
        $result = $gateway->insert($tableName, $data);

        if (false === $result) {
            throw new Exception($gateway->getError());
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
            throw new Exception($gateway->getError());
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
