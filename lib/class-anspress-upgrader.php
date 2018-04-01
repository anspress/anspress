<?php
/**
 * Holds class responsible for upgrading 3.x data to 4.x.
 *
 * @package AnsPress
 * @since 4.0.5
 */

/**
 * AnsPress upgrader class.
 *
 * @since 4.0.5
 */
class AnsPress_Upgrader {

	private $question_ids;
	private $answer_ids;

	/**
	 * Checks if old meta table exists.
	 *
	 * @var boolean
	 */
	private $meta_table_exists = false;

	/**
	 * Singleton instance.
	 *
	 * @return AnsPress_Upgrader
	 */
	public static function get_instance() {
		static $instance = null;

		if ( $instance === null ) {
			$instance = new AnsPress_Upgrader();
		}

		return $instance;
	}

	/**
	 * Private ctor so nobody else can instance it
	 */
	private function __construct() {
		$this->check_tables();
		// Enable required addons.
		ap_activate_addon( 'tag.php' );
		ap_activate_addon( 'category.php' );
		ap_activate_addon( 'reputation.php' );

		// Disable sending email while upgrading.
		define( 'AP_DISABLE_EMAIL', true );

		// Also disable inserting of reputations and notifications.
		define( 'AP_DISABLE_INSERT_NOTI', true );

		$this->check_old_meta_table_exists();
		$this->get_question_ids();

		foreach ( (array) $this->question_ids as $id ) {
			// Translators: Question ID in placeholder.
			print( "\n\r" . sprintf( __( 'Migrating question: %d', 'anspress-question-answer' ), $id ) . "\n\r" );
			$this->question_tasks( $id );
		}

		$this->migrate_reputations();
		$this->migrate_category_data();
	}

	/**
	 * Check if tables are updated, if not create it first.
	 */
	public function check_tables() {
		if ( get_option( 'anspress_db_version' ) !== AP_DB_VERSION ) {
			$activate = AP_Activate::get_instance();
			$activate->insert_tables();
			update_option( 'anspress_db_version', AP_DB_VERSION );
		}
	}

	/**
	 * Check if old ap_meta table exists.
	 */
	public function check_old_meta_table_exists() {
		global $wpdb;

		if ( $wpdb->prefix . 'ap_meta' === $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}ap_meta'" ) ) {
			$this->meta_table_exists = true;
		}
	}

	/**
	 * Get all question ids.
	 *
	 * @return void
	 */
	public function get_question_ids() {
		global $wpdb;

		$this->question_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->ap_qameta} q ON q.post_id = p.ID WHERE q.post_id IS NULL AND post_type = 'question' ORDER BY ID ASC" );
	}

	/**
	 * Process question tasks.
	 *
	 * @param integer $id Question ID.
	 * @return void
	 */
	private function question_tasks( $id ) {
		global $wpdb;

		$question = get_post( $id );

		$last_active = get_post_meta( $id, '_ap_updated', true );
		$views       = get_post_meta( $id, '_views', true );

		// Get all answers associated with current question.
		$this->answer_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} p WHERE post_type = 'answer' AND post_parent = %d ORDER BY post_date ASC", $id ) );

		foreach ( (array) $this->answer_ids as $answer_id ) {
			$this->answer_tasks( $answer_id );
		}

		$answers_counts     = ap_count_published_answers( $id );
		$answer_id          = (int) get_post_meta( $id, '_ap_selected', true );
		$featured_questions = (array) get_option( 'featured_questions' );

		ap_insert_qameta(
			$id, array(
				'answers'      => $answers_counts,
				'views'        => (int) get_post_meta( $id, '_views', true ),
				'subscribers'  => (int) get_post_meta( $id, '_ap_subscriber', true ),
				'closed'       => ( 'closed' === $question->post_status ? 1 : 0 ),
				'flags'        => (int) get_post_meta( $id, '_ap_flag', true ),
				'selected_id'  => $answer_id,
				'featured'     => in_array( $id, $featured_questions ),
				'last_updated' => empty( $last_active ) ? $question->post_date : $last_active,
			)
		);

		ap_update_qameta_terms( $id );
		ap_update_post_attach_ids( $id );

		$this->migrate_votes( $id );
		$this->restore_last_activity( $id );

		delete_post_meta( $id, '_ap_answers' );
		delete_post_meta( $id, '_ap_participants' );
		delete_post_meta( $id, '_views' );
		delete_post_meta( $id, '_ap_subscriber' );
		delete_post_meta( $id, '_ap_selected' );
		delete_post_meta( $id, '_ap_vote' );
		delete_post_meta( $id, '_ap_flag' );
		delete_post_meta( $id, '_ap_selected' );

		$this->delete_question_metatables( $id );
	}

	/**
	 * Migrate votes to new qameta table.
	 *
	 * @param integer $post_id Post ID.
	 * @return void
	 */
	public function migrate_votes( $post_id ) {
		global $wpdb;

		if ( ! $this->meta_table_exists ) {
			return;
		}

		$post_id   = (int) $post_id;
		$old_votes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type IN ('vote_up', 'vote_down') AND apmeta_actionid = {$post_id}" ); // @codingStandardsIgnoreLine

		$apmeta_to_delete = [];
		foreach ( (array) $old_votes as $vote ) {
			ap_add_post_vote( $post_id, $vote->apmeta_userid, 'vote_up' === $vote->apmeta_type, $vote->apmeta_value );
			$apmeta_to_delete[] = $vote->apmeta_id;
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );

		if ( ! empty( $apmeta_to_delete ) ) {
			$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // @codingStandardsIgnoreLine
		}
	}

	/**
	 * Delete old meta.
	 *
	 * @param integer $id Question ID.
	 */
	public function delete_question_metatables( $id ) {
		global $wpdb;

		if ( ! $this->meta_table_exists ) {
			return;
		}

		$old_views = $wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'post_view' AND apmeta_actionid = {$id}" ); // DB call okay, Db cache okay.

	}

	/**
	 * Process answers tasks.
	 *
	 * @param integer $answer_id Answer ID.
	 * @return void
	 */
	private function answer_tasks( $answer_id ) {
		$answer      = get_post( $answer_id );
		$last_active = get_post_meta( $answer_id, '_ap_updated', true );
		$best_answer = get_post_meta( $answer_id, '_ap_best_answer', true );
		$flags       = (int) get_post_meta( $answer_id, '_ap_flag', true );

		$args = array(
			'flags'        => $flags,
			'last_updated' => empty( $last_active ) ? $answer->post_date : $last_active,
		);

		if ( '1' === $best_answer ) {
			$args['selected'] = 1;
		}

		ap_insert_qameta( $answer_id, $args );
		$this->migrate_votes( $answer_id );

		delete_post_meta( $answer_id, '_ap_updated' );
		delete_post_meta( $answer_id, '_ap_best_answer' );
		delete_post_meta( $answer_id, '_ap_subscriber' );
		delete_post_meta( $answer_id, '_ap_participants' );
		delete_post_meta( $answer_id, '_ap_close' );
		delete_post_meta( $answer_id, '_ap_vote' );
		delete_post_meta( $answer_id, '_ap_flag' );
		delete_post_meta( $answer_id, '_ap_selected' );

		$this->restore_last_activity( $answer_id );
	}

	/**
	 * restore last activity of a post.
	 *
	 * @param integer $post_id Post ID.
	 * @return void
	 */
	public function restore_last_activity( $post_id ) {
		$activity = get_post_meta( $post_id, '__ap_activity', true );

		// Restore last activity.
		if ( ! empty( $activity ) ) {
			ap_insert_qameta( $post_id, [ 'activities' => $activity ] );
		}

		delete_post_meta( $post_id, '__ap_activity' );
	}

	/**
	 * Migrate migration data to new table.
	 */
	public function migrate_reputations() {
		if ( ! $this->meta_table_exists ) {
			print( __( 'Successfully migrated all reputations', 'anspress-question-answer' ) );
			return;
		}

		global $wpdb;
		$old_reputations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ap_meta WHERE apmeta_type = 'reputation'" ); // DB call okay, Db cache okay.

		if ( empty( $old_reputations ) ) {
			print( __( 'Successfully migrated all reputations', 'anspress-question-answer' ) );
			return;
		}

		$apmeta_to_delete = [];

		foreach ( (array) $old_reputations as $rep ) {
			$event = $this->replace_old_reputation_event( $rep->apmeta_param );
			ap_insert_reputation( $event, $rep->apmeta_actionid, $rep->apmeta_userid );
			$apmeta_to_delete[] = $rep->apmeta_id;

			// Delete user meta.
			delete_user_meta( $rep->apmeta_userid, 'ap_reputation' ); // @codingStandardsIgnoreLine.
		}

		// Delete all migrated data.
		$apmeta_to_delete = sanitize_comma_delimited( $apmeta_to_delete, 'int' );
		$wpdb->query( "DELETE FROM {$wpdb->prefix}ap_meta WHERE apmeta_id IN ({$apmeta_to_delete})" ); // DB call okay, Db cache okay.
		print( esc_attr__( 'Migrated all reputations', 'anspress-question-answer' ) );
	}

	/**
	 * Return new reputation event alternative.
	 *
	 * @param  string $old_event Old event.
	 * @return string
	 */
	public function replace_old_reputation_event( $old_event ) {
		$events = array(
			'ask'                => [ 'new_question', 'question' ],
			'answer'             => [ 'new_answer', 'answer' ],
			'received_vote_up'   => [ 'vote_up', 'question_upvote', 'answer_upvote' ],
			'received_vote_down' => [ 'vote_down', 'question_downvote', 'answer_downvote' ],
			'given_vote_up'      => [ 'voted_up', 'question_upvoted', 'answer_upvoted' ],
			'given_vote_down'    => [ 'voted_down', 'question_downvoted', 'answer_downvoted' ],
			'selecting_answer'   => 'select_answer',
			'select_answer'      => 'best_answer',
			'comment'            => 'new_comment',
		);

		$found = false;

		foreach ( $events as $new_event => $olds ) {
			if ( is_array( $olds ) && in_array( $old_event, $olds, true ) ) {
				$found = $new_event;
				break;
			} elseif ( $old_event === $olds ) {
				$found = $new_event;
				break;
			}
		}

		if ( false !== $found ) {
			return $found;
		}

		return $old_event;
	}

	/**
	 * Migrate old category options from option table to term meta table.
	 */
	public function migrate_category_data() {
		global $wpdb;

		$terms = $wpdb->get_results( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('question_category') ORDER BY t.name ASC" ); // @codingStandardsIgnoreLine.

		foreach ( (array) $terms as $term ) {
			$term_meta = get_option( 'ap_cat_' . $term->term_id );

			if ( isset( $term_meta['ap_image'] ) ) {
				$term_meta['image'] = $term_meta['ap_image'];
				unset( $term_meta['ap_image'] );
			}

			if ( isset( $term_meta['ap_icon'] ) ) {
				$term_meta['icon'] = $term_meta['ap_icon'];
				unset( $term_meta['ap_icon'] );
			}

			if ( isset( $term_meta['ap_color'] ) ) {
				$term_meta['color'] = $term_meta['ap_color'];
				unset( $term_meta['ap_color'] );
			}

			update_term_meta( $term->term_id, 'ap_category', $term_meta );
			delete_option( 'ap_cat_' . $term->term_id );
		}

		print( esc_attr__( 'Categories data migrated', 'anspress-question-answer' ) );
	}
}
