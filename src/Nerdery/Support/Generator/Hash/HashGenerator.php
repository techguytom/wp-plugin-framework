<?php
/**
 * File HashGenerator.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Support\Generator\Hash;

use Nerdery\Support\Generator\GeneratorInterface;

/**
 * Class HashGenerator
 *
 * @package Nerdery\Support\Generator
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class HashGenerator implements GeneratorInterface
{
    public function generate($salt = null)
    {
        return crypt(microtime(), $salt);
    }
} 
