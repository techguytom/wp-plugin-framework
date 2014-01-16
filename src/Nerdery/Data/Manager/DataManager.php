<?php
/**
 * File DataManager.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Data\Manager;

use Nerdery\Data\Hydrator\Hydrator;
use Nerdery\WordPress\Gateway;

/**
 * Class DataManager
 *
 * @package ClogCulprits\Data\Manager
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class DataManager 
{
    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * Constructor
     *
     * @param Gateway $gateway
     * @param Hydrator $hydrator
     */
    public function __construct(Gateway $gateway, Hydrator $hydrator)
    {
        $this->gateway = $gateway;
        $this->hydrator = $hydrator;
    }

    /**
     * Get the gateway
     *
     * @return Gateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Get the hydrator
     *
     * @return Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
}
