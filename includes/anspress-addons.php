<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

class AP_Addons
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		$addons = ap_read_addons();
		if(!empty($addons))
			foreach($addons as $addon){
				$include = ANSPRESS_ADDON_DIR.$addon['folder']. DS .$addon['file'];
				
				if(file_exists($include))
					require_once( $include );
			}
    }

}

function ap_read_addons(){
	$addons = array();
	//load files from addons folder
	$files=glob(ANSPRESS_DIR.'/addons/*/addon.php');
	//print_r($files);
	foreach ($files as $file){
		$data = ap_get_addon_data($file);
		$data['folder'] = basename(dirname($file));
		$data['file'] = basename($file);
		$addons[] = $data;
	}
	return $addons;
}


function ap_get_addon_data( $plugin_file) {
	$plugin_data = ap_get_file_data( $plugin_file);

	return $plugin_data;
}

function ap_get_file_data( $file) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 1000 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	$metadata=ap_addon_metadata($file_data, array(
		'name' 				=> 'Name',
		'version' 			=> 'Version',
		'description' 		=> 'Description',
		'author' 			=> 'Author',
		'author_uri' 		=> 'Author URI',
		'addon_uri' 		=> 'Addon URI'
	));

	return $metadata;
}

function ap_addon_metadata($contents, $fields){
	$metadata=array();

	foreach ($fields as $key => $field)
		if (preg_match('/'.str_replace(' ', '[ \t]*', preg_quote($field, '/')).':[ \t]*([^\n\f]*)[\n\f]/i', $contents, $matches))
			$metadata[$key]=trim($matches[1]);
	
	return $metadata;
}