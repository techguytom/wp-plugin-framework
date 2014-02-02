<?php
/**
 * File Plugin.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin;

use Nerdery\WordPress\Proxy;
use Nerdery\Plugin\Router\Route;
use Nerdery\Plugin\Router\Router;
use Pimple;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Environment;

/**
 * Class Plugin
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Plugin extends Pimple
{
    /*
     * Standard container keys
     */
    const CONTAINER_KEY_PLUGIN_WP_NAME = 'plugin.wp-name';
    const CONTAINER_KEY_PLUGIN_DB_PREFIX = 'plugin.db.prefix';
    const CONTAINER_KEY_PLUGIN_SLUG = 'plugin.slug';
    const CONTAINER_KEY_ROUTER = 'router';
    const CONTAINER_KEY_WP_PROXY = 'wp-proxy';
    const CONTAINER_KEY_WP_DBAL = 'wp-dbal';
    const CONTAINER_KEY_GATEWAY = 'gateway';
    const CONTAINER_KEY_DATA_HYDRATOR = 'data.hydrator';
    const CONTAINER_KEY_SESSION = 'session';
    const CONTAINER_KEY_TWIG_PATH = 'twig.template-path';
    const CONTAINER_KEY_TWIG_LOADER = 'twig.loader';
    const CONTAINER_KEY_TWIG = 'twig';
    const CONTAINER_KEY_DB = 'db';
    const CONTAINER_KEY_REQUEST = 'request';
    const CONTAINER_KEY_PLUGIN_FILE = 'plugin.file';
    const CONTAINER_KEY_PLUGIN_URL = 'plugin.url';

    /**
     * Initialize the plugin
     *
     * This method is called as a callback to the WordPress hook "init".
     *
     * @Todo: Add standard actions
     *
     * - wp_loaded, After WordPress is completely loaded.
     * - parse_request, Allow manipulation of HTTP request handling
     * - send_headers, Allow customization of HTTP headers
     * - shutdown, PHP execution is about to end
     *
     * @return $this
     */
    public function initialize()
    {
        /*
         * Initialize the session manager.
         */
        $this->initSession();

        /*
         * This helps resolve some of WordPress's shenanigans regarding
         * outputting headers etc. directly to the output stream. This
         * causes issues when trying to redirect users, particularly within
         * the controllers of a plugin.
         *
         * Placing this here puts all of WordPress's output into an output
         * buffer, this buffer does not have an accompanying flush, and thus
         * will flush when PHP shuts down.
         */
        ob_start();

        $plugin = $this;
        $proxy = $this->getProxy();

        /*
         * Subscribe to the WordPress core hook that is called when the admin
         * menus are bing resolved.
         *
         * @see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
         */
        $proxy->addAction('admin_menu', function() use ($plugin) {
            $plugin->registerAdminMenus();
        }, 1);

        /*
         * Subscribe to the WordPress core hook that is called just before PHP
         * is finished running and is about to shut down. This is the last
         * WordPress hook.
         *
         * @see http://codex.wordpress.org/Plugin_API/Action_Reference/shutdown
         */
        $proxy->addAction('shutdown', function() use ($plugin) {
            $plugin->shutdown();
        }, 1);

        /*
         * This action is the hook that the controllers subscribe to for
         * initialization.
         */
        $slug = $this->getSlug();
        $proxy->doAction("{$slug}_initialize");

        return $this;
    }

    /**
     * Initialize the session
     *
     * @return void
     */
    public function initSession()
    {
        /*
         * Play nice with other plugins. WordPress itself create a globally
         * usable session. For this plugin framework, we're using the Symfony2
         * HTTP Foundation component session module for session management.
         *
         * By default, this module requires that no other software create the
         * PHP session. To get around this, because it is entirely likely that
         * other plugins that may be installed along side this one have already
         * started a session, we have implemented the Session bridge.
         *
         * @see http://symfony.com/doc/current/components/http_foundation/session_php_bridge.html
         */
        if (!session_id()) {
            session_start();
        }

        $session = $this->getSession();
        if (false === $session->isStarted()) {
            $session->start();
        }
    }

    /**
     * Dispatch admin menu registration hook
     *
     * @return self
     */
    public function registerAdminMenus()
    {
        $proxy = $this->getProxy();
        $pluginSlug = $this->getSlug();

        $proxy->doAction("{$pluginSlug}_admin_menu");

        return $this;
    }

    /**
     * Activate the plugin
     *
     * @return self
     */
    public function activate()
    {
        $proxy = $this->getProxy();
        $pluginSlug = $this->getSlug();

        $proxy->doAction("{$pluginSlug}_activation");

        return $this;
    }

    /**
     * Deactivate the plugin
     *
     * @return self
     */
    public function deactivate()
    {
        $proxy = $this->getProxy();
        $pluginSlug = $this->getSlug();
        $proxy->doAction("{$pluginSlug}_deactivation");
        $proxy->flushRewriteRules(true);

        return $this;
    }

    /**
     * Shutdown
     *
     * This method is called just prior to the end of PHP parsing, after all
     * other hooks are triggered. This is the last hook in the WordPress event
     * lifecycle.
     *
     * @return void
     */
    public function shutdown()
    {
        $proxy = $this->getProxy();

        /*
         * This allows us to flush the WordPress url rewrite rules. WordPress
         * stores these in it's database by default and they are *not* updated
         * automatically when a new rewrite rule is registered, instead you
         * have to do this.
         */
        $proxy->flushRewriteRules(true);
    }

    /**
     * Add a route
     *
     * @param Route $route
     *
     * @throws \UnexpectedValueException
     */
    public function registerRoute(Route $route)
    {
        $router = $this->getRouter();

        // Require the router before setting routes
        if (null === $router) {
            throw new \UnexpectedValueException(
                'Unable to set route until a router is registered.'
            );
        }

        $router->addRoute($route);
    }

    /**
     * Get the session handler
     *
     * @return Session
     */
    public function getSession()
    {
        $session = $this[self::CONTAINER_KEY_SESSION];
        return $session;
    }

    /**
     * Get the plugin slug
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = $this[self::CONTAINER_KEY_PLUGIN_SLUG];
        return $slug;
    }

    /**
     * Get the WordPress proxy
     *
     * @return Proxy
     */
    public function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = $this[self::CONTAINER_KEY_WP_PROXY];
        return $proxy;
    }

    /**
     * Get the WordPress name for this plugin
     *
     * The WordPress name for this plugin is how the Plugin API will recognize
     * this plugin specifically. This usually resembles:
     *
     *  ex. "plugin-name/plugin-name.php"
     *
     * @return string
     */
    public function getPluginWpName()
    {
        $wpName = $this[self::CONTAINER_KEY_PLUGIN_WP_NAME];
        return $wpName;
    }

    /**
     * Get the router
     *
     * @return Router
     */
    public function getRouter()
    {
        $router = $this[self::CONTAINER_KEY_ROUTER];
        return $router;
    }

    /**
     * Get the request
     *
     * @return Request
     */
    public function getRequest()
    {
        $request = $this['request'];
        return $request;
    }

    /**
     * Get the view renderer
     *
     * @return Twig_Environment
     */
    public function getViewRenderer()
    {
        $viewRenderer = $this[self::CONTAINER_KEY_TWIG];
        return $viewRenderer;
    }

    /**
     * {@inheritDoc}
     */
    public function register($storeName, $resource)
    {
        $this[$storeName] = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function get($storeName)
    {
        if (!isset($this->$storeName)) {
            return null;
        }

        return $this[$storeName];
    }

    /**
     * Run the plugin
     *
     * @return $this
     */
    public function run()
    {
        $pluginWpName = $this->getPluginWpName();

        /*
         * Alias $this to $plugin so it can be used in the action callback
         * closures, this is necessary while this framework supports PHP 5.3,
         * in version 5.4 PHP introduced $this support.
         */
        $plugin = $this;

        $proxy = $this->getProxy();

        /*
         * These actions must be registered with the WordPress dispatcher
         * before the WordPress "init" hook is called, so we register them now
         * and set an internal subscriber on the "init" hook to finish
         * initialization.
         */
        $proxy->addAction('init', function() use ($plugin) {
            $plugin->initialize();
        });

        /*
         * Set up a subscriber for the WordPress core hook that is dispatched
         * when this plugin is activated.
         *
         * @See http://codex.wordpress.org/Function_Reference/register_activation_hook
         */
        $proxy->addAction("activate_{$pluginWpName}", function() use ($plugin) {
            $plugin->activate();
        });

        /*
         * Set up a subscriber for the WordPress core hook that is dispatched
         * when this plugin is deactivated.
         *
         * @see http://codex.wordpress.org/Function_Reference/register_activation_hook
         */
        $proxy->addAction("deactivate_{$pluginWpName}", function() use ($plugin) {
            $plugin->deactivate();
        });

        return $this;
    }
}
