<?php

// This is global bootstrap for autoloading

use tad\FunctionMocker\FunctionMocker;
use Symfony\Component\Filesystem\Filesystem;

require_once __DIR__ . '/../vendor/autoload.php';

include_once('_support/extra.php');

FunctionMocker::init();


/**
 * Copy plugin source to WP_ROOT.
 */
// $fileSystem = new FileSystem();

// $source = dirname( dirname( __FILE__ ) ) . '/src';
// $target = getenv( 'WP_ROOT' ) . '/wp-content/plugins/pointpress';

// if ( file_exists( $target ) ) {
//     $fileSystem->remove( $target );
// }

// $fileSystem->mkdir( $target );

// $directoryIterator = new \RecursiveDirectoryIterator( $source, \RecursiveDirectoryIterator::SKIP_DOTS );
// $iterator          = new \RecursiveIteratorIterator( $directoryIterator, \RecursiveIteratorIterator::SELF_FIRST );

// foreach ( $iterator as $item ) {
//     if ( $item->isDir() ) {
//         $fileSystem->mkdir($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
//     } else {
//         $fileSystem->copy($item, $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
//     }
// }