<?php

class AnsPress_Options_Page
{
	static public function add_option_groups()
	{
		$settings = ap_opt();

		// Register general settings
		ap_register_option_group( 'general', __('General', 'ap'), array(
			array(
				'name' 				=> 'anspress_opt[questions_page]',
				'label' 			=> __('Questions page', 'ap'),
				'description' 		=> __('Select page for displaying questions list.', 'ap'),
				'type' 				=> 'page_select',
				'value' 			=> @$settings['questions_page_id'],
				
			),
			array(
				'name' 				=> 'anspress_opt[ask_page]',
				'label' 			=> __('Ask page', 'ap'),
				'description' 		=> __('Select page for displaying ask form.', 'ap'),
				'type' 				=> 'page_select',
				'value' 			=> @$settings['ask_page_id'],
			),
			array(
				'name' 				=> 'anspress_opt[user_page]',
				'label' 			=> __('User page', 'ap'),
				'description' 		=> __('Select page for displaying user profile.', 'ap'),
				'type' 				=> 'page_select',
				'value' 			=> @$settings['user_page_id'],
			),
			array(
				'name' 				=> 'anspress_opt[edit_page]',
				'label' 			=> __('Edit page', 'ap'),
				'description' 		=> __('Select page for displaying edit form.', 'ap'),
				'type' 				=> 'page_select',
				'value' 			=> @$settings['edit_page_id'],
			),
			array(
				'name' 				=> 'anspress_opt[author_credits]',
				'label' 			=> __('Hide author credits', 'ap'),
				'description' 		=> __('Show your love by showing link to AnsPress project site.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> @$settings['author_credits'],
				'order' 			=> '1',
			),

			array(
				'name' 				=> 'anspress_opt[allow_private_posts]',
				'label' 			=> __('Allow private posts', 'ap'),
				'description' 		=> __('Allow users to create private question and answer.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> @$settings['allow_private_posts'],
			),
		));
	
		//Register layout settings
		ap_register_option_group( 'layout', __('Layout', 'ap'), array(
			array(
				'name' 				=> '__sep',
				'type' 				=> 'custom',
				'html' 				=> '<span class="ap-form-separator">'.__('Avatar').'</span>',
			),
			array(
				'name' 				=> 'anspress_opt[theme]',
				'label' 			=> __('Theme', 'ap'),
				'description' 		=> __('Select theme for AnsPress.', 'ap'),
				'type' 				=> 'select',
				'options' 			=> ap_theme_list(),
				'value' 			=> $settings['theme'],
			),
			array(
				'name' 				=> 'anspress_opt[avatar_size_list]',
				'label' 			=> __('List avatar size', 'ap'),
				'description' 		=> __('User avatar size for questions list.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['avatar_size_list'],
			),
			array(
				'name' 				=> 'anspress_opt[avatar_size_qquestion]',
				'label' 			=> __('Question avatar size', 'ap'),
				'description' 		=> __('User avatar size for question.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['avatar_size_qquestion'],
			),
			array(
				'name' 				=> 'anspress_opt[avatar_size_qanswer]',
				'label' 			=> __('Answer avatar size', 'ap'),
				'description' 		=> __('User avatar size for answer.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['avatar_size_qanswer'],
			),
			array(
				'name' 				=> 'anspress_opt[avatar_size_qanswer]',
				'label' 			=> __('Answer avatar size', 'ap'),
				'description' 		=> __('User avatar size for answer.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['avatar_size_qanswer'],
			),
			array(
				'name' 				=> 'anspress_opt[avatar_size_qcomment]',
				'label' 			=> __('Comment avatar size', 'ap'),
				'description' 		=> __('User avatar size for comments.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['avatar_size_qcomment'],
			),
			array(
				'name' 				=> '__sep',
				'type' 				=> 'custom',
				'html' 				=> '<span class="ap-form-separator">'.__('Items to show per page').'</span>',
			),
			array(
				'name' 				=> 'anspress_opt[question_per_page]',
				'label' 			=> __('Questions per page', 'ap'),
				'description' 		=> __('Questions to show per page.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['question_per_page'],
			),
			array(
				'name' 				=> 'anspress_opt[answers_per_page]',
				'label' 			=> __('Answers per page', 'ap'),
				'description' 		=> __('Answers to show per page.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['answers_per_page'],
			),
			array(
				'name' 				=> 'anspress_opt[answers_per_page]',
				'label' 			=> __('Answers per page', 'ap'),
				'description' 		=> __('Answers to show per page.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['answers_per_page'],
			),
			array(
				'name' 				=> '__sep',
				'type' 				=> 'custom',
				'html' 				=> '<span class="ap-form-separator">'.__('Sorting').'</span>',
			),

			array(
				'name' 				=> 'anspress_opt[answers_sort]',
				'label' 			=> __('Default answer sort', 'ap'),
				'description' 		=> __('Sort answers by default.', 'ap'),
				'type' 				=> 'select',
				'options' 			=> array(
						'voted' 	=> __('Voted', 'ap'),
						'active' 	=> __('Active', 'ap'),
						'newest' 	=> __('Newest', 'ap'),
						'oldest' 	=> __('Oldest', 'ap'),
					),
				'value' 			=> $settings['answers_sort'],
			),
			array(
				'name' 				=> '__sep',
				'type' 				=> 'custom',
				'html' 				=> '<span class="ap-form-separator">'.__('Toggle').'</span>',
			),
			array(
				'name' 				=> 'anspress_opt[show_comments_by_default]',
				'label' 			=> __('Show comments', 'ap'),
				'description' 		=> __('Show comments by default.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['show_comments_by_default'],
			),

			/* TODO: Add question sorting*/

		));
		
		// Register question settings
		ap_register_option_group( 'question', __('Question', 'ap'), array(
			array(
				'name' 				=> 'anspress_opt[minimum_qtitle_length]',
				'label' 			=> __('Minimum title length', 'ap'),
				'description' 		=> __('Set minimum letters for a question title.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['minimum_qtitle_length'],
			),
			array(
				'name' 				=> 'anspress_opt[minimum_question_length]',
				'label' 			=> __('Minimum question content', 'ap'),
				'description' 		=> __('Set minimum letters for a question contents.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['minimum_question_length'],
			),
			array(
				'name' 				=> 'anspress_opt[question_text_editor]',
				'label' 			=> __('Use text editor ?', 'ap'),
				'description' 		=> __('Text editor as default.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['question_text_editor'],
			),
		));

		// Register answer settings
		ap_register_option_group( 'answer', __('Answer', 'ap'), array(
			array(
				'name' 				=> 'anspress_opt[multiple_answers]',
				'label' 			=> __('Multiple Answers', 'ap'),
				'description' 		=> __('Allow an user to submit multiple answers on a single question.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['multiple_answers'],
			),
			array(
				'name' 				=> 'anspress_opt[minimum_ans_length]',
				'label' 			=> __('Minimum question content', 'ap'),
				'description' 		=> __('Set minimum letters for a answer contents.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['minimum_ans_length'],
			),
			array(
				'name' 				=> 'anspress_opt[answer_text_editor]',
				'label' 			=> __('Use text editor ?', 'ap'),
				'description' 		=> __('Text editor as default.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['answer_text_editor'],
			),
		));

		// register user settings
		ap_register_option_group( 'user', __('User', 'ap'), array(
			array(
				'name' 				=> 'anspress_opt[cover_width]',
				'label' 			=> __('Cover width', 'ap'),
				'description' 		=> __('Set width of user cover photo.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['cover_width'],
			),
			array(
				'name' 				=> 'anspress_opt[cover_height]',
				'label' 			=> __('Cover height', 'ap'),
				'description' 		=> __('Set height of user cover photo.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['cover_height'],
			),
			array(
				'name' 				=> 'anspress_opt[cover_width_small]',
				'label' 			=> __('Cover thumb width', 'ap'),
				'description' 		=> __('Set width of user cover photo thumbnail.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['cover_width_small'],
			),
			array(
				'name' 				=> 'anspress_opt[cover_height_small]',
				'label' 			=> __('Small cover height', 'ap'),
				'description' 		=> __('Set height of user cover photo thumbnail.', 'ap'),
				'type' 				=> 'number',
				'value' 			=> $settings['cover_height_small'],
			),
			array(
				'name' 				=> 'anspress_opt[default_rank]',
				'label' 			=> __('Default rank', 'ap'),
				'description' 		=> __('Assign a default rank for newly registered user.', 'ap'),
				'type' 				=> 'select',
				'options' 			=> get_terms( 'rank', array( 'hide_empty' => false, 'orderby' => 'id' ) ),
				'value' 			=> $settings['default_rank'],
			),
		));

		// register permission settings
		ap_register_option_group( 'permission', __('Permission', 'ap'), array(
			array(
				'name' 				=> 'anspress_opt[allow_anonymous]',
				'label' 			=> __('Allow anonymous', 'ap'),
				'description' 		=> __('Non-loggedin user can post questions and answers.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['allow_anonymous'],
			),
			array(
				'name' 				=> 'anspress_opt[only_admin_can_answer]',
				'label' 			=> __('Only admin can answer', 'ap'),
				'description' 		=> __('Only allow admin to answer question.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['only_admin_can_answer'],
			),
			array(
				'name' 				=> 'anspress_opt[logged_in_can_see_ans]',
				'label' 			=> __('Only logged in can see answers', 'ap'),
				'description' 		=> __('non-loggedin user cannot see answers.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['logged_in_can_see_ans'],
			),
			array(
				'name' 				=> 'anspress_opt[logged_in_can_see_comment]',
				'label' 			=> __('Only logged in can see comment', 'ap'),
				'description' 		=> __('non-loggedin user cannot see comments.', 'ap'),
				'type' 				=> 'checkbox',
				'value' 			=> $settings['logged_in_can_see_comment'],
			),
		));

		// register moderate settings
		ap_register_option_group( 'moderate', __('Moderate', 'ap'), array(
			array(
				'name' 				=> '__sep',
				'type' 				=> 'custom',
				'html' 				=> '<span class="ap-form-separator">'.__('Flag').'</span>',
			),
			/*array(
				'name' 				=> 'anspress_opt[flag_note]',
				'label' 			=> __('Flag notes', 'ap'),
				'description' 		=> __('Default notes when flagging a post', 'ap'),
				'type' 				=> 'text',
				'value' 			=> $settings['flag_note'],
				'repeatable' 		=> true,
			),*/
			
		));
	}
}
