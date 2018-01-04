<?php
// Here you can initialize variables that will be available to your tests
use Codeception\Configuration;
use Codeception\Util\Autoload;
$support = Configuration::supportDir();

Autoload::addNamespace( '\AnsPress\Tests', $support );

define( 'SMTP_USER',   'user@example.com' );    // Username to use for SMTP authentication
define( 'SMTP_PASS',   'smtp password' );       // Password to use for SMTP authentication
define( 'SMTP_HOST',   'localhost' );    // The hostname of the mail server
define( 'SMTP_FROM',   'aptest@wp.com' ); // SMTP From email address
define( 'SMTP_NAME',   'ApTest' );    // SMTP From name
define( 'SMTP_PORT',   '1025' );                  // SMTP port number - likely to be 25, 465 or 587
define( 'SMTP_SECURE', '' );                 // Encryption system to use - ssl or tls
define( 'SMTP_AUTH',    false );                 // Use SMTP authentication (true|false)
define( 'SMTP_DEBUG',   1 );                    // for debugging purposes only set to 1 or 2

