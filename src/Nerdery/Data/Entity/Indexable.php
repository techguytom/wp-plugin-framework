<?php
/**
 * File Indexable.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Entity;

/**
 * Interface Indexable
 *
 * @package Nerdery\Plugin\Data\Entity
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
interface Indexable
{
    public function getId();

    public function setId($index);
}
