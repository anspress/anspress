<?php

namespace AnsPress\Tests\Unit;

use Yoast\WPTestUtils\BrainMonkey;


require_once __DIR__ . '/../../vendor/yoast/wp-test-utils/src/BrainMonkey/bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$GLOBALS['wp_version'] = '1.0';

define( 'PLUGIN_DIR', dirname( dirname( __DIR__ ) ) );

// Create the necessary test doubles for WP native classes on which properties are being set (PHP 8.2 compat).
BrainMonkey\makeDoublesForUnavailableClasses(
	[
		'WP',
		'WP_Post',
		'WP_Query',
		'WP_Rewrite',
		'WP_Roles',
		'WP_Term',
		'WP_User',
		'wpdb',
	]
);
