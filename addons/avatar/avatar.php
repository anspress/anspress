<?php
/**
 * Dynamic addon avatar.
 *
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage Dynamic Avatar Addon
 *
 * @anspress-addon
 * Addon Name:    Dynamic Avatar
 * Addon URI:     https://anspress.net
 * Description:   Generate user avatar dynamically.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.net
 */

namespace Anspress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'class-generator.php';

/**
 * AnsPress avatar hook class.
 *
 * @since 4.1.8
 */
class Avatar extends \AnsPress\Singleton {
	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 * @since 4.1.8
	 */
	public static $instance = null;

	/**
	 * Initialize the class.
	 */
	protected function __construct() {
		ap_add_default_options(
			[
				'avatar_font'  => 'Pacifico',
				'avatar_force' => false,
			]
		);

		anspress()->add_action( 'ap_form_addon-avatar', __CLASS__, 'option_form' );
		anspress()->add_filter( 'pre_get_avatar_data', __CLASS__, 'get_avatar', 1000, 3 );
		anspress()->add_action( 'wp_ajax_ap_clear_avatar_cache', __CLASS__, 'clear_avatar_cache' );
	}

	/**
	 * Register options of Avatar addon.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function option_form() {
		$opt = ap_opt();

		ob_start();
		?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#ap-clear-avatar').click(function(e){
						e.preventDefault();
						$.ajax({
							url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
							data: {
								action: 'ap_clear_avatar_cache',
								__nonce: '<?php echo wp_create_nonce( 'clear_avatar_cache' ); ?>'
							},
							success: function(data){
								if(data==='success') alert('All avatar deleted');
							}
						});
					});
				});
			</script>
		<?php
		$js = ob_get_clean();

		$form = array(
			'submit_label' => __( 'Save add-on options', 'anspress-question-answer' ),
			'fields'       => array(
				'clear_avatar_cache' => array(
					'label' => __( 'Clear Cache', 'anspress-question-answer' ),
					'html'  => '<div class="ap-form-fields-in"><a id="ap-clear-avatar" href="#" class="button">' . __( 'Clear avatar cache', 'anspress-question-answer' ) . '</a></div>' . $js,
				),
				'avatar_font'        => array(
					'label'   => __( 'Font family', 'anspress-question-answer' ),
					'desc'    => __( 'Select font family for avatar letters.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'calibri'         => 'Calibri',
						'Pacifico'        => 'Pacifico',
						'OpenSans'        => 'Open Sans',
						'Glegoo-Bold'     => 'Glegoo Bold',
						'DeliusSwashCaps' => 'Delius Swash Caps',
					),
					'value'   => $opt['avatar_font'],
				),
				'avatar_force'       => array(
					'label' => __( 'Force avatar', 'anspress-question-answer' ),
					'desc'  => __( 'Show AnsPress avatars by default instead of gravatar fallback. Useful in localhost development.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['avatar_force'],
				),
			),
		);

		return $form;
	}

	/**
	 * Override get_avatar.
	 *
	 * @param  string         $args         Avatar image.
	 * @param  integer|string $id_or_email  User id or email.
	 * @return string
	 */
	public static function get_avatar( $args, $id_or_email ) {
		$override = apply_filters( 'ap_pre_avatar_url', false, $args, $id_or_email );

		// Return if override is not false.
		if ( false !== $override ) {
			return $override;
		}

		$args['default'] = ap_generate_avatar( $id_or_email );

		// Set default avatar url.
		if ( ap_opt( 'avatar_force' ) ) {
			$args['url'] = ap_generate_avatar( $id_or_email );
		}

		return $args;
	}

	/**
	 * Ajax callback for clearing avatar cache.
	 */
	public static function clear_avatar_cache() {
		check_ajax_referer( 'clear_avatar_cache', '__nonce' );

		if ( current_user_can( 'manage_options' ) ) {
			WP_Filesystem();
			global $wp_filesystem;
			$upload_dir = wp_upload_dir();
			$wp_filesystem->rmdir( $upload_dir['basedir'] . '/ap_avatars', true );
			wp_die( 'success' );
		}

		wp_die( 'failed' );
	}
}

/**
 * Check if avatar exists already.
 *
 * @param integer $user_id User ID or name.
 * @return boolean
 */
function ap_is_avatar_exists( $user_id ) {
	$filename   = md5( $user_id );
	$upload_dir = wp_upload_dir();
	$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';

	return file_exists( $avatar_dir . $filename . '.jpg' );
}

/**
 * Generate avatar.
 *
 * @param integer|string $user_id User ID or name.
 * @return string Link to generated avatar.
 */
function ap_generate_avatar( $user_id ) {
	$avatar = new Avatar\Generator( $user_id );
	$avatar->generate();

	return $avatar->fileurl();
}

// Init class.
Avatar::init();
