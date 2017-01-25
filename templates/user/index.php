<?php
/**
	* Display question list
	*
	* This template is used in base page, category, tag , etc
	*
	* @link https://anspress.io
	* @since unknown
	*
	* @package AnsPress
	*/

	$user_id = get_query_var( 'ap_user_id' );
	$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="ap-row">
	<div id="ap-author" class="ap-author <?php echo is_active_sidebar( 'ap-author' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12' ?>">
		<div class="ap-author-bio">
			<div class="ap-author-avatar ap-pull-left">
				<?php echo get_avatar( $user_id, 80 ); ?>
			</div>
			<div class="no-overflow">
				<div class="ap-author-name">
					<?php echo ap_user_display_name( [ 'user_id' => $user_id, 'html' => true ] ); ?>
				</div>
				<div class="ap-author-about">
					<?php echo get_user_meta( $user_id, 'description', true ); ?>
				</div>
			</div>
		</div>

		<ul class="ap-tab-nav clearfix">
			<li<?php echo 'questions' === $current_tab ? ' class="active"' : ''; ?>><a href="<?php echo ap_user_link( $user_id ); ?>"><?php esc_attr_e( 'Questions', 'anspress-question-answer' ); ?></a></li>
			<?php do_action( 'ap_user_tab' ); ?>
		</ul>

		<?php if ( 'questions' === $current_tab ) : ?>

			<?php if ( ap_have_questions() ) : ?>
				<div class="ap-questions">
					<?php
						/* Start the Loop */
						while ( ap_have_questions() ) : ap_the_question();
							ap_get_template_part( 'question-list-item' );
						endwhile;
					?>
				</div>

				<?php ap_questions_the_pagination(); ?>

			<?php
				else :
					ap_get_template_part( 'content-none' );
				endif;
			?>

		<?php endif; ?>

		<?php do_action( 'ap_user_content' ); ?>

	</div>
	<?php if ( is_active_sidebar( 'ap-author' ) && is_anspress()){ ?>
		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-author' ); ?>
		</div>
	<?php } ?>
</div>


