<?php
/**
 * File RepositoryInterface.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Repository;

/**
 * Interface RepositoryInterface
 *
 * @package Nerdery\Plugin\Data\Repository
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
interface RepositoryInterface 
{
    /**
     * Get the source for this repository.
     *
     * This equates to a MySQL table name.
     *
     * @return string
     */
    public function source();
}
