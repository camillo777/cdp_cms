<?php

//error_reporting(E_ALL);
//ini_set( "display_errors", false );

$dbconfig = [
	"host" => "localhost",
	"user" => "prestashop",
	"password" => "prestashop",
	"db" => "camillo"
];

define( "DB_DSN", "mysql:host=localhost;dbname=camillo" );
define( "DB_USERNAME", "prestashop" );
define( "DB_PASSWORD", "prestashop" );

const EXIT_OK = 0;
const EXIT_ERR = 1;

const ST_ERR = "ERR";
const ST_OK = "OK";

define( "SITE_URL", "http://88.198.152.248/camillo" );

define( "CLASS_PATH", "classes" );
define( "MEDIA_PATH", "media" );
define( "TEMPLATE_PATH", "templates" );
define( "HOMEPAGE_NUM_ARTICLES", 5 );
define( "ADMIN_USERNAME", "admin" );
define( "ADMIN_PASSWORD", "admin" );

define( "GOOGLE_SERVICE_ACCOUNT", $_SERVER['DOCUMENT_ROOT']."/camillo/cdp-club-3a7a8-firebase-adminsdk-8djfy-007960c77c.json" );

// function handleException( $exception ) {
// 	echo "Sorry, a problem occurred. Please try later.";
// 	echo $exception->getMessage();
// 	error_log( $exception->getMessage() );
// }

// set_exception_handler( 'handleException' );

?>