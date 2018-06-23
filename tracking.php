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
	die('No Script Kiddys please!');
} // Exit if accessed directly

if ( ! class_exists( 'EU_TRACKING_WP' ) ) :

class EU_TRACKING_WP {

   var $version = '1.0';
   var $settings = array();

	function define( $name, $value = true ) {

		if( !defined($name) ) {
			define( $name, $value );
		}

	}

   function __construct() {
       // This guy does nothing
   }

	function initialize() {

	   $version = $this->version;

	   $basename = plugin_basename( __FILE__ );
	   $path     = plugin_dir_path( __FILE__ );
	   $url      = plugin_dir_url( __FILE__ );
	   $slug     = dirname( $basename );

	   $this->settings = array (

		   // Basics
		   'name'     => __( 'EU Tracking WP', 'etwp' ),
		   'version'  => $version,

		   // urls
		   'file'     => __FILE__,
		   'basename' => $basename,
		   'path'     => $path,
		   'url'      => $url,
		   'slug'     => $slug

	   );

	   // define constants
      $this->define('ETWP_PATH', $path);

	   include_once( ETWP_PATH . 'includes/helpers.php');

	   $this->load_plugin_textdomain();

	   // Actions
	   add_action( 'admin_menu', array( $this, 'ETWP_create_menu' ) );
	   add_action( 'wp_head', array( $this, 'google_analytics' ) );
	   add_action( 'init', array($this, 'register_assets') );
	   add_action( 'wp_footer', array($this, 'init_cookieconsent'));

	   // GA Output Shortcode
	   add_shortcode( 'gaoptout', array($this, 'ga_optout') );
   }

	function load_plugin_textdomain() {

		// vars
		$domain = 'etwp';
		$locale = apply_filters( 'plugin_locale', etwp_get_locale(), $domain );
		$mofile = $domain . '-' . $locale . '.mo';


		// load from the languages directory first
		load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile );

		// redirect missing translations
		$mofile = str_replace('en_EN', 'en_EN', $mofile);

		// load from plugin lang folder
		load_textdomain( $domain, etwp_get_path('lang/' . $mofile) );

	}

	function register_assets() {

		$this->load_css();
		$this->load_javascript();

	}

	function load_css() {

		$files = array();

		if (is_admin()) {

			$files = array(

				array(
					'handle' => 'backend',
					'src' => $this->settings['url'] . 'dist/admin/backend.css',
					'deps' => array(),
				)

			);

		}
        elseif(!is_admin()) {

			$files = array(

				array(
					'handle' => 'cookieconsent-css',
					'src' => $this->settings['url'] . 'dist/frontend/cookieconsent.min.css',
					'deps' => array(),
				)

			);

		}

		foreach ($files as $file) {

			wp_register_style($file['handle'], $file['src'], $file['deps'], $this->settings['version']);
			wp_enqueue_style($file['handle']);

		}

	}

	function load_javascript()
	{

		if (!is_admin()) {

			$files = array(

				array(
					'handle' => 'cookieconsent-js',
					'src' => $this->settings['url'] . '/dist/frontend/cookieconsent.min.js',
					'deps' => array(),
				)

			);

			foreach ($files as $file) {

				wp_register_script($file['handle'], $file['src'], $file['deps'], $this->settings['version']);
				wp_enqueue_script($file['handle']);

			}

		}

	}

   function init_cookieconsent() {
	   echo getcwd();
      ?>
      <script class="js--cookieconsent">

          window.cookieconsent.initialise({
              container: document.getElementById("content"),
              palette:{
                  popup: {background: "#fff"},
                  button: {background: "#aa0000"}
              },
              type: 'opt-in',
              revokable: false,
              onInitialise: function (status) {
                  var type = this.options.type;
                  var didConsent = this.hasConsented();
                  if (type === 'opt-in' && status === 'allow' && didConsent) {
                      if(typeof load_Marketing_Tracking() === 'function') {
                        load_Marketing_Tracking();
                      }
                  }
              },
              onStatusChange: function(status, chosenBefore) {
                  var type = this.options.type;
                  var didConsent = this.hasConsented();
                  console.log(status === 'allow' ?
                      'enable cookies' : 'disable cookies');

                  if (type === 'opt-in' && status === 'allow' && didConsent) {
                      if(typeof load_Marketing_Tracking() === 'function') {
                          load_Marketing_Tracking();
                      }
                  }
              },
              law: {
                  regionalLaw: false
              },
              location: false,
              content: {
                  header: 'Cookies used on the website!',
                  message: "<?php echo esc_attr( get_option('cc_message') ); ?>",
                  dismiss: 'No cookies!',
                  allow: 'Allow cookies',
                  deny: 'Decline',
                  link: 'Learn more',
                  href: 'http://cookiesandyou.com',
                  close: '&#x274c;'
              }
          });


      </script>
      <?php

   }

	function ga_optout($atts, $content = null) {
		return '<a href="javascript:gaOptout();">'.$content.'</a>';
	}



   function google_analytics() {

	   if ( get_option( 'ga_property' ) ) {
	      ?>
         <script class="js--google-analytics">

             var load_Marketing_Tracking = function() {

                 if (document.cookie.indexOf(disableStr + '=true') > -1) {
                     window[disableStr] = true;
                 }

                 var gaProperty = '<?php esc_attr_e( get_option( 'ga_property' ) ); ?>';
                 var disableStr = 'ga-disable-' + gaProperty;

                 var gaOptout = function() {
                     document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
                     window[disableStr] = true;
                 };

                 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                     (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                     m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                 })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

                 ga('create', gaProperty, 'auto');
                 ga('set', 'anonymizeIp', true);
                 ga('send', 'pageview');

             };

         </script>
         <?php
      }

   }

   // create custom plugin settings menu
   function ETWP_create_menu() {

      //create new top-level menu
      add_menu_page(
	      'My Cool Plugin Settings',
         __('EU Tracking', 'etwp'),
         'administrator',
         __FILE__, array($this, 'my_cool_plugin_settings_page'),
         'dashicons-chart-line'
      );

      //call register settings function
      add_action( 'admin_init', array($this, 'register_my_cool_plugin_settings') );
   }


   function register_my_cool_plugin_settings() {
      //register our settings
      register_setting( 'my-cool-plugin-settings-group', 'ga_property' );
      register_setting( 'my-cool-plugin-settings-group', 'cc_message');
   }

   function my_cool_plugin_settings_page() {
   ?>
   <div class="etwp--admin-wrapper">
      <h1><?php _e('EU Tracking WP', 'etwp'); ?></h1>

      <form method="post" action="options.php">
          <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
          <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>

         <table class="form-table">
              <tr valign="top">
                 <th scope="row"><? _e('Google Analytics Property ID', 'etwp'); ?></th>
                 <td><input type="text" name="ga_property" value="<?php echo esc_attr( get_option('ga_property') ); ?>" /></td>
              </tr>

              <tr valign="top">
                 <th scope="row"><? _e('Your Cookieconsent Message', 'etwp'); ?></th>
                 <td><textarea name="cc_message"><?php echo esc_attr( get_option('cc_message') ); ?></textarea></td>
              </tr>

              <tr valign="top">
                 <th scope="row">Options, Etc.</th>
                 <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
              </tr>
          </table>

          <?php submit_button(); ?>

      </form>
   </div>
   <?php }

}

function eu_tracking_wp() {

   // globals
   global $eu_tracking_wp;


   // initialize
   if( !isset($eu_tracking_wp) ) {
	   $eu_tracking_wp = new EU_TRACKING_WP();
	   $eu_tracking_wp->initialize();
   }


   // return
   return $eu_tracking_wp;

}

// initialize
eu_tracking_wp();

endif;