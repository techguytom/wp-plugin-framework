<?php
/**
 * File Router.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin\Router;

use Nerdery\Plugin\Router\Route;
use Nerdery\Plugin\Plugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Router
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Router
{
    /*
     * Define constants
     */
    const QUERY_PARAM_PLUGIN = '__plugin';
    const QUERY_PARAM_ACTION = '__action';

    /**
     * Route container
     *
     * @var Route[]
     */
    private $routes = array();

    /**
     * Container
     *
     * The router needs the plugin IoC container to be able to map
     * a route key, e.g. "controller.foo:barAction" to an actual
     * controller object in the IoC.
     *
     * @var Plugin
     */
    private $container;

    /**
     * Constructor
     *
     * @param Plugin $container
     * @return self
     */
    public function __construct(Plugin $container)
    {
        $this->container = $container;
        $this->initialize();
        return $this;
    }

    /**
     * Initialize the router
     *
     * This method hooks into the WordPress core events to facilitate
     * routing.
     *
     * @return $this
     */
    private function initialize()
    {
        /*
         * This is necessary while this framework supports PHP 5.3, in version
         * 5.4 PHP started automatically importing the current context into a
         * closure, but for now we have to manually import it.
         */
        $router = $this;

        $container = $this->getContainer();
        $wordPressProxy = $container->getProxy();

        $wordPressProxy->addAction('init', function () use ($router) {
            $router->registerEndpoints();
        }, 0);

        $wordPressProxy->addAction('parse_request', function () use ($router) {
            $router->inspectRequests();
        }, 0);

        $wordPressProxy->addFilter('query_vars', function ($queryVars) use ($router) {
            return $router->addQueryVars($queryVars);
        });

        return $this;
    }

    /**
     * addRoute
     *
     * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
     *
     * @param Route $route
     *
     * @return self
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * Get the routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Register endpoints
     *
     * A major risk to registering these custom routes is
     *
     * @throws \UnexpectedValueException
     * @return void
     */
    public function registerEndpoints()
    {


        // @todo Catch this upon activation.
        if (false === $this->permalinksEnabled()) {
            throw new \UnexpectedValueException(
                'Permalinks must be enabled for rewrite rules to work.'
            );
        }

        /** @var Route $route */
        foreach ($this->getRoutes() as $route) {

            if (null === $route->getUrl()) {
                continue;
            }

            $this->registerEndpoint($route);
        }

        $this->getContainer()->getProxy()->flushRewriteRules(true);
    }

    /**
     * Register a single endpoint
     *
     * Registering an endpoint involves setting a url rewrite using the
     * WordPress permalink API. After that, we subscribe to our own custom
     * hook that was set by the request listener. When the event is triggered
     * we map the callback to a controller action.
     *
     * @param Route $route
     * @throws \UnexpectedValueException
     */
    public function registerEndpoint(Route $route)
    {
        $container = $this->getContainer();
        $slug = $container->getSlug();
        $proxy = $container->getProxy();
        $hookName = $this->generateHookName();
        $controllerName = $route->getControllerName();
        $actionName = $route->getActionName();

        $toUrl = sprintf(
            'index.php?%s=%s&%s=%s',
            self::QUERY_PARAM_PLUGIN,
            $slug,
            self::QUERY_PARAM_ACTION,
            $hookName
        );

        $proxy->addRewriteRule($route->getUrl(), $toUrl);

        $controller = $container[$controllerName];

        if (null === $controller) {
            throw new \UnexpectedValueException(
                sprintf('Unable to find controller service "%s".', $controllerName)
            );
        }

        if (!method_exists($controller, $actionName)) {
            throw new \UnexpectedValueException(
                sprintf('Unable to find controller action "%s".', $actionName)
            );
        }

        $proxy->addAction($hookName, function () use ($container, $controller, $actionName) {
            $request = $container->getRequest();
            $response = $controller->$actionName($request, new Response());

            if ($response instanceof Response) {
                $response->send();
            } else {
                echo $response;
            }

            exit;
        });
    }

    /**
     * Generate a random hook name
     *
     * @return string
     */
    private function generateHookName()
    {
        $hash = substr(md5(uniqid(null, true)), 0, 12);
        return $hash;
    }

    /**
     * Validate permalink settings
     *
     * This plugin requires permalinks (url rewrites) to be enabled at
     * the global level, this is done in the admin settings:
     * Settings > Permalinks > Any setting except for "Default" must be selected
     *
     * @todo Make the error output more friendly
     *
     * @return bool
     */
    private function permalinksEnabled()
    {
        $container = $this->getContainer();
        $proxy = $container->getProxy();
        $permalinkStrategy = $proxy->getOption('permalink_structure');
        return (empty($permalinkStrategy)) ? false : true;
    }

    /**
     * Inspect all requests
     *
     * This method is called on every page request to the site, it's logic
     * inspects the request looking for the custom $_GET parameters that this
     * controller has registered and if it finds them, it will dispatch a
     * custom hook to an action to handle the request.
     *
     * @return void
     */
    public function inspectRequests()
    {
        $container = $this->getContainer();
        $proxy = $container->getProxy();
        $wp = $proxy->getWordPress();

        if (!isset($wp->query_vars[self::QUERY_PARAM_PLUGIN])) {
            return;
        }

        $plugin = $wp->query_vars[self::QUERY_PARAM_PLUGIN];

        if ($plugin !== $container->getSlug()) {
            return;
        }

        if (!isset($wp->query_vars[self::QUERY_PARAM_ACTION])) {
            return;
        }

        $action = $wp->query_vars[self::QUERY_PARAM_ACTION];

        $this->dispatch($action);
    }

    /**
     * Dispatch a hook
     *
     * @param string $hookName
     */
    public function dispatch($hookName)
    {
        $container = $this->getContainer();
        $wordPressProxy = $container->getProxy();
        $wordPressProxy->doAction($hookName);
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
     * Register query variables
     *
     * In order for the WordPress core request parser to pick up our custom
     * $_GET parameters we must register them.
     *
     * @param array $vars
     * @return array
     */
    public function addQueryVars(array $vars)
    {
        $vars[] = self::QUERY_PARAM_ACTION;
        $vars[] = self::QUERY_PARAM_PLUGIN;
        return $vars;
    }
}
