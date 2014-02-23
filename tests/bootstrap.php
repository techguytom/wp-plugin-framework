<?php
/**
 * Tests environment bootstrap
 *
 * @package WpPluginFramework
 */

if (!defined('PHPUNIT_WP_PLUGIN_FRAMEWORK_IN_TESTS')) {
    define('PHPUNIT_WP_PLUGIN_FRAMEWORK_IN_TESTS', true);
}

/**
 * @see PHPUnit/Framework/TestCase.php
 */
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'BaseTestCase.php';

$root = realpath(dirname(dirname(__FILE__)));

require_once $root . '/vendor/autoload.php';

/*
 * Set up required WP constants
 */
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}
