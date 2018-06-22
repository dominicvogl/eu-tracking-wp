<?php
/**
 * @package Hello_Dolly
 * @version 1.7
 */
/*
Plugin Name: EU Tracking WP
Plugin URI: https://github.com/dominicvogl/german-tracking-wp.git
Description: Integrate your Tracking the european way
Author: Dominic Vogl
Version: 1.0
Author URI: http://dominicvogl.de/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'EU_TRACKING_WP' ) ) :

	class EU_TRACKING_WP {

		function __construct() {

			add_action( 'wp_head', array($this, 'google_analytics') );

		}

		function google_analytics() {

			$propertyID = 'UA-18703653-4';
			?>

           <script>
               (function (i, s, o, g, r, a, m) {
                   i['GoogleAnalyticsObject'] = r;
                   i[r] = i[r] || function () {
                       (i[r].q = i[r].q || []).push(arguments)
                   }, i[r].l = 1 * new Date();
                   a = s.createElement(o),
                       m = s.getElementsByTagName(o)[0];
                   a.async = 1;
                   a.src = g;
                   m.parentNode.insertBefore(a, m)
               })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

               ga('create', '<?php echo $propertyID; ?>', 'auto');
               ga('set', 'anonymizeIp', true);
               ga('send', 'pageview');

           </script>

			<?php

		}

	}

	$EU_TRACKING_WP = new EU_TRACKING_WP();

endif;