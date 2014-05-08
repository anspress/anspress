<?php
	$post_id = get_edit_question_id();
	
	if( !ap_user_can_edit_question($post_id)){
		echo '<p>'.__('You don\'t have permission to access this page.', 'ap').'</p>';
	}

	$action = get_post_type($post_id).'-'.$post_id;	

	if(!isset($_REQUEST['ap_nonce']) || !wp_verify_nonce($_REQUEST['ap_nonce'], $action)){
		echo '<p>'.__('Trying to cheat? huh!.', 'ap').'</p>';
	}
	
	global $current_user;
	$post = get_post($post_id);
	
	$validate = ap_validate_form();
	if(isset($validate['has_error']) && $validate['has_error']){
		echo '<div class="alert alert-danger">'. implode(', ', $validate['message']) .'</div>';
	}	
	
	$cats_t = get_the_terms( $post_id, 'question_category' );

	if(isset($cats_t) && is_array($cats_t)){
		foreach($cats_t as $c)
			$category = $c->term_id;
	}	
	$tags_t = get_the_terms( $post_id, 'question_tags' );
	$tags ='';
	
	if($tags_t){
		foreach($tags_t as $t){
			$tags .= $t->name.', ';
		}
	}

?>
<div id="edit-question-page" class="ap-container clearfix">
	<form action="" id="ask_question_form" method="POST">
		<?php do_action('ap_ask_form_top'); ?>
		<div class="form-group">
			<label for="post_title"><?php _e('Title', 'ap') ?></label>				
			<input type="text" name="post_title" id="post_title" value="<?php echo $post->post_title; ?>" class="form-control" placeholder="<?php _e('Question in one sentence', 'ap'); ?>" />
		</div>
		<div class="form-group">						
			<label for="post_content"><?php _e('Content', 'ap') ?></label>
			<?php 
				ap_editor_content($post->post_content)
			?>
		</div>

		<div class="form-group">
			<label for="category"><?php _e('Category', 'ap') ?></label>
			<select class="form-control" name="category" id="industries">
				<?php 
				$taxonomies = get_terms( 'question_category', 'orderby=count&hide_empty=0' );
				echo '<option value=""> -- </option>';
				foreach($taxonomies as $cat)
					echo '<option value="'.$cat->term_id.'"'.(( $category == $cat->term_id ) ? ' selected="selected"' : '').'>'.$cat->name.'</option>';
				?>
			</select>
		</div>
		<div class="form-group">
			<label for="tags"><?php _e('Tags', 'ap') ?></label>
			<input type="text" value="<?php echo $tags; ?>" tabindex="5" name="tags" id="tags" class="form-control" />
		</div>
		<?php do_action('ap_edit_question_form_bottom'); ?>
		<?php ap_edit_question_form_hidden_input($post_id); ?>
		
	</form>
</div>
