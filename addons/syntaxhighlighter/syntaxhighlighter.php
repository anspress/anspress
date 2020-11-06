<?php
/**
 * An AnsPress add-on which for syntax highlighting.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
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
		anspress()->add_filter( 'tiny_mce_before_init', $this, 'mce_before_init' );
		anspress()->add_filter( 'ap_editor_buttons', $this, 'editor_buttons', 10, 2 );
		anspress()->add_filter( 'ap_allowed_shortcodes', $this, 'allowed_shortcodes' );

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

		echo '<script type="text/javascript">AP_Brushes = ' . wp_json_encode( $this->brushes ) . ';</script>';
		wp_enqueue_script( 'syntaxhighlighter-core', $js_url . 'shCore.js', [ 'jquery', 'anspress-main' ], AP_VERSION );
		wp_enqueue_script( 'syntaxhighlighter-autoloader', $js_url . 'shAutoloader.js', [ 'syntaxhighlighter-core' ], AP_VERSION );
		wp_enqueue_script( 'syntaxhighlighter', ANSPRESS_URL . 'addons/syntaxhighlighter/script.js', [ 'syntaxhighlighter-core' ], AP_VERSION, true );

		// Register theme stylesheets.
		wp_enqueue_style( 'syntaxhighlighter-core', $css_url . 'shCore.css', [], AP_VERSION );
		wp_enqueue_style( 'syntaxhighlighter-theme-default', $css_url . 'shThemeDefault.css', [ 'syntaxhighlighter-core' ], AP_VERSION );

		ob_start();
		?>
			aplang = aplang||{};
			aplang.shLanguage = '<?php esc_attr_e( 'Language', 'anspress-question-answer' ); ?>';
			aplang.shInline = '<?php esc_attr_e( 'Is inline?', 'anspress-question-answer' ); ?>';
			aplang.shTxtPlholder = '<?php esc_attr_e( 'Insert code snippet here ...', 'anspress-question-answer' ); ?>';
			aplang.shButton = '<?php esc_attr_e( 'Insert to editor', 'anspress-question-answer' ); ?>';
			aplang.shTitle = '<?php esc_attr_e( 'Insert code', 'anspress-question-answer' ); ?>';

			window.apBrushPath = "<?php echo ANSPRESS_URL . '/addons/syntaxhighlighter/syntaxhighlighter/scripts/'; ?>";
		<?php
		$script = ob_get_clean();
		wp_add_inline_script( 'syntaxhighlighter', $script, 'before' );
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
		$field->add_html( '<button type="button" class="ap-btn-insertcode ap-btn-small ap-btn mb-10 ap-mr-5" apinsertcode><i class="apicon-code ap-mr-3"></i>' . __( 'Insert Code', 'anspress-question-answer' ) . '</button>' );
	}

	/**
	 * Add `apcode` to allowed shortcode.
	 *
	 * @param array $allowed Allowed shortcode.
	 * @return array
	 * @since 4.1.8
	 */
	public function allowed_shortcodes( $allowed ) {
		$allowed[] = 'apcode';

		return $allowed;
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
