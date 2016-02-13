<?php

class AnsPressTest extends WP_UnitTestCase {
	function testAnsPressActivation() {
		// Activate AnsPress plugin.
		$result = activate_plugin('anspress-question-answer/anspress-question-answer.php');
		var_dump($result);
		if ( is_wp_error( $result ) ) {
			// Process Error
		}

	    $this->assertTrue( 
	    	is_plugin_active( 'anspress-question-answer/anspress-question-answer.php' ),
	    	'Unable to activate AnsPress plugin',
	    	'Trying to activate AnsPress'
	    );   
	}
}

