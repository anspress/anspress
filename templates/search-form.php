<?php
/**
 * Template for search form.
 * Different from WP default searchfrom.php. This only search for question and answer.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 *
 * @since   3.0.0
 * @since   4.1.0 Changed action link to home. Added post_type hidden field.
 */

?>

<form id="ap-search-form" class="ap-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<button class="ap-btn ap-search-btn" type="submit"><?php esc_attr_e( 'Search', 'anspress-question-answer' ); ?></button>
	<div class="ap-search-inner no-overflow">
	  <input name="s" type="text" class="ap-search-input ap-form-input" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php the_search_query(); ?>" />
		<input type="hidden" name="post_type" value="question" />
  </div>
</form>
