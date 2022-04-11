<?php

function logException( $exception ) {
    //echo $exception->getMessage();
    error_log( $exception->getMessage() );
    http_response_code(400);
    $result = new Result(ST_ERR, $exception->__toString());
    header('Content-Type: application/json');
    echo json_encode($result);
    exit(EXIT_ERR);
}

function check_for_fatal()
{
    $error = error_get_last();
    if ( $error["type"] == E_ERROR )
        log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
}

function log_error( $num, $str, $file, $line, $context = null )
{
    logException( new ErrorException( $str, 0, $num, $file, $line ) );
}

register_shutdown_function( "check_for_fatal" );
set_error_handler( "log_error" );
set_exception_handler( "logException" );
ini_set( "display_errors", "off" );
error_reporting( E_ALL );

?>