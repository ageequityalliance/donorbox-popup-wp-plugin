<?php
/**
 * Plugin Name: Donorbox popup plugin for WordPress
 * Plugin URI: https://github.com/ageequityalliance/donorbox-popup-wp-plugin
 * Description: Adds a Donorbox popup on pageload
 * Version: 1.0.0
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
    $plugin_version = '1.0.0';
	$script_key = 'donorbox-popup-js';
	wp_enqueue_script(
		$script_key,
		plugins_url( '/donorbox-popup.js', __FILE__ ),
		array(),
		$plugin_version,
		true
	);
    $url_key = 'donorbox_popup_url';
    $days_key = 'donorbox_popup_days';
    $url = get_option($url_key, '');
    $days = get_option($days_key, 14);
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

function admin_page_content(): void {
    $url_key = 'donorbox_popup_url';
    $days_key = 'donorbox_popup_days';
    if ( check_admin_referer() ) {
        if ( array_key_exists($url_key, $_POST) ) {
            update_option($url_key, esc_url_raw( wp_unslash($_POST[ $url_key ])), true);
        }
        if ( array_key_exists($days_key, $_POST) ) {
            update_option($days_key, sanitize_text_field(wp_unslash($_POST[ $days_key ])), true);
        }
    }
?>
<h1>Donorbox Popup plugin for WordPress</h1>
<p>This plugin allows website owners to include a full screen donorbox form on page load.</p>
<form method="post">
    <label for="<?php echo sanitize_key($url_key); ?>">Donorbox campaign url:</label>
    <input type="url" id="<?php echo sanitize_key($url_key); ?>" name="<?php echo sanitize_key($url_key); ?>" value="<?php echo esc_url(get_option($url_key, '')); ?>" placeholder="https://donorbox.org/my-campaign"><br><br>
    <p>A visitor to your site will see the form on first page load. When they close the form, it will not reappear for the selected number of days:</p>
    <label for="<?php echo sanitize_key($days_key); ?>">Days:</label>
    <input type="number" id="<?php echo sanitize_key($days_key); ?>" name="<?php echo sanitize_key($days_key); ?>" value="<?php echo sanitize_key(get_option($days_key, 14)); ?>" min="1" step="1"><br><br>
    <input type="submit" value="Submit">
    <?php wp_nonce_field(); ?>
</form>
<?php
}

function add_admin_page_content(): void {    
    add_menu_page('Donorbox Popup', 'Donorbox Popup', 'manage_options', 'donorbox_popup', 'DonorboxPopup\admin_page_content', 'dashicons-wordpress'); 
}
add_action('admin_menu', 'DonorboxPopup\add_admin_page_content');