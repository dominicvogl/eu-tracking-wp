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

function etwp_get_cc_translations( $string ) {

	$result = get_option($string);

	if($string === $result) {
		return $string;
	}

	if(!empty($result)) {
		return esc_attr($result);
	}

	switch($string) {

		case 'cc_header':
			$result = __('Cookies used on the website!', 'etwp');
			break;

		case 'cc_message':
			$result = __('This website uses cookies to ensure you get the best experience on our website.', 'etwp');
			break;

		case 'cc_url':
			$result = __('privacy-policy', 'etwp');
			break;

		default:
			$result = $string;
			break;

	}

	return $result;

}

function etwp_cc_translations( $string ) {

	if(!empty($string))
		echo etwp_get_cc_translations($string);

}