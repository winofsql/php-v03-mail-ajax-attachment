<?php
error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );
session_cache_limiter('nocache');
session_start();

$server = 'LAA1087486-contactsphp';
$user = 'LAA1087486';
$dbname = 'LAA1091811-contactsphp';

$server = 'localhost';
$user = 'root';
$dbname = 'lightbox';

$password = '';

// この下のパスは、config.php を単独で動かして debug.log の1行目を使用する
// set_include_path( get_include_path() . PATH_SEPARATOR . "" );

file_put_contents( "debug.log", realpath(".") . "\n", FILE_APPEND );
file_put_contents( "debug.log", get_include_path() . "\n", FILE_APPEND );

?>
