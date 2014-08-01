<?php
/*
Plugin Name: WooCommerce XMPP Integration
Plugin URI: http://proficomp.ru
Description: Integrates <a href="http://www.woothemes.com/woocommerce" target="_blank" >WooCommerce</a> with the XMPP client for Android and iOS and Desktop.
Version: 1.0.0
Author: andry.chuyko@gmail.com
Author URI: http://proficomp.ru
*/
/* 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * Required functions
 */
 
if ( class_exists( 'WC_XMPP' ) ) return;

/**
 * Localisation
 */
load_plugin_textdomain( 'wc_xmpp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );


define( 'WC_XMPP_DIR', plugin_dir_path(__FILE__) );

/**
 * Plugin activation check
 */
function wc_xmpp_activation_check() {

	// Verify WooCommerce is installed and active
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() )
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	
	if ( ! ( in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) ) ) {
        deactivate_plugins( basename( __FILE__ ) );
        wp_die( "This plugin requires WooCommerce to be installed and active." );
	}


	// verify that SimpleXML library is available	
	if ( ! function_exists( 'simplexml_load_string' ) ) {
        deactivate_plugins( basename( __FILE__ ) );
        wp_die( "Sorry, but you can't run this plugin, it requires the SimpleXML library installed on your server/hosting to function." );
	}
}

register_activation_hook( __FILE__, 'wc_xmpp_activation_check' );


/**
 * wc_xmpp_init function.
 *
 * @access public
 * @return void
 */
function wc_xmpp_init() {
	include_once( 'classes/class-wc-xmpp.php' );
}

add_action( 'woocommerce_integrations_init', 'wc_xmpp_init' );


function add_xmpp_integration( $integrations ) {
	$integrations[] = 'WC_XMPP';
	return $integrations;
}

add_filter('woocommerce_integrations', 'add_xmpp_integration' );


/**
 * Plugin page links
 */
function wc_xmpp_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration' ) . '">' . __( 'Settings', 'wc_xmpp' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_xmpp_plugin_links' );


