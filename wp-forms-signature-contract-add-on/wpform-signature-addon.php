<?php
/**
 * @package   	      WP Forms Signature Contract Add-on
 * @contributors      Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me), Arafat Rahman (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WPForms Signature Contract Add-on by ApproveMe.com
 * Plugin URI:        http://aprv.me/2lIyMBx
 * Description:       This add-on makes it possible to automatically email a WP E-Signature contract (or redirect a user to a contract) after the user has successfully submitted a WPForms. You can also insert data from the submitted WPForms into the WP E-Signature contract.
 * Version:           1.8.1
 * Author:            ApproveMe.com
 * Author URI:        http://aprv.me/wpForms
 * Text Domain:       esig-wpform
 * Domain Path:       /languages
 * License/Terms & Conditions: http://www.approveme.com/terms-conditions/
 * Privacy Policy: http://www.approveme.com/privacy-policy/
 * License:     GPLv2+
 * Text Domain: wpform-wpesignature
 * Domain Path: /languages
 */

/**
 * Define constants
 */
define( 'WPFORM_WPESIGNATURE_VER', '1.8.1' );
define( 'WPFORM_WPESIGNATURE_URL',     plugin_dir_url( __FILE__ ) );
define( 'WPFORM_WPESIGNATURE_PATH',    dirname( __FILE__ ) . '/' );
define( 'WPFORM_WPESIGNATURE_CORE',    dirname( __FILE__ )  );

/**
 * Default initialization for the plugin:
 * - Registers the default textdomain.
 */


/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-wpform-function.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-wpform-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-wpform.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-wpf-filters.php' );


/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
 
register_activation_hook( __FILE__, array( 'ESIG_WPFORM', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ESIG_WPFORM', 'deactivate' ) );

require_once( plugin_dir_path( __FILE__ ) . 'admin/about/autoload.php' );


//if (is_admin()) {
         
    require_once( plugin_dir_path( __FILE__ ) . 'admin/esig-wpform-admin.php' );
    add_action( 'plugins_loaded', array( 'ESIG_WPFORM_Admin', 'get_instance' ) );
    add_action( 'plugins_loaded', array( 'esigWpFormFilters', 'instance' ) );
    require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/esig-wpform-document-view.php' );
    
require_once( plugin_dir_path( __FILE__ ) . 'admin/rating-widget/esign-rating-widget.php' );
add_action( 'plugins_loaded', array( 'esignRatingWidgetWpForm', 'get_instance' ) );
    
