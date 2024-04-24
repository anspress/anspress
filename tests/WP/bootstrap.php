<?php
namespace AnsPress\Tests;

use Yoast\WPTestUtils\WPIntegration;

define('COOKIEPATH', '/');

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$plugin_dir = dirname( dirname( __FILE__ ) );

require_once $plugin_dir . '/vendor/yoast/wp-test-utils/src/BrainMonkey/bootstrap.php';
require_once $plugin_dir . '/vendor/autoload.php';
