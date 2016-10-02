<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Form_Helper
{
	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	/**
	 * Return an instance of this class.
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public function __construct() {

		/*TODO: remove this, only anspress comment from ajax*/
		add_action('comment_post', array( $this, 'save_comment' ), 20, 2 );

		// add_action( 'ap_after_delete_comment', array($this, 'after_deleting_comment'), 10, 2 );
		add_action( 'wp_ajax_ap_submit_question', array( $this, 'ajax_question_submit' ) );
		add_action( 'wp_ajax_nopriv_ap_submit_question', array( $this, 'ajax_question_submit' ) );

		add_action( 'wp_ajax_ap_submit_answer', array( $this, 'ajax_answer_submit' ) );
		add_action( 'wp_ajax_nopriv_ap_submit_answer', array( $this, 'ajax_answer_submit' ) );

		add_action('wp_insert_comment', array( $this, 'comment_inserted' ), 99, 2 );

		add_action( 'wp_ajax_ap_new_tag', array( $this, 'ap_new_tag' ) );
		add_action( 'wp_ajax_ap_load_new_tag_form', array( $this, 'ap_load_new_tag_form' ) );
	}





	public function delete_comment() {
		$args = $args = explode('-', sanitize_text_field($_REQUEST['args'] ) );
		if ( ! ap_user_can_delete_comment($args[0] ) ) {
			$result = array( 'status' => false, 'message' => __('You do not have permission to delete this comment', 'anspress-question-answer' ) );

			wp_die(json_encode($result ) );
		}
		$action = 'delete-comment-'.$args[0];
		if ( wp_verify_nonce( $args[1], $action ) ) {
			$comment = get_comment($args[0] );
			$delete = wp_delete_comment( $args[0], true );
			if ( $delete ) {
				$post_type = get_post_type( $comment->comment_post_ID );
				do_action('ap_after_delete_comment', $comment, $post_type );

				if ( $post_type == 'question' ) {
					ap_do_event('delete_comment', $comment, 'question' ); } elseif ($post_type == 'answer')
					ap_do_event('delete_comment', $comment, 'answer' );
			}
			$result = array( 'status' => true, 'message' => __('Comment deleted successfully.', 'anspress-question-answer' ) );
			wp_die(json_encode($result ) );
		}
		wp_die();
	}

	/** TODO: Add this again */
	public function after_deleting_comment($comment, $post_type) {
		if ( $post_type == 'question' ) {
			ap_remove_parti($comment->comment_post_ID, $comment->user_id, 'comment', $comment->comment_ID );
		} elseif ( $post_type == 'answer' ) {
			$post_id = wp_get_post_parent_id($comment->comment_post_ID );
			ap_remove_parti($post_id, $comment->user_id, 'comment', $comment->comment_ID );
		}
	}


	public function comment_inserted($comment_id, $comment_object) {
		if ( $comment_object->comment_approved == '1' ) {
			$post = get_post( $comment_object->comment_post_ID );

			if ( $post->post_type == 'question' ) {
				ap_do_event('new_comment', $comment_object, 'question', '' );
				// set updated meta for sorting purpose
				update_post_meta($comment_object->comment_post_ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ) );

				// add participant
				// ap_add_parti($comment_object->comment_post_ID, $comment_object->user_id, 'comment', $comment_id);
			} elseif ( $post->post_type == 'answer' ) {
				ap_do_event('new_comment', $comment_object, 'answer', $post->post_parent );
				$post_id = wp_get_post_parent_id($comment_object->comment_post_ID );
				// set updated meta for sorting purpose
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ) );

				// add participant only
				// ap_add_parti($post_id, $comment_object->user_id, 'comment', $comment_id);
			}
		}
	}

	/**
	 * TODO: EXTENSION - move to tags
	 */
	public function ask_from_tags_field($validate) {
		if ( ap_opt('enable_tags' ) ) :
		?>
			<div class="form-group<?php echo isset($validate['tags'] ) ? ' has-error' : ''; ?>">
				<label for="tags"><?php _e('Tags', 'anspress-question-answer' ) ?></label>
                <input data-role="ap-tagsinput" type="text" value="" tabindex="5" name="tags" id="tags" class="form-control" />
				<?php echo isset($validate['tags'] ) ? '<span class="help-block">'. $validate['tags'] .'</span>' : ''; ?>
            </div>
		<?php
		endif;
	}



	public function edit_question_from_tags_field($question, $validate) {
		if ( ap_opt('enable_tags' ) ) :
			$tags_t = get_the_terms( $question->ID, 'question_tags' );
			$tags = '';

			if ( $tags_t ) {
				foreach ( $tags_t as $t ) {
					$tags .= $t->name.', ';
				}
			}

			?>
				<div class="form-group<?php echo isset($validate['tags'] ) ? ' has-error' : ''; ?>">
					<label for="tags"><?php _e('Tags', 'anspress-question-answer' ) ?></label>
					<input type="text" data-role="ap-tagsinput" value="<?php echo $tags; ?>" tabindex="5" name="tags" id="tags" class="form-control" />
					<?php echo isset($validate['tags'] ) ? '<span class="help-block">'. $validate['tags'] .'</span>' : ''; ?>
                </div>
			<?php
		endif;
	}

	public function ap_load_edit_form() {
		$nonce 			= sanitize_text_field($_POST['nonce'] );
		$post_id 	= sanitize_text_field($_POST['id'] );
		$type 			= sanitize_text_field($_POST['type'] );

		if ( wp_verify_nonce( $nonce, $type.'-'.$post_id ) ) {
			$post = get_post($post_id );

			if ( ap_user_can_edit_question($post_id ) && $post->post_type == 'question' ) {
				ob_start();
				ap_edit_question_form($post_id );
				$html = ob_get_clean();

				$result = array( 'action' => true, 'type' => 'question', 'message' => __('Form loaded.', 'anspress-question-answer' ), 'html' => $html );
			} elseif ( ap_user_can_edit_answer($post_id ) && $post->post_type == 'answer' ) {
				ob_start();
				ap_edit_answer_form($post_id );
				$html = ob_get_clean();

				$result = array( 'action' => true, 'type' => 'answer', 'message' => __('Form loaded.', 'anspress-question-answer' ), 'html' => $html );
			} else {
				$result = array( 'action' => false, 'message' => __('You do not have permission to edit this question.', 'anspress-question-answer' ) );
			}
		} else {
			$result = array( 'action' => false, 'message' => __('Something went wrong, please try again.', 'anspress-question-answer' ) );
		}

		die(json_encode($result ) );
	}

	/**
	 * Run on login_form_defaults filter so we can reset a few of the default values
	 * @param  Array $args The login default filter passed in from the wp_login_form() function
	 * @return Array       The modified arguments array
	 */
	public function login_form_defaults($args) {
		$args['label_username'] = 'Username / Email';
		$args['value_remember'] = true;
		return $args;
	}

	/**
	 * Add additional fields to bottom of the login form
	 * @param  String $content The existing content
	 * @return String
	 */
	public function login_form_bottom_html($content) {

		/*
         * Only add these AJAX login fields if the user wants
         * to use an AJAX login form
		 */
		if ( ap_opt('ajax_login' ) ) {
			$content .= '<input type="hidden" name="action" value="ap_ajax_login" />';
			$content .= wp_nonce_field( 'ap_login_nonce', '_wpnonce', true, false );
		}
		$content .= sprintf(
			'<p>%1$s <a class="ap-open-modal" href="#ap_signup_modal">%2$s</a></p>',
			__( "Don't have a user account?.", 'anspress-question-answer' ),
			__( 'Register now', 'anspress-question-answer' )
		);

		return $content;
	}


	public function ap_new_tag() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'new_tag' ) && ap_user_can_create_tag() ) {
			die(); }

		$term = wp_insert_term(
			$_POST['tag_name'],
			'question_tags', // the taxonomy
			array(
			'description' => $_POST['tag_desc'],
			)
		);
		if ( is_wp_error($term ) ) {
			$result = array( 'status' => false, 'message' => __('Unable to create tag, please try again.', 'anspress-question-answer' ) );

		} else {
			$result = array( 'status' => true, 'message' => __('Successfully created a tag.', 'anspress-question-answer' ), 'tag' => get_term_by( 'id', $term['term_id'], 'question_tags' ) );
		}
		die(json_encode($result ) );
	}

	public function ap_load_new_tag_form() {
		if ( ! wp_verify_nonce( $_REQUEST['args'], 'new_tag_form' ) && ap_user_can_create_tag() ) {
			$result = array( 'status' => false, 'message' => __('Unable to load form, please try again.', 'anspress-question-answer' ) );
		} else {
			$result = array( 'status' => true, 'message' => __('Successfully loaded form.', 'anspress-question-answer' ), 'html' => ap_tag_form() );
		}
		die(json_encode($result ) );
	}
}

function ap_edit_answer_form_hidden_input($post_id) {
	wp_nonce_field('post_nonce-'.$post_id, 'nonce' );
	echo '<input type="hidden" name="is_answer" value="true" />';
	echo '<input type="hidden" name="answer_id" value="'.$post_id.'" />';
	echo '<input type="hidden" name="edited" value="true" />';
	echo '<input type="hidden" name="submitted" value="true" />';
	echo '<input type="submit" class="btn btn-primary" value="'. __('Update Answer', 'anspress-question-answer' ). '" />';
}


function ap_tag_form() {
	$output = '';
	$output .= '<form method="POST" id="ap_new_tag_form">';
	$output .= '<strong>'.__('Create new tag', 'anspress-question-answer' ).'</strong>';
	$output .= '<input type="text" name="tag_name" class="form-control" value="" placeholder="'.__('Enter tag', 'anspress-question-answer' ).'" />';
	$output .= '<textarea type="text" name="tag_desc" class="form-control" value="" placeholder="'.__('Description of tag.', 'anspress-question-answer' ).'"></textarea>';
	$output .= '<button type="submit" class="ap-btn">'.__('Create tag', 'anspress-question-answer' ).'</button>';
	$output .= '<input type="hidden" name="action" value="ap_new_tag" />';
	$output .= '<input type="hidden" name="_nonce" value="'.wp_create_nonce('new_tag' ).'" />';
	$output .= '</form>';

	return $output;

}
