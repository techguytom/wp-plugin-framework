<?php
/**
 * File Factory.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin\Factory;

use Nerdery\Data\Manager\DataManager;
use Nerdery\Data\Hydrator\Hydrator;
use Nerdery\Plugin\Router\Router;
use Nerdery\Plugin\Plugin;
use Nerdery\WordPress\Gateway;
use Nerdery\WordPress\Proxy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

/**
 * Class Plugin Factory
 *
 * This class ensures that when you provision a Plugin class it is correctly
 * composed. The plugin requires some configuration options to work and this
 * factory ensures those configurations are set in the correct sequence.
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Factory
{
    /*
     * Constants
     */
    const ERROR_REQUIRED_PLUGIN_SLUG = 'You must set a plugin slug before bootstrapping this plugin.';
    const ERROR_REQUIRED_VIEW_TEMPLATE_PATH = 'You must set a valid file path to where your view templates will be.';
    const CONFIG_KEY_TEMPLATE = 'templatePath';
    const CONFIG_KEY_SLUG = 'slug';
    const CONFIG_KEY_PREFIX = 'prefix';

    /**
     * Plugin configuration
     *
     * @var array
     */
    private $configuration = array(
        self::CONFIG_KEY_TEMPLATE => null,
        self::CONFIG_KEY_PREFIX => null,
        self::CONFIG_KEY_SLUG => null,
    );

    /**
     * @var string
     */
    private $pluginFile;

    /**
     * Constructor
     *
     * @param array $configurationArray
     */
    public function __construct(array $configurationArray)
    {
        $this->configuration = array_merge($this->configuration, $configurationArray);
        $this->pluginFile = $this->getPluginFilename();
    }

    /**
     * Get the name of the plugin file
     *
     * This method uses the debug_backtrace for determining
     * which file instantiated the Plugin factory and returning
     * the path to that file <folder>/<filename>.php in the same
     * way that WordPress uses to determine the WP name of plugins
     *
     * @return string
     */
    private function getPluginFilename()
    {
        $backtrace = debug_backtrace(null, 2);
        $file = $backtrace[1]['file'];

        return $file;
    }

    /**
     * Validate this bootstrap
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function validate()
    {
        if (null === $this->configuration[self::CONFIG_KEY_TEMPLATE]) {
            throw new \InvalidArgumentException(self::ERROR_REQUIRED_VIEW_TEMPLATE_PATH);
        }

        return true;
    }

    /**
     * Register data services to a plugin instance
     *
     * Calling this method will bootstrap the Nerdery WordPress DBAL component
     * to the plugin.
     *
     * @param Plugin $plugin
     *
     * @return Plugin
     */
    public function registerDataServices(Plugin $plugin)
    {
        /*
         * Regiser the data hydrator to the container, the data hydrator is
         * a service class that allows us, with the aid of a data mapper
         * (custom per entity/repository) to persist business objects to a
         * database layer.
         *
         * This service is a dependency to the Gateway service below.
         */
        $plugin[Plugin::CONTAINER_KEY_DATA_HYDRATOR] = $plugin->factory(function ($c) {
            return new Hydrator();
        });

        /*
         * Register the database gateway service provider. This service gives
         * our plugin application access to a persistence layer. If your
         * plugin is going to use another DBAL/ORM such as Propel or Doctrine2,
         * you can
         */
        $plugin[Plugin::CONTAINER_KEY_GATEWAY] = function ($c) {
            /** @var Proxy $proxy */
            $proxy = $c[Plugin::CONTAINER_KEY_WP_PROXY];
            $wpdb = $proxy->getDatabase();
            return new Gateway(
                $wpdb,
                $c[Plugin::CONTAINER_KEY_PLUGIN_DB_PREFIX]
            );
        };

        $plugin[Plugin::CONTAINER_KEY_DB] = $plugin->factory(function ($c) {
            return new DataManager(
                $c[Plugin::CONTAINER_KEY_GATEWAY],
                $c[Plugin::CONTAINER_KEY_DATA_HYDRATOR]
            );
        });

        return $plugin;
    }

    /**
     * Build a plugin instance
     *
     * This method is a factory that wires together the plugin based on the
     * configuration options that the programmer sets the properties of this
     * bootstrap class to.
     *
     * @return Plugin
     */
    public function make()
    {
        $this->validate();

        $plugin = new Plugin();

        /*
         * Register the WordPress proxy class to our container, this class
         * provides us with an adapter to the WordPress core (global)
         * functionality that is required to interact with the Plugin api.
         */
        $plugin[Plugin::CONTAINER_KEY_WP_PROXY] = function ($c) {
            return new Proxy();
        };

        // Set configuration values
        $plugin[Plugin::CONTAINER_KEY_PLUGIN_DB_PREFIX] = $this->configuration[self::CONFIG_KEY_PREFIX];

        $plugin[Plugin::CONTAINER_KEY_PLUGIN_SLUG] = $this->configuration[self::CONFIG_KEY_SLUG];

        /*
         * This is the name that WordPress uses when referring to this plugin.
         * The name is usually in the form of <folder>/<file>.php or
         * <file>.php
         *
         * This is automatically set to whichever file instantiated the
         * factory class, which in every case, should be the WordPress
         * plugin bootstrap file.
         */
        $plugin[Plugin::CONTAINER_KEY_PLUGIN_FILE] = function ($c) {
            return $this->pluginFile;
        };

        $plugin[Plugin::CONTAINER_KEY_PLUGIN_WP_NAME] = function ($c) {
            /** @var Proxy $proxy */
            $proxy = $c[Plugin::CONTAINER_KEY_WP_PROXY];
            $pluginBasename = $proxy->pluginBasename($c[Plugin::CONTAINER_KEY_PLUGIN_FILE]);

            return $pluginBasename;
        };

        $plugin[Plugin::CONTAINER_KEY_PLUGIN_URL] = function ($c) {
            /** @var Proxy $proxy */
            $proxy = $c[Plugin::CONTAINER_KEY_WP_PROXY];
            $pluginFolder = dirname($c[Plugin::CONTAINER_KEY_PLUGIN_WP_NAME]);
            $pluginUrl = $proxy->pluginsUrl($pluginFolder);

            return $pluginUrl;
        };

        /*
         * Register the session wrapper into the container
         *
         * This session wrapper comes from the Symfony2 HTTP Foundation
         * component, it is configured here with the PhpSession bridge
         * which allows this library to function with an already initialized
         * PHP session which it is not, by default, compatible with.
         */
        $plugin[Plugin::CONTAINER_KEY_SESSION] = function ($c) {
            return new Session(new PhpBridgeSessionStorage());
        };

        /*
         * Also from the Symfony2 HTTP Foundation component we register the
         * Request wrapper to our container, this library allows us to
         * interact more smoothly with the $_POSt | $_GET and $_SERVER
         * information. This protects us from inconsistencies in HTTP server
         * request header configurations.
         */
        $plugin[Plugin::CONTAINER_KEY_REQUEST] = function ($c) {
            return Request::createFromGlobals();
        };

        /*
         * Register the router into the container, this router is responsible
         * for abstracting away much of the tediousness with regard to
         * registering public URL paths and mapping handlers to those
         * url rewrites.
         */
        $plugin[Plugin::CONTAINER_KEY_ROUTER] = function ($c) {
            return new Router($c);
        };

        /*
         * Register and configure the twig template renderer. First we set
         * the view template loader path(s).
         */
        $plugin[Plugin::CONTAINER_KEY_TWIG_PATH] = $this->configuration[self::CONFIG_KEY_TEMPLATE];
        $plugin[Plugin::CONTAINER_KEY_TWIG_LOADER] = function ($c) {
            return new \Twig_Loader_Filesystem(
                $c[Plugin::CONTAINER_KEY_TWIG_PATH]
            );
        };
        $plugin[Plugin::CONTAINER_KEY_TWIG] = function ($c) {
            return new \Twig_Environment(
                $c[Plugin::CONTAINER_KEY_TWIG_LOADER],
                array()
            );
        };

        return $plugin;
    }
}
