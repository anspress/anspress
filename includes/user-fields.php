<?php

function ap_user_fields($args = '', $group = false) {
	if ( ap_get_displayed_user_id() != get_current_user_id() ) {
		return; }

	if ( ! $group ) {
		$group = ! isset( $_GET['group'] ) ? 'basic' : sanitize_text_field( $_GET['group'] ); }

	echo ap_user_get_fields( $args, $group );
}

/**
 * Return fields of users
 * @param  string|array $args
 * @return object
 */
function ap_user_get_fields($args = '', $group = 'basic') {

	$defaults = array(
		'user_id' => get_current_user_id(),
		'form'  => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$args['form'] = wp_parse_args($args['form'], array(
		'is_ajaxified'      => true,
		'name'              => 'ap_user_profile_form',
		'user_id'           => $args['user_id'],
		'nonce_name'        => 'nonce_user_profile_'.$args['user_id'].'_'.$group,
		'fields'            => ap_get_user_fields( $group ),
	));

	$args['form']['fields'][] = array(
		'name'          => 'group',
		'type'          => 'hidden',
		'value'         => $group,
	);

	anspress()->form = new AnsPress_Form( $args['form'] );

	return anspress()->form->get_form();
}

function ap_get_user_fields($group = 'basic', $user_id = false) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id(); }

	$fields_value = ap_user_get_the_meta( false, $user_id );

	$form_fields = array();

	$form_fields['basic'] = array(
	array(
		'name' => 'hide_profile',
		'label' => __( 'Hide my profile', 'anspress-question-answer' ),
		'type'  => 'checkbox',
		'desc'  => __( 'Hide your profile from public', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['hide_profile'] ) ? $fields_value['hide_profile'] : '' ),
		'order' => 5,
		'autocomplete' => false,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
		'show_desc_tip' => false,
	),
	array(
		'name' => 'first_name',
		'label' => __( 'First name', 'anspress-question-answer' ),
		'type'  => 'text',
		'placeholder'  => __( 'Your first name', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['first_name'] ) ? $fields_value['first_name'] : ''),
		'order' => 5,
		'autocomplete' => false,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	array(
		'name' => 'last_name',
		'label' => __( 'Last name', 'anspress-question-answer' ),
		'type'  => 'text',
		'placeholder'  => __( 'Your surname', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['last_name'] ) ? $fields_value['last_name'] : '' ),
		'order' => 5,
		'autocomplete' => false,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	array(
		'name' => 'nickname',
		'label' => __( 'Nickname', 'anspress-question-answer' ),
		'type'  => 'text',
		'placeholder'  => __( 'Your nickname', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['nickname'] ) ? $fields_value['nickname'] : '' ),
		'order' => 5,
		'autocomplete' => false,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	array(
		'name' => 'display_name',
		'label' => __( 'Display name', 'anspress-question-answer' ),
		'type'  => 'select',
		'options'  => ap_user_get_display_name_option( $user_id ),
		'value' => ( !empty( $fields_value['display_name'] ) ? $fields_value['display_name'] : '' ),
		'order' => 5,
		'autocomplete' => false,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	array(
		'name' => 'description',
		'label' => __( 'Description', 'anspress-question-answer' ),
		'type'  => 'textarea',
		'value' => ( !empty( $fields_value['description'] ) ? $fields_value['description'] : '' ),
		'placeholder'  => __( 'Write something about yourself', 'anspress-question-answer' ),
		'rows' => 5,
		'order' => 5,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	array(
		'name' => 'signature',
		'label' => __( 'Signature', 'anspress-question-answer' ),
		'type'  => 'textarea',
		'value' => ( !empty( $fields_value['signature'] ) ? $fields_value['signature'] : '' ),
		'placeholder'  => __( 'A short signature for showing in hover card', 'anspress-question-answer' ),
		'rows' => 5,
		'order' => 5,
		'sanitize' => array( 'strip_tags', 'sanitize_text_field' ),
	),
	);

	$form_fields['account'] = array(
	array(
		'name' => 'user_login',
		'label' => __( 'Username', 'anspress-question-answer' ),
		'type'  => 'text',
		'placeholder'  => __( 'Your username', 'anspress-question-answer' ),
		'desc'  => __( 'This cannot be changed.', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['user_login'] ) ? $fields_value['user_login'] : '' ),
		'order' => 5,
		'attr' => 'disabled="disabled"',
		'autocomplete' => false,
		'sanitize' => array( 'sanitize_text_field' ),
		'visibility' => 'me',
	),
	array(
		'name' => 'user_email',
		'label' => __( 'Email', 'anspress-question-answer' ),
		'type'  => 'text',
		'placeholder'  => __( 'Your contact email', 'anspress-question-answer' ),
		'desc'  => __( 'NOTICE: If you update email then you need to re-verify your email and account.', 'anspress-question-answer' ),
		'value' => ( !empty( $fields_value['user_email'] ) ? $fields_value['user_email'] : '' ),
		'order' => 5,
		'autocomplete' => false,
		'edit_disabled' => true,
		'sanitize' => array( 'is_email' ),
		'validate' => array( 'is_email' ),
		'visibility' => 'me',
		'show_desc_tip' => false,
	),
	array(
		'name' => 'password',
		'label' => __( 'Password', 'anspress-question-answer' ),
		'type'  => 'password',
		'placeholder'  => __( 'Update your password', 'anspress-question-answer' ),
		'value' => '',
		'visibility' => 'me',
		'order' => 5,
		'autocomplete' => false,
	),
	);

	$form_fields = apply_filters( 'ap_user_fields', $form_fields );

	if ( isset( $form_fields[ $group ] ) ) {
		return $form_fields[ $group ];
	}

	return false;
}
