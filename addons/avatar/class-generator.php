<?php
/**
 * Avatar generator class.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage Dynamic Avatar Addon
 * @since      4.1.8
 */

namespace Anspress\Addons\Avatar;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Avatar generator.
 *
 * @since 4.1.8
 */
class Generator {

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
	 * @param integer|string $user User ID or name if non-loggedin user.
	 */
	public function __construct( $user ) {
		if ( is_object( $user ) && ! empty( $user->user_id ) ) {
			$this->user_id = (int) $user->user_id;
			$user          = get_userdata( $this->user_id );
			$this->name    = esc_attr( $user->display_name );
		} elseif ( is_object( $user ) && $user instanceof \WP_user ) {
			$this->name    = esc_attr( $user->display_name );
			$this->user_id = $user->ID;
		} elseif ( is_object( $user ) && $user instanceof \WP_Comment ) {
			$this->name    = esc_attr( $user->comment_author );
			$this->user_id = $user->user_id;
		} elseif ( is_numeric( $user ) && ! empty( $user ) ) {
			$user          = get_user_by( 'id', $user );
			$this->name    = esc_attr( $user->display_name );
			$this->user_id = $user->ID;
		} else {
			$this->name    = empty( $user ) ? 'anonymous' : esc_attr( $user );
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
		$colors = [ '#EA526F', '#FF0038', '#3C91E6', '#D64933', '#00A878', '#0A2472', '#736B92', '#FFAD05', '#DD9787', '#74D3AE', '#B9314F', '#878472', '#983628', '#E2AEDD', '#1B9AAA', '#FFC43D', '#4F3824', '#7A6F9B', '#376996', '#7B904B', '#613DC1' ];

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

		$font  = ap_get_theme_location( 'avatar-fonts/' . ap_opt( 'avatar_font' ) . '.ttf' );
		$words = explode( ' ', $this->name );
		$text  = '';

		foreach ( $words as $w ) {
			$text .= strtoupper( $w[0] );
		}

		$text = preg_replace( '~^(&([a-zA-Z0-9]);)~', htmlentities( '${1}' ), $text );

		// Convert hex code to RGB.
		$text_color = $this->hex_to_rgb( $this->text_color );

		$im         = imagecreatetruecolor( 90, 90 );
		$text_color = imagecolorallocate( $im, $text_color['r'], $text_color['g'], $text_color['b'] );

		// Random background Colors.
		$color_key = array_rand( $this->colors );

		$bg_color = $this->colors[ $color_key ];
		$this->image_gradientrect( $im, $bg_color, $this->color_luminance( $bg_color, 0.10 ) );
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

		return [
			'r' => $r,
			'g' => $g,
			'b' => $b,
		];
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
		$xi  = imagesx( $image );
		$yi  = imagesy( $image );
		$box = imagettfbbox( $size, $angle, $font, $text );
		$xr  = abs( max( $box[2], $box[4] ) );
		$yr  = abs( max( $box[5], $box[7] ) );
		$x   = intval( ( $xi - $xr ) / 2 );
		$y   = intval( ( $yi + $yr ) / 2 );

		return array( $x, $y );
	}

	/**
	 * Fill gradient.
	 *
	 * @param resource $img   Image resource.
	 * @param string   $start Start color.
	 * @param string   $end   End color.
	 * @return boolean
	 */
	private function image_gradientrect( $img, $start, $end ) {
		$x  = 0;
		$y  = 0;
		$x1 = 90;
		$y1 = 90;

		if ( $x > $x1 || $y > $y1 ) {
			return false;
		}

		$start = str_replace( '#', '', $start );
		$end   = str_replace( '#', '', $end );

		$s = array(
			hexdec( substr( $start, 0, 2 ) ),
			hexdec( substr( $start, 2, 2 ) ),
			hexdec( substr( $start, 4, 2 ) ),
		);

		$e = array(
			hexdec( substr( $end, 0, 2 ) ),
			hexdec( substr( $end, 2, 2 ) ),
			hexdec( substr( $end, 4, 2 ) ),
		);

		$steps = $y1 - $y;
		for ( $i = 0; $i < $steps; $i++ ) {
			$r     = $s[0] - ( ( ( $s[0] - $e[0] ) / $steps ) * $i );
			$g     = $s[1] - ( ( ( $s[1] - $e[1] ) / $steps ) * $i );
			$b     = $s[2] - ( ( ( $s[2] - $e[2] ) / $steps ) * $i );
			$color = imagecolorallocate( $img, $r, $g, $b );
			imagefilledrectangle( $img, $x, $y + $i, $x1, $y + $i + 1, $color );
		}

		return true;
	}

	/**
	 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.
	 *
	 * @param string $hex     Colour as hexadecimal (with or without hash).
	 * @param float  $percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() ).
	 * @return str Lightened/Darkend colour as hexadecimal (with hash);
	 */
	private function color_luminance( $hex, $percent ) {
		// Validate hex string.
		$hex     = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';

		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}

		// Convert to decimal and change luminosity.
		for ( $i = 0; $i < 3; $i++ ) {
			$dec      = hexdec( substr( $hex, $i * 2, 2 ) );
			$dec      = min( max( 0, $dec + $dec * $percent ), 255 );
			$new_hex .= str_pad( dechex( $dec ), 2, 0, STR_PAD_LEFT );
		}

		return $new_hex;
	}

}
