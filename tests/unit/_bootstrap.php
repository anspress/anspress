<?php
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv('TRAVIS_BUILD_DIR').'/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';
function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../../anspress-question-answer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
require $_tests_dir . '/includes/bootstrap.php';
activate_plugin( 'anspress-question-answer/anspress-question-answer.php' );
echo "Installing AnsPress...\n";

global $current_user;
$current_user = new WP_User(1);
$current_user->set_role('administrator');
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );

