<?php
/**
 * Proxy Test File
 *
 * @package Nerdery\Tests\WordPress\Proxy
 */

namespace Nerdery\Tests\WordPress\Proxy {

use Nerdery\Tests\BaseTestCase;
use Nerdery\WordPress\Proxy;

/**
 * Proxy
 *
 * @uses BaseTestCase
 * @package Nerdery\Tests\WordPress\Proxy
 * @author Jansen Price <jansen.price@nerdery.com>
 * @version $Id$
 */
class ProxyTest extends BaseTestCase
{
    /**
     * setUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->object = new Proxy();
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * testGetDatabaseFailsWithoutGlobalWpdb
     *
     * @return void
     */
    public function testGetDatabaseFailsWithoutGlobalWpdb()
    {
        $this->setExpectedException('UnexpectedValueException', 'Global $wpdb is not of class \wpdb');
        $db = $this->object->getDatabase();
    }

    /**
     * testGetDatabase
     *
     * @return void
     */
    public function testGetDatabase()
    {
        // Required in order to allow access like WP does
        global $wpdb;

        $wpdb = $this->getMock('wpdb');

        $db = $this->object->getDatabase();

        $this->assertSame($wpdb, $db);
    }

    /**
     * testGetWordPress
     *
     * @return void
     */
    public function testGetWordPress()
    {
        global $wp;

        $wp = 'hiho';

        $actual = $this->object->getWordPress();

        $this->assertSame($wp, $actual);
    }

    /**
     * testAddAction
     *
     * @return void
     */
    public function testAddAction()
    {
        $result = $this->object->addAction('a', 'b');
        $this->assertEquals('ab', $result);
    }

    /**
     * testAddFilter
     *
     * @return void
     */
    public function testAddFilter()
    {
        $result = $this->object->addFilter('a', 'b');
        $this->assertEquals('ab101', $result);

        $result = $this->object->addFilter('a', 'b', 'c');
        $this->assertEquals('abc1', $result);

        $result = $this->object->addFilter('a', 'b', 'c', 'd');
        $this->assertEquals('abcd', $result);
    }

    /**
     * testAddSubmenuPage
     *
     * @return void
     */
    public function testAddSubmenuPage()
    {
        $result = $this->object->addSubmenuPage('a', 'b', 'c', 'd', 'e');
        $this->assertEquals('abcdeNULL', $result);

        $result = $this->object->addSubmenuPage('a', 'b', 'c', 'd', 'e', 'f');
        $this->assertEquals('abcdef', $result);
    }

    /**
     * testAddMenuPage
     *
     * @return void
     */
    public function testAddMenuPage()
    {
        $result = $this->object->addMenuPage('a', 'b', 'c', 'd', 'e');
        $this->assertEquals('abcdeNULLNULL', $result);

        $result = $this->object->addMenuPage('a', 'b', 'c', 'd', 'e', 'f');
        $this->assertEquals('abcdefNULL', $result);

        $result = $this->object->addMenuPage('a', 'b', 'c', 'd', 'e', 'f', 'g');
        $this->assertEquals('abcdefg', $result);
    }

    /**
     * testDoAction
     *
     * @return void
     */
    public function testDoAction()
    {
        $result = $this->object->doAction('a');
        $this->assertEquals('a', $result);
    }

    /**
     * testAddRewriteRule
     *
     * @return void
     */
    public function testAddRewriteRule()
    {
        $result = $this->object->addRewriteRule('a', 'b');
        $this->assertEquals('abtop', $result);

        $result = $this->object->addRewriteRule('a', 'b', 'c');
        $this->assertEquals('abc', $result);
    }

    /**
     * testGetOption
     *
     * @return void
     */
    public function testGetOption()
    {
        $result = $this->object->getOption('a');
        $this->assertEquals('a', $result);
    }

    /**
     * testFlushRewriteRules
     *
     * @return void
     */
    public function testFlushRewriteRules()
    {
        $result = $this->object->flushRewriteRules();
        $this->assertEquals('TRUE', $result);

        $result = $this->object->flushRewriteRules(false);
        $this->assertEquals('FALSE', $result);
    }

    /**
     * testSettingsFields
     *
     * @return void
     */
    public function testSettingsFields()
    {
        $result = $this->object->settingsFields('a');
        $this->assertEquals('ECHOEDa', $result);
    }

    /**
     * testDoSettingsSections
     *
     * @return void
     */
    public function testDoSettingsSections()
    {
        $result = $this->object->doSettingsSections('a');
        $this->assertEquals('ECHOEDa', $result);
    }

    /**
     * testRegisterSetting
     *
     * @return void
     */
    public function testRegisterSetting()
    {
        $result = $this->object->registerSetting('a', 'b');
        $this->assertEquals('abNULL', $result);

        $result = $this->object->registerSetting('a', 'b', 'c');
        $this->assertEquals('abc', $result);
    }

    /**
     * testAddSettingsSection
     *
     * @return void
     */
    public function testAddSettingsSection()
    {
        $result = $this->object->addSettingsSection('a', 'b');
        $this->assertEquals('abNULLNULL', $result);

        $result = $this->object->addSettingsSection('a', 'b', 'c');
        $this->assertEquals('abcNULL', $result);

        $result = $this->object->addSettingsSection('a', 'b', 'c', 'd');
        $this->assertEquals('abcd', $result);
    }

    /**
     * testAddSettingsField
     *
     * @return void
     */
    public function testAddSettingsField()
    {
        $result = $this->object->addSettingsField('a', 'b', 'c', 'd', 'e');
        $this->assertEquals('abcde', $result);
    }

    /**
     * testAddShortCode
     *
     * @return void
     */
    public function testAddShortCode()
    {
        $result = $this->object->addShortCode('a', 'b');
        $this->assertEquals('ab', $result);
    }

    /**
     * testWpEnqueueScript
     *
     * @return void
     */
    public function testWpEnqueueScript()
    {
        $result = $this->object->wpEnqueueScript('a');
        $this->assertEquals('aNULLARRAYNULLFALSE', $result);
    }

    /**
     * testWpEnqueueStyle
     *
     * @return void
     */
    public function testWpEnqueueStyle()
    {
        $result = $this->object->wpEnqueueStyle('a', 'b');
        $this->assertEquals('abARRAYNULLall', $result);
    }

    /**
     * testAdminLink
     *
     * @return void
     */
    public function testAdminLink()
    {
        $result = $this->object->adminLink('hehe');
        $this->assertEquals('admin.php?page=hehe', $result);
    }

    /**
     * testSanitizeEmail
     *
     * @return void
     */
    public function testSanitizeEmail()
    {
        $result = $this->object->sanitizeEmail('a@b.com');
        $this->assertEquals('a@b.com', $result);
    }

    /**
     * testSanitizeString
     *
     * @return void
     */
    public function testSanitizeString()
    {
        $result = $this->object->sanitizeString('a');
        $this->assertEquals('a', $result);
    }

    /**
     * testRedirect
     *
     * @return void
     */
    public function testRedirect()
    {
        $result = $this->object->redirect('a');
        $this->assertNull($result);
    }

    /**
     * testAddAdminNotice
     *
     * @return void
     */
    public function testAddAdminNotice()
    {
        $result = $this->object->addAdminNotice('message1');
        $this->assertEquals('admin_notices<div class="update">message1</div>', $result);

        $result = $this->object->addAdminNotice('message2', 'error');
        $this->assertEquals('admin_notices<div class="error">message2</div>', $result);

        $result = $this->object->addAdminNotice('message3', 'haha');
        $this->assertEquals('admin_notices<div class="update">message3</div>', $result);
    }

    /**
     * testRegisterAdminRoute
     *
     * @return void
     */
    public function testRegisterAdminRoute()
    {
        $result = $this->object->registerAdminRoute('a', 'b', 'c');
        $this->assertEquals('NULLNULLNULLbac', $result);
    }

    /**
     * testPluginBasename
     *
     * @return void
     */
    public function testPluginBasename()
    {
        $result = $this->object->pluginBasename('a');
        $this->assertEquals('a', $result);
    }

    /**
     * testPluginsUrl
     *
     * @return void
     */
    public function testPluginsUrl()
    {
        $result = $this->object->pluginsUrl();
        $this->assertEquals('NULL', $result);

        $result = $this->object->pluginsUrl('a');
        $this->assertEquals('a', $result);
    }

    /**
     * testIsAdmin
     *
     * @return void
     */
    public function testIsAdmin()
    {
        $result = $this->object->isAdmin();
        $this->assertTrue($result);
    }
}

} // End namespace

/**
 * This namespace is to handle all the fake WP functions. They will be called 
 * by the object under test.
 */
namespace Nerdery\WordPress
{
    /**
     * Special function to return what was passed in to a function
     *
     * @param mixed $args
     * @return void
     */
    function concatTheArgs($args)
    {
        $result = array();
        foreach ($args as $arg) {
            if (null === $arg) {
                $result[] = 'NULL';
            } elseif (false === $arg) {
                $result[] = 'FALSE';
            } elseif (true === $arg) {
                $result[] = 'TRUE';
            } elseif (is_array($arg)) {
                $result[] = 'ARRAY';
            } elseif (is_callable($arg)) {
                ob_start();
                $arg();
                $result[] = ob_get_clean();
            } else {
                $result[] = (string) $arg;
            }
        }

        return implode('', $result);
    }

    function add_action()
    {
        return concatTheArgs(func_get_args());
    }

    function add_filter()
    {
        return concatTheArgs(func_get_args());
    }

    function add_submenu_page()
    {
        return concatTheArgs(func_get_args());
    }

    function add_menu_page()
    {
        return concatTheArgs(func_get_args());
    }

    function do_action()
    {
        return concatTheArgs(func_get_args());
    }

    function add_rewrite_rule()
    {
        return concatTheArgs(func_get_args());
    }

    function get_option()
    {
        return concatTheArgs(func_get_args());
    }

    function flush_rewrite_rules()
    {
        return concatTheArgs(func_get_args());
    }

    function settings_fields($groupName)
    {
        echo 'ECHOED' . $groupName;
    }

    function do_settings_sections($groupName)
    {
        echo 'ECHOED' . $groupName;
    }

    function register_setting()
    {
        return concatTheArgs(func_get_args());
    }

    function add_settings_section()
    {
        return concatTheArgs(func_get_args());
    }

    function add_settings_field()
    {
        return concatTheArgs(func_get_args());
    }

    function add_shortcode()
    {
        return concatTheArgs(func_get_args());
    }

    function wp_enqueue_script()
    {
        return concatTheArgs(func_get_args());
    }

    function wp_enqueue_style()
    {
        return concatTheArgs(func_get_args());
    }

    function sanitize_email()
    {
        return concatTheArgs(func_get_args());
    }

    function sanitize_text_field()
    {
        return concatTheArgs(func_get_args());
    }

    function wp_redirect()
    {
        return concatTheArgs(func_get_args());
    }

    function plugin_basename()
    {
        return concatTheArgs(func_get_args());
    }

    function plugins_url()
    {
        return concatTheArgs(func_get_args());
    }

    function is_admin()
    {
        return true;
    }
}
