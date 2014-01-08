<?php
/**
 * File ControllerInterface.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\Plugin\Controller;

use Nerdery\Plugin;

/**
 * Interface ControllerInterface
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
interface ControllerInterface 
{
    /**
     * Initialize
     *
     * Initialize the controller, this method is called by a subscriber to the
     * WordPress hook event "init".
     *
     * @return self
     */
    public function initialize();

    /**
     * Initialize admin interface
     *
     * Initializes the controller's admin interface functionality. This method
     * is called by a subscriber to the WordPress hook event "admin_init".
     *
     * @return self
     */
    public function initializeAdmin();

    /**
     * Register an administration menu
     *
     * This method is called by a subscriber to the "admin_menu" hook. Within
     * this call, you can either register new admin menu options, or simply
     * register valid pages.
     *
     * The two most common implementations to use within this method are:
     *
     *      $this->getProxy()->addSubmenuPage();
     *      $this->getProxy()->registerAdminRoute();
     */
    public function registerAdminRoutes();

    /**
     * Plugin activation logic
     *
     * This method is called by a subscriber to the activation hook for this
     * plugin, it will be called when an administrator activates this plugin.
     */
    public function activate();

    /**
     * Plugin deactivation logic
     *
     * This method is called by a subscriber to the deactivation hook for this
     * plugin, it will be called when an administrator deactivates this plugin.
     */
    public function deactivate();

    /**
     * Get the IoC container
     */
    public function getContainer();

    /**
     * Render a view
     */
    public function render($name, array $data);
} 
