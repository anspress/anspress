<?php
/**
 * AnsPress product license
 * Handle licence of AnsPress products.
 *
 * @link https://anspress.net
 * @since 2.4.5
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 */


// Load updater.
require_once dirname( __FILE__ ) . '/updater.php';


/**
 * AnsPress license
 *
 * @ignore
 */
class AP_License {

	/**
	 * Initialize class.
	 */
	public function __construct() {
		add_action( 'ap_admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'ap_plugin_updater' ), 0 );
	}

	/**
	 * Show license menu if license field is registered.
	 */
	public function menu() {
		$fields = ap_product_license_fields();
		if ( ! empty( $fields ) ) {
			$count = ' <span class="update-plugins count anspress-license-count"><span class="plugin-count">' . number_format_i18n( count( $fields ) ) . '</span></span>';
			add_submenu_page( 'anspress', __( 'Licenses', 'anspress-question-answer' ), __( 'Licenses', 'anspress-question-answer' ) . $count, 'manage_options', 'anspress_licenses', array( $this, 'display_plugin_licenses' ) );
		}
	}

	/**
	 * Display license page.
	 */
	public function display_plugin_licenses() {
		include_once 'views/licenses.php';
	}

	/**
	 * AnsPress license form.
	 */
	public static function ap_product_license() {
		if ( ! current_user_can( 'manage_options' ) || ! ap_verify_nonce( 'ap_licenses_nonce' ) ) {
			return;
		}

		$licenses = get_option( 'anspress_license', array() );
		$fields   = ap_product_license_fields();

		if ( empty( $fields ) ) {
			return;
		}

		if ( ap_isset_post_value( 'save_licenses' ) ) {
			foreach ( (array) $fields as $slug => $prod ) {
				$prod_license = ap_isset_post_value( 'ap_license_' . $slug, '' );
				if ( ! empty( $prod_license ) && ! isset( $licenses[ $slug ] ) || $prod_license !== $licenses[ $slug ]['key'] ) {
					$licenses[ $slug ] = array(
						'key'    => trim( ap_sanitize_unslash( 'ap_license_' . $slug, 'g', '' ) ),
						'status' => false,
					);

					update_option( 'anspress_license', $licenses );
				}
			}
		}

		foreach ( (array) $fields as $slug => $prod ) {

			// Data to send in our API request.
			$api_params = array(
				'license'      => $licenses[ $slug ]['key'],
				'item_name'    => rawurlencode( $prod['name'] ),
				'url'          => home_url(),
				'anspress_ver' => AP_VERSION,
			);

			// Check if activate is clicked.
			if ( ap_isset_post_value( 'ap_license_activate_' . $slug ) ) {
				$api_params['edd_action'] = 'activate_license';

				// Call the custom API.
				$response = wp_remote_post(
					'https://anspress.net', array(
						'timeout'   => 15,
						'sslverify' => true,
						'body'      => $api_params,
					)
				);

				// Make sure the response came back okay.
				if ( ! is_wp_error( $response ) ) {
					// Decode the license data.
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					$licenses[ $slug ]['status'] = sanitize_text_field( $license_data->license );
					update_option( 'anspress_license', $licenses );
				}
			}

			// Check if deactivate is clicked.
			if ( ap_isset_post_value( 'ap_license_deactivate_' . $slug ) ) {
				$api_params['edd_action'] = 'deactivate_license';

				// Call the custom API.
				$response = wp_remote_post(
					'https://anspress.net', array(
						'timeout'   => 15,
						'sslverify' => true,
						'body'      => $api_params,
					)
				);

				// Make sure the response came back okay.
				if ( ! is_wp_error( $response ) ) {
					// Decode the license data.
					$license_data                = json_decode( wp_remote_retrieve_body( $response ) );
					$licenses[ $slug ]['status'] = sanitize_text_field( $license_data->license );
					update_option( 'anspress_license', $licenses );
				}
			}
		}
	}

	/**
	 * Initiate product updater.
	 */
	public function ap_plugin_updater() {
		$fields   = ap_product_license_fields();
		$licenses = get_option( 'anspress_license', array() );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $slug => $prod ) {
				if ( isset( $licenses[ $slug ] ) && ! empty( $licenses[ $slug ]['key'] ) ) {
					new AnsPress_Prod_Updater(
						$prod['file'], array(
							'version'   => ! empty( $prod['version'] ) ? $prod['version'] : '',
							'license'   => $licenses[ $slug ]['key'],
							'item_name' => ! empty( $prod['name'] ) ? $prod['name'] : '',
							'author'    => ! empty( $prod['author'] ) ? $prod['author'] : '',
							'slug'      => $slug,
						),
						isset( $prod['is_plugin'] ) ? $prod['is_plugin'] : true
					);
				}
			}
		}
	}

}

/**
 * AnsPress product licenses.
 *
 * @return array
 * @since 2.4.5
 */
function ap_product_license_fields() {
	return apply_filters( 'anspress_license_fields', array() );
}

