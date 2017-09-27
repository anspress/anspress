<?php
/**
 * AnsPress options page.
 *
 * @link       https://anspress.io
 * @since      4.1.0
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Admin Pages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if user have proper rights.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_attr__( 'Trying to cheat, huh!', 'anspress-question-answer' ) );
}

/**
 * Action triggered before outputting AnsPress options page.
 *
 * @since 4.1.0
 */
do_action( 'ap_before_options_page' );

/**
 * Register AnsPress general pages options.
 *
 * @return array
 * @since 4.1.0
 */
function ap_options_general_pages() {
	$opt = ap_opt();
	$form = array(
		'submit_label' => __( 'Save Options', 'anspress-question-answer' ),
		'fields' => array(
			'base_page' => array(
				'label'   => __( 'Questions page', 'anspress-question-answer' ),
				'desc'    => __( 'Select page for displaying anspress.', 'anspress-question-answer' ),
				'type'    => 'select',
				'options' => 'posts',
				'posts_args' => array(
					'post_type' => 'page',
				),
				'value' => $opt['base_page'],
				'validate' => 'required,is_numeric',
				'sanitize' => 'absint',
			),
			'ask_page_slug' => array(
				'label' => __( 'Ask question page slug', 'anspress-question-answer' ),
				'desc'  => __( 'Set a slug for ask question page.', 'anspress-question-answer' ),
				'value' => $opt['ask_page_slug'],
				'validate' => 'required',
			),
			'question_page_slug' => array(
				'label' => __( 'Question slug', 'anspress-question-answer' ),
				'desc'  => __( 'Slug for single question page.', 'anspress-question-answer' ),
				'value' => $opt['question_page_slug'],
				'validate' => 'required',
			),
			'question_page_permalink' => array(
				'label' => __( 'Question permalink', 'anspress-question-answer' ),
				'desc'  => __( 'Select single question permalink structure.', 'anspress-question-answer' ),
				'type'  => 'radio',
				'options' => [
					'question_perma_1' => home_url( '/' . ap_base_page_slug() ) . '/<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b>/question-name/',
					'question_perma_2' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b>/question-name/',
					'question_perma_3' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b>/213/',
					'question_perma_4' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ). '</b>/213/question-name/',
				],
				'value' => $opt['question_page_permalink'],
				'validate' => 'required',
			),
			'author_credits' => array(
				'label'    => __( 'Hide author credits', 'anspress-question-answer' ),
				'desc'     => __( 'Hide link to AnsPress project site.', 'anspress-question-answer' ),
				'type'     => 'checkbox',
				'order'    => 1,
				'value'    => $opt['author_credits'],
				'validate' => 'required',
				'sanitize' => 'boolean',
			),
			'base_page_title' => array(
				'label'    => __( 'Base page title', 'anspress-question-answer' ),
				'desc'     => __( 'Main questions list page title', 'anspress-question-answer' ),
				'value'    => $opt['base_page_title'],
				'validate' => 'required',
			),
			'ask_page_title' => array(
				'label'    => __( 'Ask page title', 'anspress-question-answer' ),
				'desc'     => __( 'Title of the ask page', 'anspress-question-answer' ),
				'value'    => $opt['ask_page_title'],
				'validate' => 'required',
			),
			'search_page_title' => array(
				'label'    => __( 'Search page title', 'anspress-question-answer' ),
				'desc'     => __( 'Title of the search page', 'anspress-question-answer' ),
				'value'    => $opt['search_page_title'],
				'validate' => 'required',
			),
			'author_page_title' => array(
				'label'    => __( 'Author page title', 'anspress-question-answer' ),
				'desc'     => __( 'Title of the author page', 'anspress-question-answer' ),
				'value'    => $opt['author_page_title'],
				'validate' => 'required',
			),
			'show_solved_prefix' => array(
				'label'    => __( 'Show solved prefix', 'anspress-question-answer' ),
				'desc'     => __( 'If an answer is selected for question then [solved] prefix will be added in title.', 'anspress-question-answer' ),
				'type'     => 'checkbox',
				'value'    => $opt['show_solved_prefix'],
				'validate' => 'required',
			),
		),
	);

	return $form;
}
add_filter( 'ap_form_options_general_pages', 'ap_options_general_pages' );

/**
 * Register AnsPress general layout options.
 *
 * @return array
 * @since 4.1.0
 */
function ap_options_general_layout() {
	$opt = ap_opt();
	$form = array(
		'fields' => array(
			'load_assets_in_anspress_only' => array(
				'name'  => '',
				'label' => __( 'Load assets in AnsPress page only?', 'anspress-question-answer' ),
				'desc'  => __( 'Check this to load AnsPress JS and CSS on the AnsPress page only. Be careful, this might break layout.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['load_assets_in_anspress_only'],
			),
			'avatar_size_list' => array(
				'label'   => __( 'List avatar size', 'anspress-question-answer' ),
				'desc'    => __( 'User avatar size for questions list.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['avatar_size_list'],
			),
			'avatar_size_qquestion' => array(
				'label'   => __( 'Question avatar size', 'anspress-question-answer' ),
				'desc'    => __( 'User avatar size for question.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['avatar_size_qquestion'],
			),
			'avatar_size_qanswer' => array(
				'label'   => __( 'Answer avatar size', 'anspress-question-answer' ),
				'desc'    => __( 'User avatar size for answer.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['avatar_size_qanswer'],
			),
			'avatar_size_qcomment' => array(
				'label'   => __( 'Comment avatar size', 'anspress-question-answer' ),
				'desc'    => __( 'User avatar size for comments.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['avatar_size_qcomment'],
			),
			'question_per_page' => array(
				'label'   => __( 'Questions per page', 'anspress-question-answer' ),
				'desc'    => __( 'Questions to show per page.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['question_per_page'],
			),
			'answers_per_page' => array(
				'label'   => __( 'Answers per page', 'anspress-question-answer' ),
				'desc'    => __( 'Answers to show per page.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['answers_per_page'],
			),
		),
	);

	return $form;
}
add_filter( 'ap_form_options_general_layout', 'ap_options_general_layout' );

/**
 * Register AnsPress user access control options.
 *
 * @return array
 * @since 4.1.0
 */
function ap_options_uac() {
	$opt = ap_opt();

	$form = array(
		'fields' => array(
			'only_logged_in' => array(
				'label' => __( 'Must be logged in', 'anspress-question-answer' ),
				'desc'  => __( 'Force users to be logged in to see AnsPress contents?', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value'  => $opt['only_logged_in'],
			),
			'multiple_answers' => array(
				'label' => __( 'Multiple answers', 'anspress-question-answer' ),
				'desc'  => __( 'Allow users to submit multiple answer per question.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['multiple_answers'],
			),
			'disallow_op_to_answer' => array(
				'label' => __( 'Asker can answer', 'anspress-question-answer' ),
				'desc'  => __( 'Allow users to answer their own question.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disallow_op_to_answer'],
			),
			'allow_anonymous' => array(
				'label' => __( 'Allow anonymous', 'anspress-question-answer' ),
				'desc'  => __( 'Allow non-logged in users to post question and answer.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['allow_anonymous'],
			),
			'only_admin_can_answer' => array(
				'label' => __( 'Only admin can answer', 'anspress-question-answer' ),
				'desc'  => __( 'Allow only admins to answer all question.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['only_admin_can_answer'],
			),
			'logged_in_can_see_ans' => array(
				'label' => __( 'Must be logged in to view answers', 'anspress-question-answer' ),
				'desc'  => __( 'Only registered users can view answers.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['logged_in_can_see_ans'],
			),
			'logged_in_can_see_comment' => array(
				'label' => __( 'Must be logged in to view comments', 'anspress-question-answer' ),
				'desc'  => __( 'Only registered users can view comments.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['logged_in_can_see_comment'],
			),

			'allow_upload' => array(
				'label' => __( 'Allow image upload', 'anspress-question-answer' ),
				'desc'  => __( 'Allow logged-in users to upload image.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['allow_upload'],
			),
			'uploads_per_post' => array(
				'label' => __( 'Max uploads per post', 'anspress-question-answer' ),
				'desc'  => __( 'Set numbers of media user can upload for each post.', 'anspress-question-answer' ),
				'value'  => $opt['uploads_per_post'],
			),
			'max_upload_size' => array(
				'label' => __( 'Max upload size', 'anspress-question-answer' ),
				'desc'  => __( 'Set maximum upload size.', 'anspress-question-answer' ),
				'value' => $opt['max_upload_size'],
			),
			'allow_private_posts' => array(
				'label' => __( 'Allow private posts', 'anspress-question-answer' ),
				'desc'  => __( 'Allows users to create private question and answer. Private Q&A are only visible to admin and moderators.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['allow_private_posts'],
			),
			'multiple_answers' => array(
				'label' => __( 'Multiple Answers', 'anspress-question-answer' ),
				'desc'  => __( 'Allows users to post multiple answers on a question.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['multiple_answers'],
			),
			'new_question_status' => array(
				'label'     => __( 'Status of new question', 'anspress-question-answer' ),
				'desc'      => __( 'Default status of new question.', 'anspress-question-answer' ),
				'type'      => 'select',
				'options'   => array(
					'publish'  => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'anspress-question-answer' ),
				),
				'value'     => $opt['new_question_status'],
			),
			'edit_question_status' => array(
				'label'     => __( 'Status of edited question', 'anspress-question-answer' ),
				'desc'      => __( 'Default status of edited question.', 'anspress-question-answer' ),
				'type'      => 'select',
				'options'   => array(
					'publish'  => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'anspress-question-answer' ),
				),
				'value'     => $opt['edit_question_status'],
			),
			'new_answer_status' => array(
				'label'     => __( 'Status of new answer', 'anspress-question-answer' ),
				'desc'      => __( 'Default status of new answer.', 'anspress-question-answer' ),
				'type'      => 'select',
				'options'   => array(
					'publish'  => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'anspress-question-answer' ),
				),
				'value'     => $opt['new_answer_status'],
			),
			'edit_answer_status' => array(
				'label'     => __( 'Status of edited answer', 'anspress-question-answer' ),
				'desc'      => __( 'Default status of edited answer.', 'anspress-question-answer' ),
				'type'      => 'select',
				'options'   => array(
					'publish'  => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'anspress-question-answer' ),
				),
				'value'     => $opt['edit_answer_status'],
			),
			'anonymous_post_status' => array(
				'label'     => __( 'Status of anonymous post', 'anspress-question-answer' ),
				'desc'      => __( 'Default status of question or answer submitted by anonymous user.', 'anspress-question-answer' ),
				'type'      => 'select',
				'options'   => array(
					'publish'  => __( 'Publish', 'anspress-question-answer' ),
					'moderate' => __( 'Moderate', 'anspress-question-answer' ),
				),
				'value'     => $opt['anonymous_post_status'],
			),
		),
	);

	return $form;
}
add_filter( 'ap_form_options_uac', 'ap_options_uac' );

/**
 * Register AnsPress QA options.
 *
 * @return array
 * @since 4.1.0
 */
function ap_options_postscomments_posts() {
	$opt = ap_opt();
	$form = array(
		'fields' => array(
			'show_comments_default' => array(
				'label' => __( 'Load comments', 'anspress-question-answer' ),
				'desc'  => __( 'Show question and answer comments by default', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['show_comments_default'],
			),
			'comment_number' => array(
				'label'   => __( 'Numbers of comments to show', 'anspress-question-answer' ),
				'desc'    => __( 'Numbers of comments to load in each query?', 'anspress-question-answer' ),
				'value'   => $opt['comment_number'],
				'subtype' => 'number',
			),
			'duplicate_check' => array(
				'label' => __( 'Check duplicate', 'anspress-question-answer' ),
				'desc'  => __( 'Check for duplicate posts before posting', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['duplicate_check'],
			),
			'disable_q_suggestion' => array(
				'label' => __( 'Disable question suggestion', 'anspress-question-answer' ),
				'desc'  => __( 'Checking this will disable question suggestion in ask form', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_q_suggestion'],
			),
			'default_date_format' => array(
				'label' => __( 'Show default date format', 'anspress-question-answer' ),
				'desc'  => __( 'Instead of showing time passed i.e. 1 Hour ago, show default format date.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['default_date_format'],
			),
			'question_order_by' => array(
				'label'   => __( 'Default question order', 'anspress-question-answer' ),
				'desc'    => __( 'Order question list by default using selected', 'anspress-question-answer' ),
				'type'    => 'select',
				'options' => array(
					'voted'  => __( 'Voted', 'anspress-question-answer' ),
					'active' => __( 'Active', 'anspress-question-answer' ),
					'newest' => __( 'Newest', 'anspress-question-answer' ),
					'oldest' => __( 'Oldest', 'anspress-question-answer' ),
				),
				'value' => $opt['question_order_by'],
			),
			'keep_stop_words' => array(
				'label' => __( 'Keep stop words in question slug', 'anspress-question-answer' ),
				'desc'  => __( 'AnsPress will not strip stop words in question slug.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['keep_stop_words'],
			),
			'minimum_qtitle_length' => array(
				'label' => __( 'Minimum title length', 'anspress-question-answer' ),
				'desc'  => __( 'Set minimum letters for a question title.', 'anspress-question-answer' ),
				'subtype'  => 'number',
				'value' => $opt['minimum_qtitle_length'],
			),
			'minimum_question_length' => array(
				'label' => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc'  => __( 'Set minimum letters for a question contents.', 'anspress-question-answer' ),
				'subtype'  => 'number',
				'value' => $opt['minimum_question_length'],
			),
			'question_text_editor' => array(
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc'  => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['question_text_editor'],
			),
			'disable_comments_on_question' => array(
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc'  => __( 'Disable comments on questions.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_comments_on_question'],
			),
			'disable_voting_on_question' => array(
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc'  => __( 'Disable voting on questions.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_voting_on_question'],
			),
			'disable_down_vote_on_question' => array(
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc'  => __( 'Disable down voting on questions.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_down_vote_on_question'],
			),
			'close_selected' => array(
				'label' => __( 'Close question after selecting answer', 'anspress-question-answer' ),
				'desc'  => __( 'If enabled this will prevent user to submit answer on solved question.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['close_selected'],
			),
			'answers_sort' => array(
				'label'   => __( 'Default answers order', 'anspress-question-answer' ),
				'desc'    => __( 'Order answers by by default using selected', 'anspress-question-answer' ),
				'type'    => 'select',
				'options' => array(
					'voted'  => __( 'Voted', 'anspress-question-answer' ),
					'active' => __( 'Active', 'anspress-question-answer' ),
					'newest' => __( 'Newest', 'anspress-question-answer' ),
					'oldest' => __( 'Oldest', 'anspress-question-answer' ),
				),
				'value' => $opt['answers_sort'],
			),
			'minimum_ans_length' => array(
				'label'   => __( 'Minimum question content', 'anspress-question-answer' ),
				'desc'    => __( 'Set minimum letters for a answer contents.', 'anspress-question-answer' ),
				'subtype' => 'number',
				'value'   => $opt['minimum_ans_length'],
			),
			'answer_text_editor' => array(
				'label' => __( 'Use text editor ?', 'anspress-question-answer' ),
				'desc'  => __( 'Text editor as default.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['answer_text_editor'],
			),
			'disable_comments_on_answer' => array(
				'label' => __( 'Disable comments', 'anspress-question-answer' ),
				'desc'  => __( 'Disable comments on answer.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_comments_on_answer'],
			),
			'disable_voting_on_answer' => array(
				'label' => __( 'Disable voting', 'anspress-question-answer' ),
				'desc'  => __( 'Disable voting on answers.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_voting_on_answer'],
			),
			'disable_down_vote_on_answer' => array(
				'label' => __( 'Disable down voting', 'anspress-question-answer' ),
				'desc'  => __( 'Disable down voting on answers.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
				'value' => $opt['disable_down_vote_on_answer'],
			),
		),
	);

	return $form;
}
add_filter( 'ap_form_options_postscomments_posts', 'ap_options_postscomments_posts' );

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );
$updated = false;

// Process submit form.
if ( ! empty( $form_name ) && anspress()->get_form( $form_name )->is_submitted() ) {
	$values = anspress()->get_form( $form_name )->get_values();

	$options = get_option( 'anspress_opt', [] );

	foreach ( $values as $key => $opt ) {
		$options[ $key ] = $opt['value'];
	}

	update_option( 'anspress_opt', $options );
	wp_cache_delete( 'anspress_opt', 'ap' );
	wp_cache_delete( 'anspress_opt', 'ap' );
	$updated = true;
}

?>

<?php if ( true === $updated ) :   ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'AnsPress option updated successfully!', 'anspress-question-answer' ); ?></p>
	</div>
<?php endif; ?>

<div id="anspress" class="wrap">
	<h2 class="admin-title">
		<?php esc_html_e( 'AnsPress Options', 'anspress-question-answer' ); ?>
		<div class="social-links clearfix">
			<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
			<a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
			<a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
			<a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
		</div>
	</h2>
	<div class="clear"></div>

	<!-- <div class="anspress-imglinks">
		<a href="https://anspress.io/extensions/" target="_blank">
			<img src="<?php echo ANSPRESS_URL; ?>assets/images/more_functions.svg" />
		</a>
	</div> -->

	<div class="ap-optionpage-wrap no-overflow">
		<div class="ap-wrap">
			<div class="anspress-options ap-wrap-left clearfix">
				<div class="option-nav-tab clearfix">
					<div class="option-nav-tab clearfix">
						<h2 class="nav-tab-wrapper">
							<?php
								$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );

								$tab_links = array(
									'general'       => __( 'General', 'anspress-question-answer' ),
									'postscomments' => __( 'Posts & Comments', 'anspress-question-answer' ),
									'uac'           => __( 'User Access Control', 'anspress-question-answer' ),
									'tools'         => __( 'Tools', 'anspress-question-answer' ),
								);

								/**
								 * Hook for modifying AnsPress options tab links.
								 *
								 * @param array $tab_links Tab links.
								 * @since 4.1.0
								 */
								$tab_links = apply_filters( 'ap_options_tab_links', $tab_links );

								foreach ( $tab_links as $key => $name ) {
									echo '<a href="' . esc_url( admin_url( 'admin.php?page=anspress_options' ) ) . '&active_tab=' . esc_attr( $key ) . '" class="nav-tab ap-user-menu-' . esc_attr( $key ) . ( $key === $active_tab ? ' nav-tab-active' : '' ) . '">' . esc_html( $name ) . '</a>';
								}

								/**
								 * Action triggered right after AnsPress options tab links.
								 * Can be used to show custom tab links.
								 *
								 * @since 4.1.0
								 */
								do_action( 'ap_options_tab_links' );
							?>
						</h2>
					</div>
				</div>
				<div class="metabox-holder">
					<?php
						$active_tab = ap_sanitize_unslash( 'active_tab', 'r', 'general' );
						$form = ap_sanitize_unslash( 'ap_form_name', 'r' );
						$action_url = admin_url( 'admin.php?page=anspress_options&active_tab=' . $active_tab );
					?>
					<div class="ap-group-options">
						<?php if ( 'general' === $active_tab ) : ?>
							<p class="ap-tab-subs">
								<a href="#pages-options"><?php esc_attr_e( 'Pages Options', 'anspress-question-answer' ); ?></a>
								<a href="#layout-options"><?php esc_attr_e( 'Layout Options', 'anspress-question-answer' ); ?></a>
							</p>
							<div class="postbox">
								<h3 id="pages-options"><?php esc_attr_e( 'Pages Options', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_general_pages' )->generate( array(
											'form_action' => $action_url . '#form_options_general_pages',
										) );
									?>
								</div>
							</div>
							<div class="postbox">
								<h3 id="layout-options"><?php esc_attr_e( 'Layout Options', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_general_layout' )->generate( array(
											'form_action' => $action_url . '#form_options_general_layout',
										) );
									?>
								</div>
							</div>
						<?php elseif ( 'postscomments' === $active_tab ) : ?>
							<div class="postbox">
								<h3><?php esc_attr_e( 'Posts', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_postscomments_posts' )->generate( array(
											'form_action' => $action_url . '#form_options_postscomments_posts',
										) );
									?>
								</div>
							</div>
						<?php elseif ( 'uac' === $active_tab ) : ?>
							<p class="ap-tab-subs">
								<a href="#uac"><?php esc_attr_e( 'User Access Control', 'anspress-question-answer' ); ?></a>
								<a href="#user-roles"><?php esc_attr_e( 'User roles', 'anspress-question-answer' ); ?></a>
							</p>
							<div class="postbox">
								<h3 id="uac"><?php esc_attr_e( 'User Access Control', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php
										anspress()->get_form( 'options_uac' )->generate( array(
											'form_action' => $action_url . '#form_options_uac',
										) );
									?>
								</div>
							</div>

							<div class="postbox">
								<h3 id="user-roles"><?php esc_attr_e( 'User roles', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/roles.php'; ?>
								</div>
							</div>

						<?php elseif( 'tools' === $active_tab ): ?>
							<p class="ap-tab-subs">
								<a href="#re-count"><?php esc_attr_e( 'Re-count', 'anspress-question-answer' ); ?></a>
								<a href="#uninstall"><?php esc_attr_e( 'Uninstall', 'anspress-question-answer' ); ?></a>
							</p>
							<?php global $wpdb; ?>

							<div class="postbox">
								<h3 id="re-count"><?php esc_attr_e( 'Re-count', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/recount.php'; ?>
								</div>
							</div>

							<div class="postbox">
								<h3 id="uninstall"><?php esc_attr_e( 'Uninstall - clear all AnsPress data', 'anspress-question-answer' ); ?></h3>
								<div class="inside">
									<?php include ANSPRESS_DIR . '/admin/views/uninstall.php'; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php
							/**
							 * Action triggered in AnsPress options page content.
							 * This action can be used to show custom options fields.
							 *
							 * @since 4.1.0
							 */
							do_action( 'ap_option_page_content' );
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
/**
 * Action triggered after outputting AnsPress options page.
 *
 * @since 4.1.0
 */
do_action( 'ap_after_options_page' );

?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.postbox > h3').click(function(){
			$(this).closest('.postbox').toggleClass('closed');
		});
		$('#form_options_general_pages-question_page_slug').on('keyup', function(){
			$('.ap-base-slug').text($(this).val());
		})
	});
</script>
