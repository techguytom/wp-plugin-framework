<?php
/**
 * File Entity.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Entity;

/**
 * Class Entity
 *
 * @package Nerdery\Plugin\Data\Entity
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
abstract class Entity implements Validatable, Indexable
{
    /**
     * Errors container
     *
     * @var array
     */
    private $errors = array();

    /**
     * Check if the entity has errors
     *
     * {@inheritdoc}
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (count($this->errors) > 0);
    }

    /**
     * Get the errors in this entity
     *
     * {@inheritdoc}
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if this entity is valid
     *
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isValid()
    {
        return (!$this->hasErrors());
    }

    /**
     * Add an error to the entity
     *
     * {@inheritdoc}
     *
     * @param string $message
     *
     * @return self
     */
    public function addError($message)
    {
        $this->errors[] = (string) $message;

        return $this;
    }
} 
