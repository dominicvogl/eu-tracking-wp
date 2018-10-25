<?php
/*
Plugin Name: EU Tracking for Wordpress
Plugin URI: https://github.com/dominicvogl/german-tracking-wp.git
Description: Integrate your Tracking right passing the EU GDRP rules
Author: Dominic Vogl
Version: 1.0.1
Author URI: https://www.github.com/dominicvogl/
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('No Script Kiddys please!');
} // Exit if accessed directly

if ( ! class_exists( 'EU_TRACKING_WP' ) ) {

class EU_TRACKING_WP {

	var $version = '1.0.1';
	var $settings = array();

	function define( $name, $value = true ) {

		if ( ! defined( $name ) ) {
			define( $name, $value );
		}

	}

	function __construct() {
		// This guy does nothing because we have the initialize function
	}

	function initialize() {

		$version = $this->version;

		$basename = plugin_basename( __FILE__ );
		$path     = plugin_dir_path( __FILE__ );
		$url      = plugin_dir_url( __FILE__ );
		$slug     = dirname( $basename );

		$this->settings = array(

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
		$this->define( 'ETWP_PATH', $path );

		include_once( ETWP_PATH . 'includes/helpers.php' );

		$this->load_plugin_textdomain();

		// Actions
		add_action( 'admin_menu', array( $this, 'ETWP_create_menu' ) );
		add_action( 'wp_head', array( $this, 'google_analytics' ) );
		add_action( 'init', array( $this, 'register_assets' ) );
		add_action( 'wp_footer', array( $this, 'init_cookieconsent' ) );

		/**
		 * @deprecated 1.0.1 not needed anymore, because Google Analytics will only be loaded when there is an opt-in
		 */
		add_shortcode( 'gaoptout', array( $this, 'ga_optout' ) );
	}

	/**
	 * load plugin textdomain
	 */

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

	/**
	 * Load CSS and Javascript Assets
	 */

	function register_assets() {

		$this->load_css();
		$this->load_javascript();

	}

	/**
	 * Load CSS files they are for necessary for the plugin
	 * @param
	 * @return void
	 */

	function load_css() {

		$files = array();

		if (is_admin()) {
			$files = array(
				array(
					'handle' => 'backend',
					'src' => $this->settings['url'] . 'dist/admin/backend.css',
					'deps' => array(),
					'ver' => $this->settings['version']
				)
			);
		}
        elseif(!is_admin()) {
			$files = array(
				array(
					'handle' => 'cookieconsent-css',
					'src' => $this->settings['url'] . 'dist/frontend/cookieconsent.min.css',
					'deps' => array(),
					'ver' => $this->settings['version']
				)
			);
		}

		$this->register_styles($files);

	}

	/**
	 * register passed files from array
	 * @param $files array
	 * @author Dominic Vogl <dv@cat-ia.de>
	 * @since 1.0.1
	 */

	function register_styles( $files ) {

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				wp_register_style( $file['handle'], $file['src'], $file['deps'], $file['ver'] );
				wp_enqueue_style( $file['handle'] );
			}
		}
	}

	/**
	 * Load CSS files they are for necessary for the plugin
	 */

	function load_javascript() {

		if ( ! is_admin() ) {
			$files = array(
				array(
					'handle' => 'cookieconsent-js',
					'src'    => $this->settings['url'] . '/dist/frontend/cookieconsent.min.js',
					'deps'   => array(),
					'ver'    => $this->settings['version']
				)
			);

			$this->register_scripts( $files );
		}
	}

	/**
	 * register passed files from array
	 * @param $files array
	 * @author Dominic Vogl <dv@cat-ia.de>
	 * @since 1.0.1
	 */

	function register_scripts( $files ) {

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				wp_register_script( $file['handle'], $file['src'], $file['deps'], $file['ver'] );
				wp_enqueue_script( $file['handle'] );
			}
		}
	}

	/**
	 * renders js for cookieconsent
	 * @author Dominic Vogl <dv@cat-ia.de>
	 * @since 1.0.0
	 */

	function init_cookieconsent() {
		?>

		<script class="js--cookieconsent">

            window.cookieconsent.initialise({
                container: document.getElementById("content"),
                palette: <?php echo ! empty( get_option( 'cc_palette' ) ) ? get_option( 'cc_palette' ) : 'null'; ?>,
                type: 'opt-in',
                revokable: false,
                position: 'top',
                static: true,
                law: {
                    regionalLaw: false
                },
                location: false,
                content: {
                    header: "<?php etwp_cc_translations( 'cc_header' ); ?>",
                    message: "<?php etwp_cc_translations( 'cc_message' ); ?>",
                    dismiss: "<?php _e( 'Do not allow cookies', 'etwp' ); ?>",
                    allow: "<?php _e( 'Allow Cookies', 'etwp' ); ?>",
                    deny: "<?php _e( 'Decline', 'etwp' ); ?>",
                    link: "<?php _e( 'Learn more', 'etwp' ); ?>",
                    href: "<?php echo home_url( __( '/privacy-policy/', 'etwp' ) ); ?>",
                    close: '&#x274c;'
                },
                onInitialise: function (status) {
                    do_CookieConsent(status, this);
                },
                onStatusChange: function (status, chosenBefore) {
                    do_CookieConsent(status, this);
                }
            });

		</script>

		<?php
	}

	/**
	 * Returns Google Analytics Opt-Out for Shortcode usage in Privacy Policy
	 * @param $atts
	 * @param null $content
	 * @deprecated 1.0.1 not needed anymore, because Google Analytics will only be loaded when there is an opt-in
	 * @return string
	 */

	function ga_optout( $atts, $content = null ) {
		return '<a href="javascript:gaOptout();">' . $content . '</a>';
	}

	/**
	 * Includes Google Analytics Rendering Script, if Property is available
	 * @since 1.0.0
	 * @version 1.0.1
	 * @author Dominic Vogl <dv@cat-ia.de>
	 */

	function google_analytics() {

		if ( get_option( 'ga_property' ) || get_option( 'gtag_property' )) {
			?>
			<script class="js--google-analytics">

				var do_CookieConsent = function(status, currentItem) {

                    var type = currentItem.options.type;
                    var didConsent = currentItem.hasConsented();

                    // console debugging / tracking
                    console.info(status === 'allow' ?
                        '[<?php echo $this->version; ?>] eu-tracking-wp: Cookies enabled' : '[<?php echo $this->version; ?>] eu-tracking-wp: Cookies disabled');

                    if (type === 'opt-in' && status === 'allow' && didConsent) {
    
                        var gaProperty = '<?php esc_attr_e( get_option( 'ga_property' ) ); ?>';
                        var gtagProperty = '<?php esc_attr_e( get_option( 'gtag_property' ) ); ?>';
                        
                        if(gaProperty.length > 0) {
                            load_Marketing_Tracking(gaProperty);
                        }
                        
                        if(gtagProperty.length > 0) {
                            load_googleTagManager(gtagProperty);
                        }
                    }
				};

                var load_Marketing_Tracking = function (gaProperty) {
                    
                    if (document.cookie.indexOf(disableStr + '=true') > -1) {
                        window[disableStr] = true;
                    }

                    //var gaProperty = '<?php //esc_attr_e( get_option( 'ga_property' ) ); ?>//';
                    var disableStr = 'ga-disable-' + gaProperty;

                    var gaOptout = function () {
                        document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
                        window[disableStr] = true;
                    };

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

                    ga('create', gaProperty, 'auto');
                    ga('set', 'anonymizeIp', true);
                    ga('send', 'pageview');
    
                };
                
                var load_googleTagManager = function (gtagProperty) {
                    
                    <!-- Google Tag Manager -->
                    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                    })(window,document,'script','dataLayer', gtagProperty);
                    <!-- End Google Tag Manager -->
                
                }

			</script>
			<?php
		}

   }

	/**
	 * Create custom plugin settings menu
	 * @since 1.0.0
	 * @author Dominic Vogl <dv@cat-ia.de>
	 */

	function ETWP_create_menu() {

		//create new top-level menu
		add_menu_page(
			__( 'EU Tracking - Optionen', 'etwp' ),
			__( 'EU Tracking', 'etwp' ),
			'administrator',
			__FILE__, array( $this, 'etwp_settings_page' ),
			'dashicons-chart-line'
		);

		//call register settings function
		add_action( 'admin_init', array( $this, 'register_etwp_settings' ) );
	}

	/**
	 * register settings for menu in backend
	 * @since 1.0.0
	 * @author Dominic Vogl <dv@cat-ia.de>
	 */

	function register_etwp_settings() {

		// list of settings to register
		$inputs = array(
			'ga_property',
			'gtag_property',
			'cc_header',
			'cc_message',
			'cc_url',
			'cc_palette'
		);

		// register every element of array as setting
		foreach($inputs as $input) {
			register_setting( 'etwp-settings-group', $input );
		}
	}

	/**
	 * Create fields for settings page n the backend
	 * @since 1.0.0
	 * @author Dominic Vogl <dv@cat-ia.de>
	 */

	function etwp_settings_page() {
		?>
		<div class="etwp--admin-wrapper">
			<h1><?php _e( 'EU Tracking WP', 'etwp' ); ?></h1>

			<form method="post" action="options.php">
				<?php settings_fields( 'etwp-settings-group' ); ?>
				<?php do_settings_sections( 'etwp-settings-group' ); ?>
                
                <h2>Cookie Consent Settings and Styles</h2>
                <p><?php _e('Setup your Cookie Consent styles and settings', 'etwp'); ?></p>
                
                <table class="form-table">
                    
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Cookie Consent Header', 'etwp' ); ?></th>
                        <td><input type="text" name="cc_header" value="<?php echo esc_attr( get_option( 'cc_header' ) ); ?>"/>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><? _e( 'Your Cookieconsent Message', 'etwp' ); ?></th>
                        <td><textarea name="cc_message"><?php echo esc_attr( get_option( 'cc_message' ) ); ?></textarea></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><? _e( 'Color Scheme', 'etwp' ); ?></th>
                        <td><textarea name="cc_palette"><?php echo esc_attr( get_option( 'cc_palette' ) ); ?></textarea></td>
                    </tr>
                
                </table>
                
                <h2>Google Analytics Universal Tracking</h2>
                <p><?php _e('If you are using the Google Universal Tracking script, enter your data in this area', 'etwp'); ?></p>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><? _e( 'Google Analytics Property ID', 'etwp' ); ?></th>
						<td><input type="text" name="ga_property"
								   value="<?php echo esc_attr( get_option( 'ga_property' ) ); ?>"/></td>
					</tr>

				</table>
                
                <h2>Google Tag Manager</h2>
                <p><?php _e('If you are using the Google Tag Manager, enter your data in this area', 'etwp'); ?></p>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><? _e( 'Google Tag Manager ID', 'etwp' ); ?></th>
                        <td><input type="text" name="gtag_property"
                                   value="<?php echo esc_attr( get_option( 'gtag_property' ) ); ?>"/></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php _e( esc_attr('Please add this snippet to your very first row after the opening <body> Tag'), 'etwp'); ?></th>
                        <td>
                            <textarea readonly type="text" name="gtag_snippet">
                                <?php echo esc_textarea('<!-- Google Tag Manager (noscript) -->
                                <noscript>
                                    <iframe src="https://www.googletagmanager.com/ns.html?id=<?php esc_attr_e( get_option(\'gtag_property\') ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
                                </noscript>
                                <!-- End Google Tag Manager (noscript) -->');
                                ?>
                            </textarea>
                        </td>
                    </tr>
                    
                </table>

				<?php submit_button(); ?>

			</form>
		</div>
		<?php
	}
}

/**
 * Initialize the class and check for other instances
 * @return EU_TRACKING_WP class
 */

function eu_tracking_wp() {

	// globals
	global $eu_tracking_wp;

	// initialize
	if ( ! isset( $eu_tracking_wp ) ) {
		$eu_tracking_wp = new EU_TRACKING_WP();
		$eu_tracking_wp->initialize();
	}

	// return
	return $eu_tracking_wp;
}

// initialize this class
eu_tracking_wp();

}
