<?php
/**
 * An AnsPress add-on which for syntax highlighting.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage Syntax Highlighter Addon
 * @since      4.1.0
 *
 * @anspress-addon
 * Addon Name:    Syntax Highlighter
 * Addon URI:     https://anspress.io
 * Description:   Add syntax highlighter support in AnsPress form.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The syntax highlighter class.
 *
 * @since 4.1.0
 */
class AnsPress_Syntax_Highlighter {
	static $brushes = [];

	/**
	 * Initialize the addon.
	 */
	public static function init() {
		self::brush();
		anspress()->add_filter( 'wp_enqueue_scripts', __CLASS__, 'scripts' );
		anspress()->add_filter( 'mce_external_plugins', __CLASS__, 'mce_plugins' );
		anspress()->add_action( 'wp_footer', __CLASS__, 'output_scripts', 15 );
		anspress()->add_action( 'admin_footer', __CLASS__, 'output_scripts' );
		anspress()->add_filter( 'tiny_mce_before_init', __CLASS__, 'mce_before_init' );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @return void
	 */
	public static function scripts() {
		$js_url = ANSPRESS_URL . '/assets/syntaxhighlighter/scripts/';
		$css_url = ANSPRESS_URL . '/assets/syntaxhighlighter/styles/';

		$scripts = array(
			'brush-bash'       => 'shBrushBash.js',
			'brush-coldfusion' => 'shBrushColdFusion.js',
			'brush-cpp'        => 'shBrushCpp.js',
			'brush-csharp'     => 'shBrushCSharp.js',
			'brush-css'        => 'shBrushCss.js',
			'brush-delphi'     => 'shBrushDelphi.js',
			'brush-diff'       => 'shBrushDiff.js',
			'brush-groovy'     => 'shBrushGroovy.js',
			'brush-java'       => 'shBrushJava.js',
			'brush-javafx'     => 'shBrushJavaFX.js',
			'brush-jscript'    => 'shBrushJScript.js',
			'brush-perl'       => 'shBrushPerl.js',
			'brush-perl'       => 'shBrushPerl.js',
			'brush-php'        => 'shBrushPhp.js',
			'brush-plain'      => 'shBrushPlain.js',
			'brush-powershell' => 'shBrushPowerShell.js',
			'brush-python'     => 'shBrushPython.js',
			'brush-ruby'       => 'shBrushRuby.js',
			'brush-scala'      => 'shBrushScala.js',
			'brush-sql'        => 'shBrushSql.js',
			'brush-vb'         => 'shBrushVb.js',
			'brush-xml'        => 'shBrushXml.js',
			'brush-clojure'    => 'shBrushClojure.js',
			'brush-fsharp'     => 'shBrushFSharp.js',
			'brush-latex'      => 'shBrushLatex.js',
			'brush-matlabkey'  => 'shBrushMatlabKey.js',
			'brush-objc'       => 'shBrushObjC.js',
			'brush-r'          => 'shBrushR.js',
		);

		echo '<script type="text/javascript">AP_Brushes = ' . wp_json_encode( self::$brushes )  . ';</script>';
		wp_register_script( 'syntaxhighlighter-core',  $js_url . 'shCore.js', [],  AP_VERSION );

		foreach ( $scripts as $key => $script ) {
			wp_register_script( 'syntaxhighlighter-' . $key,  $js_url . $script, [ 'syntaxhighlighter-core' ],  AP_VERSION );
		}

		// Register theme stylesheets.
		wp_register_style( 'syntaxhighlighter-core', $css_url . 'shCore.css', [], AP_VERSION );
		wp_register_style( 'syntaxhighlighter-theme-default', $css_url . 'shThemeDefault.css', [ 'syntaxhighlighter-core' ], AP_VERSION );
	}

	/**
	 * Define all brush.
	 *
	 * @return void
	 */
	public static function brush() {
		self::$brushes = array(
			'php'           => 'PHP',
			'css'           => 'CSS',
			'xml'           => 'XML/HTML',
			'jscript'       => 'Javascript',
			'sql'           => 'SQL',
			'as3'           => 'Action Script',
			'bash'          => 'Bash/Shell',
			'colfusion'     => 'ColdFusion',
			'clojure'       => 'Clojure',
			'cpp'           => 'C++/C',
			'csharp'        => 'C#',
			'delphi'        => 'Delphi',
			'diff'          => 'Diff',
			'erlang'        => 'Erlang',
			'fsharp'        => 'F#',
			'groovy'        => 'Groovy',
			'java'          => 'Java',
			'javafx'        => 'JavaFX',
			'latex'         => 'Latex',
			'plain'         => 'Plain text',
			'matlab'        => 'Matlabkey',
			'objc'          => 'Object',
			'perl'          => 'Perl',
			'powershell'    => 'PowerShell',
			'python'        => 'Python',
			'r'             => 'R',
			'ruby'          => 'Ruby/Rails',
			'scala'         => 'Scala',
			'vb'            => 'VisualBasic',
		);
	}

	/**
	 * Add tinyMCE plugin.
	 *
	 * @param array $plugins Plugins.
	 * @return array
	 */
	public static function mce_plugins( $plugins ) {
		$plugins[ 'apsyntax' ] = ANSPRESS_URL . 'assets/js/min/tinymce-syntax.min.js';
		return $plugins;
	}

	/**
	 * Output required scripts in footer.
	 *
	 * @return void
	 */
	public static function output_scripts() {
		if ( ! is_anspress() ) {
			return;
		}

		global $wp_styles;

		$scripts = [];
		foreach ( self::$brushes as $brush => $label ) {
			$scripts[] = 'syntaxhighlighter-brush-' . strtolower( $brush );
		}

		wp_print_scripts( $scripts );

		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
		}

		$sh_css = '';
		$theme_css = '';

		if ( ! empty( $wp_styles ) && ! empty( $wp_styles->registered ) &&
			! empty( $wp_styles->registered['syntaxhighlighter-core'] ) &&
			! empty( $wp_styles->registered['syntaxhighlighter-core']->src ) ) {
				$sh_css = add_query_arg( 'ver', AP_VERSION, $wp_styles->registered['syntaxhighlighter-core']->src );
				$theme_css = add_query_arg( 'ver', AP_VERSION, $wp_styles->registered['syntaxhighlighter-theme-default']->src );
		}
		?>
		<script type='text/javascript'>
			(function($){
				SyntaxHighlighter.defaults.toolbar = false;

				$(document).ready(function(){
					AnsPress.loadCSS('<?php echo esc_url( $sh_css ); ?>');
					AnsPress.loadCSS('<?php echo esc_url( $theme_css ); ?>');

					SyntaxHighlighter.highlight();
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Modify tinyMCE options so that we can add our pre tags along with language code.
	 *
	 * Our language code is stored in a custom attribute `aplang`. Also whitelist
	 * `contenteditable` attribute so that we can prevent editing `pre` tag in editor.
	 *
	 * @param array $options TinyMCE options.
	 * @return array
	 */
	public static function mce_before_init( $options ) {
		if ( ! isset( $options['extended_valid_elements'] ) ) {
			$options['extended_valid_elements'] = '';
		} else {
			$options['extended_valid_elements'] .= ',';
		}

		if ( ! isset( $options['custom_elements'] ) ) {
			$options['custom_elements'] = '';
		} else {
			$options['custom_elements'] .= ',';
		}

		$options['extended_valid_elements'] .= 'pre[aplang|contenteditable]';
		$options['custom_elements']         .= 'pre[aplang|contenteditable]';

		return $options;
	}
}

// Time to lunch the rocket.
AnsPress_Syntax_Highlighter::init();
