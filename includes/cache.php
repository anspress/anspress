<?php
/**
 * Handles AnsPress caching
 *
 * @since 4.0.0
 */

function ap_cache_dir() {
		global $wp;
		$current_url_path = str_replace( rtrim( ap_base_page_link(), '/' ), '', esc_url( home_url( add_query_arg( [], $wp->request ) ) ) );

		$current_url_path = ltrim( $current_url_path, '/' );
	if ( empty( $current_url_path ) ) {
		$current_url_path = 'index';
	}
		$folder = rtrim( $current_url_path, basename( $current_url_path ) );
		return (object) array(
			'current_path' => $current_url_path,
			'filename'     => basename( $current_url_path ) . '.html',
			'folder'       => ANSPRESS_CACHE_DIR . '/' . $folder,
			'path'         => ANSPRESS_CACHE_DIR . '/' . $current_url_path . '.html',
		);
}

function ap_cache_page( $content ) {
	$dir = ap_cache_dir();
	if ( ! is_dir( $dir->folder ) ) {
		mkdir( $dir->folder, 0777, true ); // true for recursive create
	}

	if ( false !== ( $f = @fopen( $dir->path, 'w+' ) ) ) {
		fwrite( $f, $content );
		fclose( $f );
	}
}

function ap_cache_page_get() {
	$dir = ap_cache_dir();
	if ( file_exists( $dir->path ) ) {
		return file_get_contents( $dir->path );
	}

	return false;
}

// return location and name for cache file
/*
function cache_file(){
	return ANSPRESS_CACHE_DIR . md5($_SERVER['REQUEST_URI']);
}

// display cached file if present and not expired
function cache_display() {
	$file = cache_file();

	// check that cache file exists and is not too old
	if( !file_exists($file) ) {
		return;
	}

	if(filemtime($file) < time() - ANSPRESS_CACHE_TIME * 3600) {
		return;
	}

	// if so, display cache file and stop processing
	echo gzuncompress( file_get_contents( $file ) );
}

// write to cache file
function cache_page($content) {
	if(false !== ($f = @fopen(cache_file(), 'w'))) {
		fwrite($f, gzcompress($content));
		fclose($f);
	}
	return $content;
}

// execution stops here if valid cache file found
cache_display();*/
