<?php
/**
 * File ControllerAbstract.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin\Controller;

use Nerdery\Plugin\Plugin;

/**
 * Class ControllerAbstract
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
abstract class Controller implements ControllerInterface
{
    /**
     * Dependency container
     *
     * Provided by the Plugin class
     *
     * @var \Nerdery\Plugin\Plugin
     */
    private $container;

    /**
     * Constructor
     */
    public function __construct(Plugin $container)
    {
        $this->container = $container;

        $slug = $container->getSlug();

        $controller = $this;

        $proxy = $this->getProxy();

        $proxy->addAction("{$slug}_initialize", function () use ($controller) {
            $controller->connect();
        });

        /*
         * Subscribe to the activation/deactivation hooks.
         * These must be here and *not* in the
         */
        $proxy->addAction("{$slug}_activation", function () use($controller) {
            $controller->activate();
        });

        $proxy->addAction("{$slug}_deactivation", function () use ($controller) {
            $controller->deactivate();
        });
    }

    /**
     * Connect the controller
     *
     * @throws \UnexpectedValueException
     * @return self
     */
    private function connect()
    {
        /*
         * Alias $this to $controller so it can be used in the action callback
         * closures, this is necessary while this framework supports PHP 5.3,
         * in version 5.4 PHP introduced $this support.
         */
        $controller = $this;

        $container = $this->getContainer();
        $proxy = $container->getProxy();
        $slug = $container->getSlug();

        if (null === $container) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Unable to connect the controller "%s" to the plugin, the controller must first have container set.',
                    get_class($this)
                )
            );
        }

        $proxy->addAction("{$slug}_admin_menu", function () use ($controller) {
            $controller->registerAdminRoutes();
        });

        $proxy->addAction('admin_init', function () use ($controller) {
            $controller->initializeAdmin();
        });

        $this->initialize();

        return $this;
    }

    /**
     * Initialize the controller
     *
     * {@inheritdoc}
     *
     * @return $this|ControllerInterface
     */
    public function initialize()
    {
        return $this;
    }

    /**
     * Initialize the administrator interface
     *
     * {@inheritdoc}
     *
     * @return $this
     */
    public function initializeAdmin()
    {
        return $this;
    }

    /**
     * Handle plugin activation
     *
     * This method is triggered by a subscription to a custom hook that is
     * created by a listener that is triggered by the WordPress plugin
     * activation hook.
     *
     * @return self
     */
    public function activate()
    {
        return $this;
    }

    /**
     * Handle plugin deactivation
     *
     * This method is triggered by a subscription to a custom hook that is
     * created by a listener that is triggered by the WordPress plugin
     * deactivation hook.
     *
     * @return $this
     */
    public function deactivate()
    {
        return $this;
    }

    /**
     * registerAdminMenu
     *
     * @return self
     */
    public function registerAdminRoutes()
    {
        return $this;
    }

    /**
     * Set the container
     *
     * @param Plugin $container
     *
     * @return self
     */
    public function setContainer(Plugin $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get the container
     *
     * @return Plugin
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the WordPress proxy
     *
     * This is a convenience method, which simply wraps the container
     * functionality.
     *
     * @return \Nerdery\WordPress\Proxy
     */
    public function getProxy()
    {
        return $this->getContainer()->getProxy();
    }

    /**
     * Get the request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->getContainer()->getRequest();
    }

    /**
     * Get flash bag
     *
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    public function getFlashBag()
    {
        $container = $this->getContainer();
        $session = $container->getSession();
        $flashBag = $session->getFlashBag();

        return $flashBag;
    }

    /**
     * Render a view
     *
     * @param string $name View name, represents a file path/name
     * @param array $data
     *
     * @return string
     */
    public function render($name, array $data = array())
    {
        // Add flash messages
        $flashBag = $this->getFlashBag();
        $data['errors'] = $flashBag->get('error', array());
        $data['notices'] = $flashBag->get('notice', array());

        $container = $this->getContainer();
        $viewRenderer = $container->getViewRenderer();
        return $viewRenderer->render($name, $data);
    }

    /**
     * Check if the current request is a POST
     *
     * @return bool
     */
    public function requestIsPost()
    {
        $requestType = $_SERVER['REQUEST_METHOD'];
        return ($requestType === 'POST');
    }

    /**
     * Check if the current request is a GET
     *
     * @return bool
     */
    public function requestIsGet()
    {
        $requestType = $_SERVER['REQUEST_METHOD'];
        return ($requestType === 'GET');
    }
}
