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
     * Route string
     *
     * @var string
     */
    private $hook;

    /**
     * Constructor
     *
     * @param string $url
     * @param string $handler
     *
     * @return Route
     */
    public function __construct($url, $handler)
    {
        $this->setUrl($url);
        $this->setHandler($handler);
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
     * @param string $handler Ex: controller.foo:barAction
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setHandler($handler)
    {
        list($controller, $action) = explode(':', $handler);

        $this->controllerName = $controller;
        $this->actionName = $action;

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

    /**
     * Set the hook
     *
     * @param string $hook
     *
     * @return self
     */
    public function setHook($hook)
    {
        $this->hook = $hook;

        return $this;
    }

    /**
     * Get the hook
     *
     * @return string
     */
    public function getHook()
    {
        return $this->hook;
    }
}
