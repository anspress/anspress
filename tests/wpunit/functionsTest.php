<?php

class functionsTest extends \Codeception\TestCase\WPTestCase {

	use AnsPress\Tests\Testcases\Common;
	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * @covers ::ap_get_short_link
	 */
	public function testApGetShortLink() {
		$id    = $this->insert_question();
		$url   = ap_get_short_link( [ 'ap_q' => $id ] );
		$query = wp_parse_args( wp_parse_url( $url )['query'] );
		$this->assertEquals( 'shortlink', $query['ap_page'] );
		$this->go_to( ap_get_short_link( [ 'ap_q' => $id ] ) );
		$this->assertEquals( 'shortlink', get_query_var( 'ap_page' ) );
	}

	/**
	 * @covers ::ap_base_page_slug
	 */
	public function testApBasePageSlug() {
		$id = $this->factory->post->create(
			[
				'post_type' => 'page',
				'post_name' => 'qqqqslug',
			]
		);
		ap_opt( 'base_page', $id );
		$this->assertEquals( 'qqqqslug', ap_base_page_slug() );
	}

	/**
	 * @covers ::ap_base_page_link
	 */
	public function testApBasePageLink() {
		ap_opt( 'base_page', '' );
		$this->assertEquals( home_url( '/questions/' ), ap_base_page_link() );
		$id = $this->factory->post->create(
			[
				'post_type' => 'page',
				'post_name' => 'qqqqslugqq',
			]
		);
		ap_opt( 'base_page', $id );
		$this->assertEquals( get_permalink( $id ), ap_base_page_link() );
	}

	/**
	 * @covers ::ap_get_theme_location
	 */
	public function testApGetThemeLocation() {
		$file       = 'test.php';
		$template_f = get_template_directory() . '/anspress/';
		if ( ! file_exists( $template_f ) ) {
			mkdir( $template_f );
		}

		if ( file_exists( $template_f . $file ) ) {
			unlink( $template_f . $file );
		}

		$plugin_path = ABSPATH . 'testplugin';

		$this->assertEquals( ANSPRESS_THEME_DIR . '/' . $file, ap_get_theme_location( $file ) );
		$this->assertEquals( $plugin_path . '/templates/' . $file, ap_get_theme_location( $file, $plugin_path ) );

		file_put_contents( $template_f . $file, '<?php' );
		$this->assertEquals( $template_f . $file, ap_get_theme_location( $file ) );
	}

	/**
	 * @covers :: ap_get_theme_url
	 */
	public function testApGetThemeUrl() {
		$file = 'test.css';

		$template_f = get_template_directory() . '/anspress/';
		if ( ! file_exists( $template_f ) ) {
			mkdir( $template_f );
		}
		if ( file_exists( $template_f . $file ) ) {
			unlink( $template_f . $file );
		}

		$plugin_url = home_url( 'wp-content/plugins/testplugin/' );

		$this->assertEquals( ANSPRESS_URL . 'templates/' . $file, ap_get_theme_url( $file, false, false ) );
		$this->assertEquals( $plugin_url . 'templates/' . $file, ap_get_theme_url( $file, $plugin_url, false ) );

		file_put_contents( $template_f . $file, ' ' );
		$this->assertEquals( get_template_directory_uri() . '/anspress/' . $file, ap_get_theme_url( $file, false, false ) );
	}

	/**
	 * @covers ::is_question
	 */
	public function testIsQuestion() {
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertTrue( is_question() );
	}

	/**
	 * @covers ::is_ask
	 */
	// public function testIsAsk() {
	// 	$this->assertFalse( is_ask() );
	// 	$id = $this->factory->post->create(
	// 		[
	// 			'post_type'  => 'page',
	// 			'post_name'  => 'asksssd3432s',
	// 			'post_title' => 'ask page',
	// 		]
	// 	);
	// 	ap_opt( 'ask_page', $id );
	// 	ap_opt( 'ask_page_id', 'asksssd3432s' );
	// 	$this->go_to( '?post_type=page&p=' . $id );
	// 	$this->assertTrue( is_ask() );
	// }

	/**
	 * @covers ::get_question_id
	 */
	public function testGetQuestionID() {
		$this->assertFalse( get_question_id() );
		$id = $this->insert_question();
		$this->go_to( '?post_type=question&p=' . $id );
		$this->assertEquals( $id, get_question_id() );
	}

	/**
	 * @covers ::ap_human_time
	 */
	public function testApHumanTime() {
		$this->assertEquals( '1 minute ago', ap_human_time( current_time( 'mysql' ), false ) );
		$this->assertEquals( '1 minute ago', ap_human_time( current_time( 'U' ) ) );
		$this->assertEquals( date_i18n( get_option( 'date_format' ), current_time( 'U' ) ), ap_human_time( current_time( 'U' ), true, 0 ) );
		$this->assertEquals( date_i18n( 'M Y', current_time( 'U' ) ), ap_human_time( current_time( 'U' ), true, 0, 'M Y' ) );
	}

	/**
	 * @covers ::ap_is_user_answered
	 */
	public function testApIsUserAnswered() {
		$this->setRole( 'subscriber' );
		$id = $this->insert_answer();
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
		$id = $this->insert_answer( '', '', get_current_user_id() );
		$this->assertTrue( ap_is_user_answered( $id->q, get_current_user_id() ) );
		wp_delete_post( $id->a, true );
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
		$id = $this->insert_answer( '', '', get_current_user_id() );
		wp_delete_post( $id->a );
		$this->assertFalse( ap_is_user_answered( $id->q, get_current_user_id() ) );
	}

	/**
	 * @covers ::ap_answers_link
	 */
	public function testApAnswersLink() {
		$id = $this->insert_answer();
		$this->assertEquals( get_permalink( $this->q ) . '#answers', ap_answers_link( $this->q ) );
	}

	/**
	 * @covers ::ap_post_edit_link
	 */
	public function testApPostEditLink() {
		$id    = $this->insert_answer();
		$nonce = wp_create_nonce( 'edit-post-' . $id->q );
		$this->assertEquals( ap_get_link_to( 'ask' ) . '?id=' . $id->q . '&__nonce=' . $nonce, ap_post_edit_link( $id->q ) );
		$nonce = wp_create_nonce( 'edit-post-' . $id->a );
		$this->assertEquals( ap_get_link_to( 'edit' ) . '?id=' . $id->a . '&__nonce=' . $nonce, ap_post_edit_link( $id->a ) );
	}

	/**
	 * @covers ::sanitize_comma_delimited
	 */
	public function testSanitizeCommaDelimited() {
		$string = '122, sfdsf<87, jhj,, 87&&,00000, &amp;';
		$this->assertEquals( '122,0,0,87,0,0', sanitize_comma_delimited( $string ) );

		$array = array(
			'<?pgp223',
			'shdshds((*8',
			'77878',
			'8 8',
			'&&&&***',
		);

		$this->assertEquals( '0,0,77878,8,0', sanitize_comma_delimited( $array ) );
		$this->assertEquals(
			'"dsfsdf??","function(){","sdsd","sdsdsds86789"', sanitize_comma_delimited(
				array(
					'dsfsdf<>??',
					'function(){',
					',,,""sdsd',
					'\'sdsdsds86789',
				), 'str'
			)
		);
	}

	/**
	 * @covers ::ap_form_allowed_tags
	 */
	public function testApFormAllowedTags() {
		$tags = ap_form_allowed_tags();
		$this->assertArraySubset(
			[
				'style' => array(
					'align' => true,
				),
				'title' => true,
			], $tags['p']
		);

		$this->assertArraySubset(
			[
				'style' => array(
					'align' => true,
				),
			], $tags['span']
		);

		$this->assertArraySubset(
			[
				'href'  => true,
				'title' => true,
			], $tags['a']
		);
		$this->assertEquals( [], $tags['br'] );
		$this->assertEquals( [], $tags['em'] );
		$this->assertArraySubset(
			[
				'style' => array(
					'align' => true,
				),
			], $tags['strong']
		);
		$this->assertEquals( [], $tags['pre'] );
		$this->assertEquals( [], $tags['code'] );
		$this->assertEquals( [], $tags['blockquote'] );
		$this->assertArraySubset(
			[
				'style' => array(
					'align' => true,
				),
				'src'   => true,
			], $tags['img']
		);
		$this->assertEquals( [], $tags['ul'] );
		$this->assertEquals( [], $tags['ol'] );
		$this->assertEquals( [], $tags['li'] );
		$this->assertEquals( [], $tags['del'] );
	}

}
