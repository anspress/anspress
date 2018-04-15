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
 */

namespace AnsPress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The syntax highlighter class.
 *
 * @since 4.1.0
 */
class Syntax_Highlighter extends \AnsPress\Singleton {
	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * The brushes.
	 *
	 * @var array
	 */
	var $brushes = [];

	/**
	 * Initialize the addon.
	 */
	protected function __construct() {
		$this->brush();
		anspress()->add_filter( 'wp_enqueue_scripts', $this, 'scripts' );
		anspress()->add_action( 'wp_footer', $this, 'output_scripts', 15 );
		anspress()->add_action( 'admin_footer', $this, 'output_scripts' );
		anspress()->add_filter( 'tiny_mce_before_init', $this, 'mce_before_init' );
		anspress()->add_filter( 'ap_editor_buttons', $this, 'editor_buttons', 10, 2 );

		add_shortcode( 'apcode', [ $this, 'shortcode' ] );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @return void
	 */
	public function scripts() {
		$js_url  = ANSPRESS_URL . '/addons/syntaxhighlighter/syntaxhighlighter/scripts/';
		$css_url = ANSPRESS_URL . '/addons/syntaxhighlighter/syntaxhighlighter/styles/';

		$scripts = array(
			'brush-bash'       => 'shBrushBash.js',
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

		foreach ( $scripts as $key => $script ) {
			wp_register_script( 'syntaxhighlighter-' . $key, $js_url . $script, [ 'syntaxhighlighter-core' ], AP_VERSION );
		}

		echo '<script type="text/javascript">AP_Brushes = ' . wp_json_encode( $this->brushes ) . ';</script>';
		wp_register_script( 'syntaxhighlighter-core', $js_url . 'shCore.js', [], AP_VERSION );
		wp_register_script( 'syntaxhighlighter', ANSPRESS_URL . 'addons/syntaxhighlighter/script.js', [ 'syntaxhighlighter-core' ], AP_VERSION );

		// Register theme stylesheets.
		wp_register_style( 'syntaxhighlighter-core', $css_url . 'shCore.css', [], AP_VERSION );
		wp_register_style( 'syntaxhighlighter-theme-default', $css_url . 'shThemeDefault.css', [ 'syntaxhighlighter-core' ], AP_VERSION );
	}

	/**
	 * Define all brush.
	 *
	 * @return void
	 */
	public function brush() {
		$this->brushes = array(
			'php'        => 'PHP',
			'css'        => 'CSS',
			'xml'        => 'XML/HTML',
			'jscript'    => 'Javascript',
			'sql'        => 'SQL',
			'bash'       => 'Bash/Shell',
			'clojure'    => 'Clojure',
			'cpp'        => 'C++/C',
			'csharp'     => 'C#',
			'delphi'     => 'Delphi',
			'diff'       => 'Diff',
			'erlang'     => 'Erlang',
			'fsharp'     => 'F#',
			'groovy'     => 'Groovy',
			'java'       => 'Java',
			'javafx'     => 'JavaFX',
			'latex'      => 'Latex',
			'plain'      => 'Plain text',
			'matlab'     => 'Matlabkey',
			'objc'       => 'Object',
			'perl'       => 'Perl',
			'powershell' => 'PowerShell',
			'python'     => 'Python',
			'r'          => 'R',
			'ruby'       => 'Ruby/Rails',
			'scala'      => 'Scala',
			'vb'         => 'VisualBasic',
		);
	}

	/**
	 * Output required scripts in footer.
	 *
	 * @return void
	 */
	public function output_scripts() {
		if ( ! is_anspress() ) {
			return;
		}

		global $wp_styles;

		$scripts = [];
		foreach ( $this->brushes as $brush => $label ) {
			$scripts[] = 'syntaxhighlighter-brush-' . strtolower( $brush );
		}

		$scripts[] = 'syntaxhighlighter';

		wp_print_scripts( $scripts );

		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
		}

		$sh_css    = '';
		$theme_css = '';

		if ( ! empty( $wp_styles ) && ! empty( $wp_styles->registered ) &&
			! empty( $wp_styles->registered['syntaxhighlighter-core'] ) &&
			! empty( $wp_styles->registered['syntaxhighlighter-core']->src ) ) {
				$sh_css    = add_query_arg( 'ver', AP_VERSION, $wp_styles->registered['syntaxhighlighter-core']->src );
				$theme_css = add_query_arg( 'ver', AP_VERSION, $wp_styles->registered['syntaxhighlighter-theme-default']->src );
		}
		?>
		<script type='text/javascript'>
			if(typeof SyntaxHighlighter !== 'undefined'){
				AnsPress.loadCSS('<?php echo esc_url( $sh_css ); ?>');
				AnsPress.loadCSS('<?php echo esc_url( $theme_css ); ?>');

				aplang = aplang||{};
				aplang.shLanguage = '<?php esc_attr_e( 'Language', 'anspress-question-answer' ); ?>';
				aplang.shInline = '<?php esc_attr_e( 'Is inline?', 'anspress-question-answer' ); ?>';
				aplang.shTxtPlholder = '<?php esc_attr_e( 'Insert code snippet here ...', 'anspress-question-answer' ); ?>';
				aplang.shButton = '<?php esc_attr_e( 'Insert to editor', 'anspress-question-answer' ); ?>';
				aplang.shTitle = '<?php esc_attr_e( 'Insert code', 'anspress-question-answer' ); ?>';

				SyntaxHighlighter.highlight();
			}
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
	 *
	 * @since 4.1.8 Fixed: SCRIPT5022: InvalidCharacterError showing in Edge browser.
	 */
	public function mce_before_init( $options ) {
		if ( ! isset( $options['extended_valid_elements'] ) ) {
			$options['extended_valid_elements'] = '';
		} else {
			$options['extended_valid_elements'] .= ',';
		}

		$options['extended_valid_elements'] = 'pre,code';

		return $options;
	}

	/**
	 * Add insert code button before editor.
	 *
	 * @param string $name  Field name.
	 * @param object $field Field object.
	 * @return void
	 * @since 4.1.8
	 */
	public function editor_buttons( $name, $field ) {
		$field->add_html( '<button type="button" class="ap-btn-insertcode ap-btn-small ap-btn mb-10 mr-5" apinsertcode><i class="apicon-code mr-3"></i>' . __( 'Insert Code', 'anspress-question-answer' ) . '</button>' );
	}

	/**
	 * Render shortcode `[apcode]`.
	 *
	 * @param array  $atts    Attributes.
	 * @param string $content Content
	 * @return string
	 */
	public function shortcode( $atts, $content = '' ) {
		$atts = wp_parse_args( $atts, array(
			'language' => 'plain',
			'inline'   => false,
		) );

		$tag     = $atts['inline'] ? 'code': 'pre';
		$content = preg_replace( '/<br(\s+)?\/?>/i', "", $content );
		$klass = 'class="brush: ' . esc_attr( $atts['language'] ) . '"';
		$content = str_replace( [ '<pre', '<code' ], [ '<pre ' . $klass, '<code ' . $klass ], $content );

 		return $content;
	}
}

// Time to launch the rocket.
Syntax_Highlighter::init();
