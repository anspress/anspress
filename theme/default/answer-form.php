<?php if ( ap_user_can_answer(get_question_id() ) ) : ?>
    <div id="answer-form-c" class="ap-minimal-editor">
        <div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(get_current_user_id() ); ?>"<?php ap_hover_card_attributes(get_current_user_id() ); ?>>
				<?php echo get_avatar(get_current_user_id(), ap_opt('avatar_size_qquestion' ) ); ?>
            </a>
        </div>
        <div class="ap-a-cells ap-form-c clearfix">
            <div class="ap-minimal-placeholder"  data-action="ajax_btn" data-query="load_tinymce_assets::<?php echo wp_create_nonce( 'ap_ajax_nonce' ); ?>::answer" data-cb="apAppendEditor" data-loadclass="ripple-loading">
				<?php _e('Write your answer..', 'anspress-question-answer' ); ?>
            </div>
            <div class="ap-form-head">
                <ul class="ap-form-head-tab ap-ul-inline clearfix ap-tab-nav">
					<li class="active"><a href="#ap-form-main"><?php _e('Write', 'anspress-question-answer' ); ?></a></li>
					<?php if ( ap_opt('answer_help_page' ) != '' ) : ?>
						<li><a href="#ap-form-help"><?php _e('How to answer', 'anspress-question-answer' ); ?></a></li>
					<?php endif; ?>
                </ul>
            </div>
            <div class="ap-tab-container">
                <div id="ap-form-main" class="active ap-tab-item">
					<?php ap_answer_form(get_question_id() ); ?>
                </div>
                <div id="ap-form-help" class="ap-tab-item">
					<?php if ( ap_opt('answer_help_page' ) != '' ) : ?>
						<?php ap_how_to_answer(); ?>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php ap_get_template_part('login-signup' ); ?>
