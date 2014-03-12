<?php
/**
 * File HashGenerator.php
 * 
 * @package Nerdery\Support\Generator\Hash
 */

namespace Nerdery\Support\Generator\Hash;

use Nerdery\Support\Generator\GeneratorInterface;

/**
 * Class HashGenerator
 *
 * @package Nerdery\Support\Generator\Hash
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class HashGenerator implements GeneratorInterface
{
    /**
     * generate
     *
     * @param mixed $salt
     * @return string
     */
    public function generate($salt = null)
    {
        return crypt(microtime(), $salt);
    }
}
