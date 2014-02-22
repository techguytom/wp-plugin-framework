<?php
/**
 * File Proxy.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\WordPress;

/**
 * Class Proxy
 *
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Proxy
{
    /**
     * Get the WP global database connection
     *
     * @return \wpdb
     * @throws \UnexpectedValueException if $wpdb is not of class \wpdb
     */
    public function getDatabase()
    {
        global $wpdb;

        if (false === ($wpdb instanceof \wpdb)) {
            throw new \UnexpectedValueException(
                'Global $wpdb is not of class \wpdb'
            );
        }

        return $wpdb;
    }

    /**
     * Get WordPress instance
     *
     * @return object
     */
    public function getWordPress()
    {
        global $wp;
        return $wp;
    }

    /**
     * Add an action
     *
     * @param string $actionName
     * @param array|callable $callback
     *
     * @return bool|void
     */
    public function addAction($actionName, $callback)
    {
        return add_action($actionName, $callback);
    }

    /**
     * Add a filter
     *
     * @param string $tag
     * @param callable|array $callable
     * @param int $priority
     * @param int $acceptedArgs
     *
     * @return bool|void
     */
    public function addFilter($tag, $callable, $priority = 10, $acceptedArgs = 1)
    {
        return add_filter($tag, $callable, $priority, $acceptedArgs);
    }

    /**
     * Add sub menu
     *
     * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
     *
     * @param string $parentSlug
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param callable $callable
     *
     * @return bool|string
     */
    public function addSubmenuPage($parentSlug, $pageTitle, $menuTitle, $capability, $menuSlug, $callable = null)
    {
        add_submenu_page(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $callable
        );
    }

    /**
     * addMenuPage
     *
     * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
     *
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param array|string $function
     * @param string $iconUrl
     * @param int $position
     *
     * @return string Returns the sub-hook name
     */
    public function addMenuPage($pageTitle, $menuTitle, $capability, $menuSlug, $function, $iconUrl = null, $position = null)
    {
        return add_menu_page(
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $function,
            $iconUrl,
            $position
        );
    }

    /**
     * doAction
     *
     * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
     *
     * @param string $actionName
     *
     * @return null
     */
    public function doAction($actionName)
    {
        return do_action($actionName);
    }

    /**
     * Add rewrite rule
     *
     * @param string $regex
     * @param string $redirect
     * @param string $after Optional
     *
     * @return void
     */
    public function addRewriteRule($regex, $redirect, $after = 'top')
    {
        add_rewrite_rule($regex, $redirect, $after);
    }

    /**
     * Get global WordPress option value
     *
     * @param $optionName
     *
     * @return mixed|void
     */
    public function getOption($optionName)
    {
        return get_option($optionName);
    }

    /**
     * flushRewriteRules
     *
     * @param bool $hardReset
     *
     * @return void
     */
    public function flushRewriteRules($hardReset = true)
    {
        flush_rewrite_rules($hardReset);
    }

    /**
     * settingsFields
     *
     * Wrapper for settings_fields
     *
     * @param string $groupName
     *
     * @return string
     */
    public function settingsFields($groupName)
    {
        $result = $this->buffer(function () use ($groupName) {
            settings_fields($groupName);
        });

        return $result;
    }

    /**
     * Get settings sections
     *
     * Wrapper for do_settings_sections
     *
     * @param string $groupName
     *
     * @return string
     */
    public function doSettingsSections($groupName)
    {
        $result = $this->buffer(function () use ($groupName) {
            do_settings_sections($groupName);
        });

        return $result;
    }

    /**
     * registerSetting
     *
     * @param string $groupName
     * @param string $settingName
     * @param callable $validationCallback
     *
     * @return mixed
     */
    public function registerSetting($groupName, $settingName, $validationCallback = null)
    {
        return register_setting($groupName, $settingName, $validationCallback);
    }

    /**
     * Add a settings section
     *
     * @param string $sectionId
     * @param string $sectionTitle
     * @param callable $callback
     * @param string $adminPageSlug
     *
     * @return void
     */
    public function addSettingsSection($sectionId, $sectionTitle, $callback = null, $adminPageSlug = null)
    {
        add_settings_section($sectionId, $sectionTitle, $callback, $adminPageSlug);
    }

    /**
     * Add a settings section
     *
     * @param string $settingId
     * @param string $settingTitle
     * @param callable $callback
     * @param string $adminPageSlug
     * @param string $sectionId
     *
     * @return void
     */
    public function addSettingsField($settingId, $settingTitle, $callback, $adminPageSlug, $sectionId)
    {
        add_settings_field($settingId, $settingTitle, $callback, $adminPageSlug, $sectionId);
    }

    /**
     * Register a shortcode
     *
     * @param string $tagName
     * @param callable $callback
     *
     * @return void
     */
    public function addShortcode($tagName, $callback)
    {
        add_shortcode($tagName, $callback);
        return;
    }

    /**
     * wpEnqueueScript
     *
     * @param string $scriptName
     * @param string $url
     * @param array $dependencies
     * @param null|string $version
     * @param bool $inFooter
     *
     * @return void
     */
    public function wpEnqueueScript($scriptName, $url = null, $dependencies = array(), $version = null, $inFooter = false)
    {
        wp_enqueue_script($scriptName, $url, $dependencies, $version, $inFooter);
    }

    /**
     * wpEnqueueStyle
     *
     * @param string $scriptName
     * @param string $url
     * @param array $dependencies
     * @param null|string $version
     * @param string|bool $media
     *
     * @return void
     */
    public function wpEnqueueStyle($scriptName, $url, $dependencies = array(), $version = null, $media = 'all')
    {
        wp_enqueue_style($scriptName, $url, $dependencies, $version, $media);
    }

    /**
     * Buffer output
     *
     * @param callable $callable
     *
     * @return string
     */
    public function buffer($callable)
    {
        ob_start();
        $callable();
        return ob_get_clean();
    }

    /**
     * Generate an admin interface link
     *
     * @param string $adminMenuSlug
     *
     * @return string
     */
    public function adminLink($adminMenuSlug)
    {
        $link = sprintf('admin.php?page=%s', $adminMenuSlug);
        return $link;
    }

    /**
     * Sanitize an email address
     *
     * @param string $emailAddress
     *
     * @return string
     */
    public function sanitizeEmail($emailAddress)
    {
        $cleanEmail = sanitize_email($emailAddress);
        return $cleanEmail;
    }

    /**
     * Sanitize a text field
     *
     * @param string $input
     *
     * @return string
     */
    public function sanitizeString($input)
    {
        $cleanInput = sanitize_text_field($input);
        return $cleanInput;
    }

    /**
     * Redirect
     *
     * @param string $toUrl
     * @param int $status (200|301|302|400..etc)
     */
    public function redirect($toUrl, $status = 302)
    {
        wp_redirect($toUrl, $status);
        exit;
    }

    /**
     * Add an administrator notification message
     *
     * @param string $message update|error
     * @param string $type
     *
     * @return bool|void
     */
    public function addAdminNotice($message, $type = 'update')
    {
        $validTypes = array('update', 'error');
        if (!in_array($type, $validTypes)) {
            $type = 'update';
        }

        return $this->addAction('admin_notices', function () use ($message, $type) {
            echo '<div class="' . $type . '">' . $message . '</div>';
        });
    }

    /**
     * registerAdminRoute
     *
     * @param string $slug
     * @param string $capability
     * @param callable $handler
     *
     * @return bool|string
     */
    public function registerAdminRoute($slug, $capability, $handler)
    {
        return $this->addSubmenuPage(null, null, null, $capability, $slug, $handler);
    }

    /**
     * Get the plugin basename
     *
     * @param string $file
     *
     * @return string
     */
    public function pluginBasename($file)
    {
        return plugin_basename($file);
    }

    /**
     * Get the url to the plugin
     *
     * @param string $file
     *
     * @return string
     */
    public function pluginsUrl($file = null)
    {
        return plugins_url($file);
    }

    /**
     * Checks if the current page is an administration page.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return is_admin();
    }
}
