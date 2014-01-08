<?php
/**
 * File Hydrator.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Hydrator;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class Hydrator
 *
 * This class is responsible for hydrating objects. It does this by reflecting
 * the object to be hydrated, gaining access to set/get private & protected
 * properties. Then the class, via a mapper,
 *
 * @package Nerdery\Plugin\Data\Entity
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Hydrator
{
    /*
     * Constants
     */
    const ERROR_ENTITY_NOT_OBJECT = 'Entity argument must be an object.';

    /**
     * Column to property map.
     *
     * @var array
     */
    protected $columnToPropertyMap;

    /**
     * Constructor
     *
     * @param array $columnToPropertyMap
     */
    public function __constructor(array $columnToPropertyMap)
    {
        $this->columnToPropertyMap = $columnToPropertyMap;
    }

    /**
     * Set the columnToPropertyMap
     *
     * @param array $columnToPropertyMap
     *
     * @return self
     */
    public function setColumnToPropertyMap($columnToPropertyMap)
    {
        $this->columnToPropertyMap = $columnToPropertyMap;

        return $this;
    }

    /**
     * Hydrate an entity
     *
     * @param object $entity
     * @param array $dataArray
     *
     * @return object
     * @throws InvalidArgumentException
     */
    public function hydrate($entity, array $dataArray)
    {
        $this->validateObject($entity);
        foreach ($dataArray as $property => $value) {
            $this->setProperty($entity, $property, $value);
        }

        return $entity;
    }

    /**
     * Dehydrate an entity
     *
     * Convert an entity to an array, this method reverses the work that
     * the hydrate method performs.
     *
     * @param $entity
     *
     * @return array
     */
    public function dehydrate($entity)
    {
        $this->validateObject($entity);
        $dataArray = array();
        $reflectedEntity = new ReflectionClass($entity);
        $properties = $reflectedEntity->getProperties(\ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $dataArray[$propertyName] = $this->getProperty($entity, $property, $propertyName);
        }

        return $dataArray;
    }

    /**
     * Get property
     *
     * @param object $entity
     * @param ReflectionProperty $property
     * @param string $propertyName
     *
     * @return mixed
     */
    private function getProperty($entity, ReflectionProperty $property, $propertyName)
    {
        $getterMethod = 'get' . ucfirst($propertyName);

        if (method_exists($entity, $getterMethod)) {
            return $entity->$getterMethod();
        }

        $property->setAccessible(true);

        return $property->getValue($entity);
    }

    /**
     * Set property
     *
     * @param object $entity
     * @param string $property
     * @param mixed $value
     *
     * @return bool
     */
    private function setProperty($entity, $property, $value)
    {
        /*
         * Is the property being set one of those in our column-to-property
         * map? If so, then we're looking at a column name and have to convert
         * it to the corresponding property.
         */
        if (array_key_exists($property, $this->columnToPropertyMap)) {
            $property = $this->columnToPropertyMap[$property];
        }

        if (false === property_exists($entity, $property)) {
            return false;
        }

        $setterMethod = 'set' . ucfirst($property);

        if (true === method_exists($entity, $setterMethod)) {
            $entity->$setterMethod($value);
            return true;
        }

        $reflectedProperty = new \ReflectionProperty($entity, $property);
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($value);

        return true;
    }

    /**
     * Validate that an entity is an object
     *
     * This is necessary because we cannot typehint a generic object with
     * PHP 5.3 which is the target for this framework.
     *
     * @param $entity
     *
     * @throws \InvalidArgumentException
     */
    private function validateObject($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(self::ERROR_ENTITY_NOT_OBJECT);
        }
    }
}
