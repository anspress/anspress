<?php
error_reporting( E_ALL );

define( 'WP_USE_THEMES', false );
$_tests_dir = getenv('WP_TESTS_DIR');

if ( !$_tests_dir )
	$_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../anspress-question-answer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

$ret = activate_plugin( 'anspress-question-answer/anspress-question-answer.php' );

echo "Installing AnsPress...\n";
AP_Activate::get_instance();

global $current_user;
$current_user = new WP_User(1);
$current_user->set_role('administrator');
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );


function _disable_reqs( $status = false, $args = array(), $url = '') {
	return new WP_Error( 'no_reqs_in_unit_tests', 'HTTP Requests disabled for unit tests' );
}
add_filter( 'pre_http_request', '_disable_reqs' );

require_once 'helpers/class-anspress-unittestcase.php';
require_once 'helpers/class-ap-ajaxtest.php';
