<?php
/**
 * Dynamic addon avatar.
 *
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage Dynamic Avatar Addon
 *
 * @anspress-addon
 * Addon Name:    Dynamic Avatar
 * Addon URI:     https://anspress.io
 * Description:   Generate user avatar dynamically.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress avatar generator.
 *
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.ocm>
 * @since 4.0.0
 */

/**
 * AnsPress avatar hook class.
 */
class AnsPress_Avatar_Hook {

	/**
	 * Initialize the class.
	 */
	public static function init() {
		ap_add_default_options([
			'avatar_font'   => 'Pacifico',
			'avatar_force'  => false,
		]);

		anspress()->add_action( 'ap_option_groups', __CLASS__, 'load_options' );
		anspress()->add_filter( 'pre_get_avatar_data', __CLASS__, 'get_avatar', 1000, 3 );
		anspress()->add_action( 'wp_ajax_ap_clear_avatar_cache', __CLASS__, 'clear_avatar_cache' );
	}

	/**
	 * Register Avatar options
	 */
	public static function load_options() {
		ob_start();
		?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#ap-clear-avatar').click(function(e){
						e.preventDefault();
						$.ajax({
							url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
							data: {
								action: 'ap_clear_avatar_cache',
								__nonce: '<?php echo wp_create_nonce( "clear_avatar_cache" ); ?>'
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
		ap_register_option_section( 'addons', basename( __FILE__ ), __( 'Dynamic Avatar', 'anspress-question-answer' ), array(
			array(
				'name'              => 'clear_avatar_cache',
				'type'              => 'custom',
				'html' => '<label class="ap-form-label" for="avatar_font">' . __( 'Clear Cache', 'anspress-question-answer' ) . '</label><div class="ap-form-fields-in"><a id="ap-clear-avatar" href="#" class="button">' . __( 'Clear avatar cache', 'anspress-question-answer' ) . '</a></div>' .$js,
			),
			array(
				'name'              => 'avatar_font',
				'label'             => __( 'Font family', 'anspress-question-answer' ),
				'description'       => __( 'Select font family for avatar letters.', 'anspress-question-answer' ),
				'type'              => 'select',
				'options'			=> array(
					'calibri'         => 'Calibri',
					'Pacifico'        => 'Pacifico',
					'OpenSans'        => 'Open Sans',
					'Glegoo-Bold'     => 'Glegoo Bold',
					'DeliusSwashCaps' => 'Delius Swash Caps',
				),
			),
			array(
				'name'              => 'avatar_force',
				'label'             => __( 'Force avatar', 'anspress-question-answer' ),
				'description'       => __( 'Show AnsPress avatars by default instead of gravatar fallback. Useful in localhost development.', 'anspress-question-answer' ),
				'type'              => 'checkbox',
			),
		));
	}

	/**
	 * Override get_avatar.
	 *
	 * @param  string         $args 		Avatar image.
	 * @param  integar|string $id_or_email 	User id or email.
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

		wp_die('failed');
	}
}

/**
 * Avatar generator.
 */
class AnsPress_Avatar {

	/**
	 * Name of user.
	 *
	 * @var string
	 */
	public $name = 'ap';

	/**
	 * User ID.
	 *
	 * @var integer
	 */
	public $user_id = 0;

	/**
	 * Avatar file name.
	 *
	 * @var string
	 */
	public $filename = '';

	/**
	 * Charecter to show in avatar
	 *
	 * @var integer
	 */
	public $char_count = 2;

	/**
	 * Text color.
	 *
	 * @var string
	 */
	public $text_color = '#ffffff';

	/**
	 * Height of image.
	 *
	 * @var integer
	 */
	public $height = 40;

	/**
	 * Width of image.
	 *
	 * @var integer
	 */
	public $width = 40;

	/**
	 * Font weight.
	 *
	 * @var integer
	 */
	public $font_weight = 600;

	/**
	 * Font family
	 *
	 * @var string
	 */
	public $font_size = 25;

	/**
	 * Colors
	 *
	 * @var array
	 */
	public $colors = [];

	/**
	 * Initialize the class.
	 *
	 * @param integer|string $user User ID or name if anonymous.
	 */
	public function __construct( $user ) {

		if ( is_object( $user ) && ! empty( $user->user_id ) ) {
			$this->user_id = (int) $user->user_id;
			$user = get_userdata( $this->user_id );
			$this->name = esc_attr( $user->display_name );
		} elseif ( is_object( $user ) && $user instanceof WP_user ) {
			$this->name = esc_attr( $user->display_name );
			$this->user_id = $user->ID;
		} elseif ( is_object( $user ) && $user instanceof WP_Comment ) {
			$this->name = esc_attr( $user->comment_author );
			$this->user_id = $user->user_id;
		} elseif ( is_numeric( $user ) && ! empty( $user ) ) {
			$user = get_user_by( 'id', $user );
			$this->name = esc_attr( $user->display_name );
			$this->user_id = $user->ID;
		} else {
			$this->name = empty( $user ) ? 'anonymous' : esc_attr( $user );
			$this->user_id = empty( $user ) ? 'anonymous' : $this->name;
		}

		$this->colors();
		$this->filename();
	}

	/**
	 * File name of a avatar.
	 */
	public function filename() {
		$this->filename = md5( $this->user_id );
	}

	/**
	 * Background colors to be used in image.
	 * Extrated from Google's metallic color.
	 */
	public function colors() {
		$colors = [ '#F44336', '#EF5350', '#F44336', '#E53935', '#D32F2F', '#C62828', '#B71C1C', '#FF5252', '#FF1744', '#D50000', '#E91E63', '#F06292', '#EC407A', '#E91E63', '#D81B60', '#C2185B', '#AD1457', '#880E4F', '#FF80AB', '#FF4081', '#F50057', '#C51162', '#9C27B0', '#BA68C8', '#AB47BC', '#9C27B0', '#8E24AA', '#7B1FA2', '#6A1B9A', '#4A148C', '#EA80FC', '#E040FB', '#D500F9', '#AA00FF', '#673AB7', '#7E57C2', '#673AB7', '#5E35B1', '#512DA8', '#4527A0', '#311B92', '#B388FF', '#7C4DFF', '#651FFF', '#6200EA', '#3F51B5', '#7986CB', '#5C6BC0', '#3F51B5', '#3949AB', '#303F9F', '#283593', '#1A237E', '#536DFE', '#3D5AFE', '#304FFE', '#2196F3', '#42A5F5', '#2196F3', '#1E88E5', '#1976D2', '#1565C0', '#0D47A1', '#448AFF', '#2979FF', '#2962FF', '#03A9F4', '#4FC3F7', '#29B6F6', '#03A9F4', '#039BE5', '#0288D1', '#0277BD', '#01579B', '#40C4FF', '#00B0FF', '#0091EA', '#00BCD4', '#00BCD4', '#00ACC1', '#0097A7', '#00838F', '#006064', '#00B8D4', '#009688', '#4DB6AC', '#26A69A', '#009688', '#00897B', '#00796B', '#00695C', '#004D40', '#00BFA5', '#4CAF50', '#66BB6A', '#4CAF50', '#43A047', '#388E3C', '#2E7D32', '#1B5E20', '#00E676', '#00C853', '#8BC34A', '#8BC34A', '#7CB342', '#689F38', '#558B2F', '#33691E', '#64DD17', '#F0F4C3', '#E6EE9C', '#DCE775', '#D4E157', '#AFB42B', '#9E9D24', '#827717', '#AEEA00', '#FDD835', '#FBC02D', '#F9A825', '#F57F17', '#FFD600', '#FFC107', '#FFD54F', '#FFCA28', '#FFC107', '#FFB300', '#FFA000', '#FF8F00', '#FF6F00', '#FFE57F', '#FFD740', '#FFC400', '#FFAB00', '#FF9800', '#FFB74D', '#FFA726', '#FF9800', '#FB8C00', '#F57C00', '#EF6C00', '#E65100', '#FFAB40', '#FF9100', '#FF6D00', '#FF5722', '#FFAB91', '#FF8A65', '#FF7043', '#FF5722', '#F4511E', '#E64A19', '#D84315', '#BF360C', '#FF9E80', '#FF6E40', '#FF3D00', '#DD2C00', '#795548', '#A1887F', '#8D6E63', '#795548', '#6D4C41', '#5D4037', '#4E342E', '#3E2723', '#757575', '#616161', '#424242', '#212121', '#607D8B', '#78909C', '#607D8B', '#546E7A', '#455A64', '#37474F', '#263238' ];

		/**
		 * Filters avatar addon colors.
		 *
		 * Use this filter to override or add custom colors for avatar.
		 * Colors are selected randomly while creating avatar.
		 *
		 * @param array $colors List of default colors.
		 * @since 4.0.11 Introduced. Requested at @link https://goo.gl/yXN7og.
		 *
		 * @return array
		 */
		$this->colors = apply_filters( 'ap_addon_avatar_colors', $colors );
	}

	/**
	 * Check if avatar for a user already exists.
	 *
	 * @return boolean
	 */
	public function avatar_exists() {
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';

		return file_exists( $avatar_dir . '/' . $this->filename . '.jpg' );
	}

	/**
	 * Return avatar file path.
	 *
	 * @return string
	 */
	public function filepath() {
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';

		// Make dir if does not exists already.
		if ( ! file_exists( $avatar_dir ) ) {
			wp_mkdir_p( $avatar_dir );
		}

		return $avatar_dir . '/' . $this->filename . '.jpg';
	}

	/**
	 * Return url to avatar.
	 *
	 * @return string
	 */
	public function fileurl() {
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['baseurl'] . '/ap_avatars';

		return $avatar_dir . '/' . $this->filename . '.jpg';
	}

	/**
	 * Function to generate letter avatar
	 */
	public function generate() {

		if ( ! function_exists( 'imagecreatetruecolor' ) || $this->avatar_exists() ) {
			return;
		}

		$font = ap_get_theme_location( 'avatar-fonts/' . ap_opt( 'avatar_font' ) . '.ttf' );
		$words = explode( ' ', $this->name );
		$text = '';

		foreach ( $words as $w ) {
			$text .= strtoupper( $w[0] );
		}

		$text = preg_replace( '~^(&([a-zA-Z0-9]);)~', htmlentities( '${1}' ), $text );

		// Convert hex code to RGB.
		$text_color = $this->hex_to_rgb( $this->text_color );

		$im = imagecreatetruecolor( 90, 90 );
		$text_color = imagecolorallocate( $im, $text_color['r'], $text_color['g'], $text_color['b'] );

		// Random background Colors.
		$color_key = array_rand( $this->colors );

		$bg_color = $this->hex_to_rgb( $this->colors[ $color_key ] );
		$bg_color = imagecolorallocate( $im, $bg_color['r'], $bg_color['g'], $bg_color['b'] );

		imagefill( $im, 0, 0, $bg_color );
		list($x, $y) = $this->image_center( $im, $text, $font, $this->font_size );
		imagettftext( $im, $this->font_size, 0, $x, $y, $text_color, $font, $text );

		if ( imagejpeg( $im, $this->filepath(), 90 ) ) {
			imagedestroy( $im );
		}
	}

	/**
	 * Convert hex value to rgb.
	 *
	 * @param string $color Hex color.
	 * @return array
	 */
	protected function hex_to_rgb( $color ) {

		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		if ( 6 === strlen( $color ) ) {
			list( $r, $g, $b ) = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( 3 === strlen( $color ) ) {
			list( $r, $g, $b ) = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return false;
		}

		$r = hexdec( $r );
		$g = hexdec( $g );
		$b = hexdec( $b );

		return [ 'r' => $r, 'g' => $g, 'b' => $b ];
	}

	/**
	 * Get center position on image.
	 *
	 * @param resource|false $image Image resource.
	 * @param string         $text Text.
	 * @param string         $font Font file path.
	 * @param string         $size Size of image.
	 * @param integer        $angle Angle.
	 * @return array
	 */
	protected function image_center( $image, $text, $font, $size, $angle = 8 ) {
		$xi = imagesx( $image );
		$yi = imagesy( $image );
		$box = imagettfbbox( $size, $angle, $font, $text );
		$xr = abs( max( $box[2], $box[4] ) );
		$yr = abs( max( $box[5], $box[7] ) );
		$x = intval( ( $xi - $xr ) / 2 );
		$y = intval( ( $yi + $yr ) / 2 );

		return array( $x, $y );
	}

}

/**
 * Check if avatar exists already.
 *
 * @param integer $user_id User ID or name.
 * @return boolean
 */
function ap_is_avatar_exists( $user_id ) {
	$filename = md5( $user_id );
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
	$avatar = new AnsPress_Avatar( $user_id );
	$avatar->generate();

	return $avatar->fileurl();
}

// Init class.
AnsPress_Avatar_Hook::init();
