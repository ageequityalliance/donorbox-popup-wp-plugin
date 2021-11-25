<?php
/**
 * Plugin Name: Donorbox popup plugin for WordPress
 * Plugin URI: https://github.com/ageequityalliance/donorbox-popup-wp-plugin
 * Description: Adds a Donorbox popup on pageload
 * Version: 1.0.2
 * Author: Nate Gay
 * Author URI: https://github.com/ageequityalliance
 * License: GPL3
 *
 * @category WordPress_Plugin
 * @package  donorbox-popup-for-wp
 * @author   Nate Gay <nate.gay@ageequityalliance.org>
 * @license  GPL3
 * @link     https://github.com/ageequityalliance/donorbox-popup-wp-plugin
 */

namespace DonorboxPopup;

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue Donorbox popup scripts
 *
 * @return void
 */
function enqueue_scripts(): void {
	$plugin_version = '1.0.2';
	$script_key     = 'donorbox-popup-js';
	wp_enqueue_script(
		$script_key,
		plugins_url( '/donorbox-popup.js', __FILE__ ),
		array(),
		$plugin_version,
		true
	);
	$url_key  = 'donorbox_popup_url';
	$days_key = 'donorbox_popup_days';
	$url      = get_option( $url_key, '' );
	$days     = get_option( $days_key, 14 );
	if ( '' !== $url ) {
		$url .= '?modal=true';
	}
	wp_localize_script(
		$script_key,
		'donorboxPopup',
		array(
			'url'                  => $url,
			'daysUntilNextShowing' => $days,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'DonorboxPopup\enqueue_scripts' );

/**
 * Admin Page
 *
 * @return void
 */
function admin_page(): void {
	$url_key  = 'donorbox_popup_url';
	$days_key = 'donorbox_popup_days';
	if ( array_key_exists( $url_key, $_POST ) ) {
		if ( check_admin_referer() ) {
			update_option( $url_key, strip_query_params( esc_url_raw( wp_unslash( $_POST[ $url_key ] ) ) ), true );
		}
	}
	if ( array_key_exists( $days_key, $_POST ) ) {
		if ( check_admin_referer() ) {
			update_option( $days_key, sanitize_text_field( wp_unslash( $_POST[ $days_key ] ) ), true );
		}
	}
	?>
<h1>Donorbox Popup plugin for WordPress</h1>
<p>This plugin allows website owners to include a full screen donorbox form on page load.</p>
<form method="post" style="padding-right:10px;">
	<p>
	<label for="<?php echo sanitize_key( $url_key ); ?>" style="font-weight:600;">Donorbox campaign url:</label>
	<input 
		type="url"
		id="<?php echo sanitize_key( $url_key ); ?>"
		name="<?php echo sanitize_key( $url_key ); ?>"
		value="<?php echo esc_url( get_option( $url_key, '' ) ); ?>"
		placeholder="https://donorbox.org/my-campaign" 
		style="width:100%;"></p>
	<p>A visitor to your site will see the form on first page load. When they close the form, it will not reappear for the selected number of days.</p>
	<p>
	<label for="<?php echo sanitize_key( $days_key ); ?>" style="font-weight:600;">Days until next popup:</label>
	<input
		type="number"
		id="<?php echo sanitize_key( $days_key ); ?>"
		name="<?php echo sanitize_key( $days_key ); ?>"
		value="<?php echo sanitize_key( get_option( $days_key, 14 ) ); ?>"
		min="1"
		step="1"></p>
	<input type="submit" value="Submit">
	<?php wp_nonce_field(); ?>
</form>
	<?php
}

/**
 * Stip Query Params From URL
 *
 * @param string $url Some URL
 * @return string
 */
function strip_query_params( string $url ): string {
	return substr( $url, 0, strpos( $url, '?' ) ) ?: $url;
}

/**
 * Admin Menu
 *
 * @return void
 */
function admin_menu(): void {
	add_menu_page( 'Donorbox Popup', 'Donorbox Popup', 'manage_options', 'donorbox_popup', 'DonorboxPopup\admin_page', 'dashicons-money-alt' );
}
add_action( 'admin_menu', 'DonorboxPopup\admin_menu' );
