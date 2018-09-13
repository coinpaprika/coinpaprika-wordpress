<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coinpaprika_API {
	private $BASE_URL = 'https://api.coinpaprika.com/v1/';

	public function all_coins() {
		return $this->load('coins', 30 * MINUTE_IN_SECONDS);
	}

	public function widget_data($coin, $quote = 'usd') {
		if ( !isset( $this->display_currencies()[$quote] ) ) {
			$quote = 'usd';
		}

		return $this->load('widget/' . $coin . '?quote=' . $quote, 5 * MINUTE_IN_SECONDS);
	}

	public function display_currencies() {
		return array('usd' => 'USD', 'btc' => 'BTC', 'eth' => 'ETH');
	}

	private function load($path, $ttl) {
		$key = 'coinpaprika_' . $path;

		$json = get_transient( $key );

		$response = wp_safe_remote_get($this->BASE_URL . $path);

		if ( ! is_wp_error( $response ) && ! empty( $response ) && isset( $response['response']['code'] ) && 200 === $response['response']['code'] ) {
				$json = json_decode( $response['body'] );
				set_transient( $key, $json, $ttl );
				return $json;
		}
	}
}
