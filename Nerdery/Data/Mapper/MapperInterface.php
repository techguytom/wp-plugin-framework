<?php
/**
 * File MapperInterface.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Mapper;

/**
 * Interface MapperInterface
 *
 * @package ClogCulprits\Data\Mapper
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
interface MapperInterface 
{
    public function getColumnToPropertyMap();

    public function getPrimaryKeyPropertyName();

    public function getColumnByProperty($property);

    public function getPropertyByColumn($column);

    public function mapArrayColumnToProperty(array $sourceArray);

    public function mapArrayPropertyToColumn(array $sourceArray);
} 
