<?php
/**
 * Template for search form.
 * Different from WP default searchfrom.php. This only search for question and answer.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 * @since   3.0.0
 */
?>
<form id="ap-search-form" class="ap-search-form" action="<?php echo esc_url( ap_get_link_to( '/' ) ); ?>">
	<button class="ap-btn ap-search-btn" type="submit"><?php esc_attr_e( 'Search', 'anspress-question-answer' ); ?></button>
	<div class="ap-search-inner no-overflow">
	    <input name="ap_s" type="text" class="ap-search-input ap-form-input" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php echo sanitize_text_field( get_query_var( 'ap_s' ) ); ?>" />
    </div>
</form>
