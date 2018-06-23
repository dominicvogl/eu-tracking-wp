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

   var $version = '1.0';

   var $settings = array();

   function __construct() {

	   $version = $this->version;

	   $basename = plugin_basename( __FILE__ );
	   $path     = plugin_dir_path( __FILE__ );
	   $url      = plugin_dir_url( __FILE__ );
	   $slug     = dirname( $basename );

	   $this->settings = array (

		   // Basics
		   'name'     => __( 'EU Tracking WP', 'ETWP' ),
		   'version'  => $version,

		   // urls
		   'file'     => __FILE__,
		   'basename' => $basename,
		   'path'     => $path,
		   'url'      => $url,
		   'slug'     => $slug

	   );

	   add_action( 'wp_head', array( $this, 'google_analytics' ) );
	   add_action( 'admin_menu', array( $this, 'ETWP_create_menu' ) );

	   add_action( 'init', array($this, 'register_assets'));

   }

   function register_assets() {

	   if (is_admin()) {

		   $files = array(

			   array(
				   'handle' => 'ETWP-backend',
				   'src' => plugin_dir_url(__FILE__) . 'dist/backend.css',
				   'deps' => array(),
			   ),
			   array(
				   'handle' => 'styles',
				   'src' => get_template_directory_uri() . '/dist/css/app.css',
				   'deps' => array(),
			   )

		   );



	   }

	   foreach ($files as $file) {

		   wp_register_style($file['handle'], $file['src'], $file['deps'], TEMPLATE_VERSION);
		   wp_enqueue_style($file['handle']);

	   }

      wp_register_style('ETWP-backend', plugin_dir_url(__FILE__) . 'dist/backend.css', array(), '1.0');
	   wp_enqueue_style('ETWP-backend');

   }

   function google_analytics() {

	   if ( get_option( 'ga_property' ) ) {
		   $propertyID = get_option( 'ga_property' );
	      ?>
         <script class="js--google-analytics">

             var gaProperty = '<?php echo $propertyID; ?>';
             var disableStr = 'ga-disable-' + gaProperty;
             if (document.cookie.indexOf(disableStr + '=true') > -1) {
                 window[disableStr] = true;
             }
             function gaOptout() {
                 document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
                 window[disableStr] = true;
             }

             (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
             })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

             ga('create', '<?php echo $propertyID; ?>', 'auto');
             ga('set', 'anonymizeIp', true);
             ga('send', 'pageview');

         </script>
         <?php
      }

   }

   // create custom plugin settings menu
   function ETWP_create_menu() {

      //create new top-level menu
      add_menu_page(
	      'My Cool Plugin Settings',
         __('Tracking Settings', 'ETWP'),
         'administrator',
         __FILE__, array($this, 'my_cool_plugin_settings_page'),
         plugins_url('/images/icon.png', __FILE__)
      );

      //call register settings function
      add_action( 'admin_init', array($this, 'register_my_cool_plugin_settings') );
   }


   function register_my_cool_plugin_settings() {
      //register our settings
      register_setting( 'my-cool-plugin-settings-group', 'ga_property' );
      register_setting( 'my-cool-plugin-settings-group', 'cookie_consent_text');
   }

   function my_cool_plugin_settings_page() {
   ?>
   <div class="etwp--admin-wrapper">
      <h1><?php _e('EU Tracking WP', 'ETWP'); ?></h1>

      <form method="post" action="options.php">
          <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
          <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
          <table class="form-table">
              <tr valign="top">
                 <th scope="row"><? _e('Google Analytics Property ID', 'ETWP'); ?></th>
                 <td><input type="text" name="ga_property" value="<?php echo esc_attr( get_option('ga_property') ); ?>" /></td>
              </tr>

              <tr valign="top">
                 <th scope="row"><? _e('Your Cookie Consent Text', 'ETWP'); ?></th>
                 <td><textarea name="cookie_consent_text"><?php echo esc_attr( get_option('cookie_consent_text') ); ?></textarea></td>
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

$EU_TRACKING_WP = new EU_TRACKING_WP();

endif;