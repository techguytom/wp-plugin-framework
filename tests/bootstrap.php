<?php
/**
 * Tests environment bootstrap
 *
 * @package WpPluginFramework
 */

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
