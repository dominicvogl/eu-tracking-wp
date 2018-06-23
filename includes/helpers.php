<?php

function etwp_get_locale() {

	return is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();

}

function etwp_get_path( $path = '' ) {

	return ETWP_PATH . $path;

}

function etwp_include( $file ) {

	$path = etwp_get_path( $file );

	var_dump($path);

	if( file_exists($path) ) {

		include_once( $path );

	}

}