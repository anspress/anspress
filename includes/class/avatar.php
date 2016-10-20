<?php
/**
 * AnsPress avatar generator.
 *
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.ocm>
 * @since 4.0.0
 */

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
	 * SVG data.
	 *
	 * @var string
	 */
	public $svg = '';

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
	 * Height of SVG.
	 *
	 * @var integer
	 */
	public $height = 40;

	/**
	 * Width of SVG.
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
	public $font_family = 'HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica, Arial,Lucida Grande, sans-serif';

	/**
	 * Initialize the class.
	 *
	 * @param integer|string $user User ID or name if anonymous.
	 */
	public function __construct( $user ) {
		if ( is_numeric( $user ) && ! empty( $user ) ) {
			$user = get_user_by( 'id', $user );
			$this->name = esc_attr( $user->display_name );
			$this->user_id = $user->ID;
		} elseif ( $user instanceof WP_user ) {
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
	 * Background colors to be used in SVG.
	 * Extrated from Google's metallic color.
	 */
	public function colors() {
		$this->colors = [ '#F44336', '#EF5350', '#F44336', '#E53935', '#D32F2F', '#C62828', '#B71C1C', '#FF5252', '#FF1744', '#D50000', '#E91E63', '#F06292', '#EC407A', '#E91E63', '#D81B60', '#C2185B', '#AD1457', '#880E4F', '#FF80AB', '#FF4081', '#F50057', '#C51162', '#9C27B0', '#BA68C8', '#AB47BC', '#9C27B0', '#8E24AA', '#7B1FA2', '#6A1B9A', '#4A148C', '#EA80FC', '#E040FB', '#D500F9', '#AA00FF', '#673AB7', '#7E57C2', '#673AB7', '#5E35B1', '#512DA8', '#4527A0', '#311B92', '#B388FF', '#7C4DFF', '#651FFF', '#6200EA', '#3F51B5', '#7986CB', '#5C6BC0', '#3F51B5', '#3949AB', '#303F9F', '#283593', '#1A237E', '#536DFE', '#3D5AFE', '#304FFE', '#2196F3', '#42A5F5', '#2196F3', '#1E88E5', '#1976D2', '#1565C0', '#0D47A1', '#448AFF', '#2979FF', '#2962FF', '#03A9F4', '#4FC3F7', '#29B6F6', '#03A9F4', '#039BE5', '#0288D1', '#0277BD', '#01579B', '#40C4FF', '#00B0FF', '#0091EA', '#00BCD4', '#00BCD4', '#00ACC1', '#0097A7', '#00838F', '#006064', '#00B8D4', '#009688', '#4DB6AC', '#26A69A', '#009688', '#00897B', '#00796B', '#00695C', '#004D40', '#00BFA5', '#4CAF50', '#66BB6A', '#4CAF50', '#43A047', '#388E3C', '#2E7D32', '#1B5E20', '#00E676', '#00C853', '#8BC34A', '#8BC34A', '#7CB342', '#689F38', '#558B2F', '#33691E', '#64DD17', '#F0F4C3', '#E6EE9C', '#DCE775', '#D4E157', '#AFB42B', '#9E9D24', '#827717', '#AEEA00', '#FDD835', '#FBC02D', '#F9A825', '#F57F17', '#FFD600', '#FFC107', '#FFD54F', '#FFCA28', '#FFC107', '#FFB300', '#FFA000', '#FF8F00', '#FF6F00', '#FFE57F', '#FFD740', '#FFC400', '#FFAB00', '#FF9800', '#FFB74D', '#FFA726', '#FF9800', '#FB8C00', '#F57C00', '#EF6C00', '#E65100', '#FFAB40', '#FF9100', '#FF6D00', '#FF5722', '#FFAB91', '#FF8A65', '#FF7043', '#FF5722', '#F4511E', '#E64A19', '#D84315', '#BF360C', '#FF9E80', '#FF6E40', '#FF3D00', '#DD2C00', '#795548', '#A1887F', '#8D6E63', '#795548', '#6D4C41', '#5D4037', '#4E342E', '#3E2723', '#757575', '#616161', '#424242', '#212121', '#607D8B', '#78909C', '#607D8B', '#546E7A', '#455A64', '#37474F', '#263238' ];
	}

	/**
	 * Check if avatar for a user already exists.
	 *
	 * @return boolean
	 */
	public function avatar_exists() {
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';

		return file_exists( $avatar_dir . '/' . $this->filename . '.svg' );
	}

	/**
	 * Generate avatar and save it in svg file.
	 *
	 * @return string Link to svg file created.
	 */
	public function generate() {
		if ( $this->avatar_exists() ) {
			return;
		}

		$uc_name = strtoupper( substr( $this->name , 0, 2 ) );

		if ( false === strpos( strtolower( $this->name ), 'anonymous' ) ) {
			$svg_text = '<text text-anchor="middle" y="50%" x="50%" dy="0.35em" pointer-events="auto" fill="' . $this->text_color . '" font-family="' . $this->font_family . '" style="font-weight:' . $this->font_weight . ';font-size:12px;">' . $uc_name . '</text>';
		} else {
			$svg_text = '<g><path fill="#FFFFFF" d="M14.719,20.104c1.224,0,2.218-1.5,2.218-3.343c0-1.842-0.995-3.342-2.218-3.342
			c-1.224,0-2.22,1.5-2.22,3.342C12.499,18.604,13.495,20.104,14.719,20.104z"/><path fill="#FFFFFF" d="M25.279,20.104c1.225,0,2.219-1.5,2.219-3.343c0-1.842-0.994-3.342-2.219-3.342s-2.219,1.5-2.219,3.342
			C23.061,18.604,24.055,20.104,25.279,20.104z"/><path fill="#FFFFFF" d="M30.753,21.714c-0.137-0.138-0.344-0.175-0.517-0.091c-2.785,1.328-6.42,2.06-10.237,2.06
			c-3.815,0-7.451-0.731-10.236-2.06c-0.174-0.083-0.381-0.046-0.516,0.091c-0.135,0.136-0.168,0.344-0.083,0.517
			c1.266,2.562,5.722,4.351,10.835,4.351c5.118,0,9.574-1.789,10.838-4.352C30.922,22.058,30.888,21.85,30.753,21.714z"/></g>';
		}

		$color_key = array_rand( $this->colors );
		$color = $this->colors[ $color_key ];
		$this->svg = '<svg version="1.1" id="Layer_1" pointer-events="none"
	 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px"
	 viewBox="0 0 40 40" xml:space="preserve" style="background:' . $color . '">' . $svg_text . '</svg>';

		$handle = fopen( $this->filepath(), 'w+' );
		fwrite( $handle, $this->svg ); // @codingStandardsIgnoreLine
		fclose( $handle );
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

		return $avatar_dir . '/' . $this->filename . '.svg';
	}

	/**
	 * Return url to avatar.
	 *
	 * @return string
	 */
	public function fileurl() {
		$upload_dir = wp_upload_dir();
		$avatar_dir = $upload_dir['baseurl'] . '/ap_avatars';

		return $avatar_dir . '/' . $this->filename . '.svg';
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

	return file_exists( $avatar_dir . $filename . '.svg' );
}

/**
 * Generate avatar.
 *
 * @param integer $user_id User ID or name.
 * @return string Link to generated avatar.
 */
function ap_generate_avatar( $user_id ) {
	$avatar = new AnsPress_Avatar( $user_id );
	$avatar->generate();

	return $avatar->fileurl();
}

