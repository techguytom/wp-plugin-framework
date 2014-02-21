<?php
/**
 * File Mapper.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Mapper;


/**
 * Class Mapper
 *
 * This is a convenience abstract class available for developers to extend
 * that will provide all of the necessary functionality for a functional
 * data mapper.
 *
 * Your data mapper does not have to extend this so long as you provide
 * the same interfacing functionality to the Hydrator.
 *
 * @package Nerdery\Plugin\Data\Entity
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
abstract class Mapper implements MapperInterface
{
    const ERROR_EMPTY_MAP_ARRAY = 'Map array is not valid, map must not be an empty array.';

    /**
     * Column-to-property map
     *
     * This is an associative array, where each key represents a table column
     * and each matched value represents that columns analogous entity property.
     *
     * Ex:
     *      array(
     *          'column_name' => 'columnName',
     *      )
     *
     * @var array
     */
    protected $map;

    /**
     * Constructor
     *
     * @param array $map
     *
     * @throws \InvalidArgumentException
     */
    public function __constructor(array $map)
    {
        if (0 === count($map)) {
            throw new \InvalidArgumentException(self::ERROR_EMPTY_MAP_ARRAY);
        }

        $this->map = $map;
    }

    /**
     * Get the column to property map
     *
     * @return array
     */
    public function getColumnToPropertyMap()
    {
        return $this->map;
    }

    /**
     * Map an array of column keys to property keys
     *
     * @param array $sourceArray
     *
     * @return array
     */
    public function mapArrayColumnToProperty(array $sourceArray)
    {
        return $this->mapArray($sourceArray, $this->map, false);
    }

    /**
     * Map an array of property keys to column keys
     *
     * @param array $sourceArray
     *
     * @return array
     */
    public function mapArrayPropertyToColumn(array $sourceArray)
    {
        $map = array_flip($this->map);

        return $this->mapArray($sourceArray, $map, true);
    }

    /**
     * Map one set array keys to another
     *
     * If the $strict parameter is set to true, then any keys that exist in the
     * $sourceArray that are not present in the $map array will be dropped.
     * This serves to filter a source array to only those values that exist in
     * the $map.
     *
     * @param array $sourceArray
     * @param array $map
     * @param bool $strict
     *
     * @return array
     */
    private function mapArray(array $sourceArray, array $map, $strict = false)
    {
        $outputArray = array();
        foreach ($sourceArray as $sourceKey => $sourceValue) {
            if (array_key_exists($sourceKey, $map)) {
                $outputArray[$map[$sourceKey]] = $sourceValue;
            }

            if (false === $strict) {
                $outputArray[$sourceKey] = $sourceValue;
            }
        }

        return $outputArray;
    }

    /**
     * Get a column name by property
     *
     * @param string $property
     *
     * @return string
     */
    public function getColumnByProperty($property)
    {
        $map = array_flip($this->map);
        return $map[$property];
    }

    /**
     * Get a property by column name
     *
     * @param string $column
     *
     * @return string
     */
    public function getPropertyByColumn($column)
    {
        return $this->map[$column];
    }
}
