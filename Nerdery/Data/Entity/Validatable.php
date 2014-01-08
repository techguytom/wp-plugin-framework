<?php
/**
 * File Validatable.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Entity;

/**
 * Interface Validatable
 *
 * @package Nerdery\Plugin\Data\Entity
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
interface Validatable 
{
    /**
     * isValid
     *
     * @return bool
     */
    public function isValid();

    /**
     * hasErrors
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * getErrors
     *
     * @return array
     */
    public function getErrors();

    /**
     * addError
     *
     * @param string $message
     *
     * @return self
     */
    public function addError($message);
} 
