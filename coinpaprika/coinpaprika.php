<?php
/*
Plugin Name: Coinpaprika
Plugin URI: https://github.com/coinpaprika/coinpaprika-wordpress
Description: Crypto capitalization WordPress plugin featuring: easy to configure crypto price widgets & shortcodes with all available cryptocurrencies!
Version: 1.0
Author: Coinpaprika
Author URI: https://coinpaprika.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: coinpaprika
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'src' . DIRECTORY_SEPARATOR . 'api.php';
require_once 'src' . DIRECTORY_SEPARATOR . 'ticker.php';
require_once 'src' . DIRECTORY_SEPARATOR . 'shortcodes.php';

add_action( 'widgets_init', 'coinpaprika_widgets' );
add_action( 'plugins_loaded', 'coinpaprika_load_textdomain' );

add_shortcode( 'coinpaprika', array('Coinpaprika_Shortcodes', 'handle') );
add_action('wp_enqueue_scripts', array('Coinpaprika_Shortcodes', 'enqueue_styles'));

function coinpaprika_widgets() {
		register_widget( 'Coinpaprika_Ticker' );
}

function coinpaprika_load_textdomain() {
  load_plugin_textdomain( 'coinpaprika', false, 'coinpaprika/languages' );
}
