<?php

namespace Anspress\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase;

class TestAnsPressUpgrader extends TestCase {

	public function testClassProperties() {
		$class = new \ReflectionClass( 'AnsPress_Upgrader' );
		$this->assertTrue( $class->hasProperty( 'question_ids' ) && $class->getProperty( 'question_ids' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'answer_ids' ) && $class->getProperty( 'answer_ids' )->isPrivate() );
		$this->assertTrue( $class->hasProperty( 'meta_table_exists' ) && $class->getProperty( 'meta_table_exists' )->isPrivate() );
	}

	public function testMethodExists() {
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'get_instance' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', '__construct' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'check_tables' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'check_old_meta_table_exists' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'get_question_ids' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'question_tasks' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'migrate_votes' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'delete_question_metatables' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'answer_tasks' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'restore_last_activity' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'migrate_reputations' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'replace_old_reputation_event' ) );
		$this->assertTrue( method_exists( 'AnsPress_Upgrader', 'migrate_category_data' ) );
	}
}
