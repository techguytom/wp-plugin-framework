<?php
/**
 * File Route.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin\Router;

/**
 * Class Route
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Route
{
    const ERROR_URL_MUST_BE_STRING = 'Url parameter must be a string.';
    const ERROR_INVALID_HANDLER = 'Handler must be of the form controller_service_name:action_name';

    /**
     * Action name
     *
     * @var string
     */
    private $actionName;

    /**
     * Controller name
     *
     * @var string
     */
    private $controllerName;

    /**
     * Route URL
     *
     * @var string
     */
    private $url;

    /**
     * Constructor
     *
     * @param string $url
     * @param string $handler e.g.
     *
     * @throws \InvalidArgumentException
     * @return Route
     */
    public function __construct($url = null, $handler = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }

        if (!is_null($handler)) {
            $this->setHandler($handler);
        }
    }

    /**
     * Get the action
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Get the controller
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Set the handler
     *
     * @param string|callable $handler Ex: controller.foo:barAction
     *
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setHandler($handler)
    {
        if (false === is_string($handler)) {
            throw new \InvalidArgumentException(self::ERROR_INVALID_HANDLER);
        }

        $handlerParts = explode(':', $handler);

        if (count($handlerParts) < 2) {
            throw new \InvalidArgumentException(self::ERROR_INVALID_HANDLER);
        }

        $this->controllerName = $handlerParts[0];
        $this->actionName = $handlerParts[1];

        return $this;
    }

    /**
     * Set the url
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
