<?php
/**
 * Tests environment bootstrap
 *
 * @package WpPluginFramework
 */

require_once 'BaseTestCase.php';

$root = realpath(dirname(dirname(__FILE__)));

require_once $root . '/vendor/autoload.php';
