<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coinpaprika_Shortcodes {
  public static function handle( $atts ) {
		$metrics = array('price', 'volume24h', 'marketcap', 'ath');

		$attributes = shortcode_atts( array(
			'coin' => 'btc-bitcoin',
			'quote' => 'usd',
			'icon' => true,
			'metric' => $metrics[0],
			'change' => true
		), $atts );

		$attributes['icon'] = filter_var($attributes['icon'], FILTER_VALIDATE_BOOLEAN);
		$attributes['change'] = filter_var($attributes['change'], FILTER_VALIDATE_BOOLEAN);

		if ( !in_array($attributes['metric'], $metrics) ) {
			$attributes['metric'] = $metrics[0];
		}

		$api = new Coinpaprika_API();
		$widget_data = $api->widget_data($attributes['coin'], $attributes['quote']);

		if ( empty( $widget_data ) ) {
			return '';
		}

		$html = '<a href="https://coinpaprika.com/coin/' . $widget_data->id . '" class="coinpaprika_shortcode">';

		if ( $attributes['icon'] ) {
			$html .= '<img src="https://coinpaprika.com/coin/' . $widget_data->id . '/logo-thumb.png"/> ';
		}

		$value = null;
		$change = null;

		switch ($attributes['metric']) {
			case 'price':
				if ( isset( $widget_data->price ) && isset( $widget_data->price_change_24h ) ) {
					$value = $widget_data->price;
					$change = $widget_data->price_change_24h;
			  }
				break;
			case 'volume24h':
				if ( isset( $widget_data->volume_24h ) && isset( $widget_data->volume_24h_change_24h ) ) {
					$value = $widget_data->volume_24h;
					$change = $widget_data->volume_24h_change_24h;
				}
				break;
			case 'marketcap':
				if ( isset( $widget_data->market_cap ) && isset( $widget_data->market_cap_change_24h ) ) {
					$value = $widget_data->market_cap;
					$change = $widget_data->market_cap_change_24h;
				}
				break;
			case 'ath':
				if ( isset( $widget_data->price_ath ) && isset( $widget_data->percent_from_price_ath ) ) {
					$value = $widget_data->price_ath;
					$change = $widget_data->percent_from_price_ath;
				}
				break;
		}

		if ( is_null( $value ) || empty( $widget_data->quote_symbol ) ) {
			return '';
		}

		$html .= self::format_price($value, $attributes['quote'] == 'usd' ? 'fiat' : 'default') . ' ' . $widget_data->quote_symbol;

		if ( $attributes['change'] && !is_null( $change ) ) {
      $html .= ' <span class="' . (($change > 0) ? "up" : (($change < 0) ? "down" : "")) . '">' . round($change, 2) . '%</span>';
		}

    $html .= '</a>';

		return $html;
	}

  private static function format_price($price, $type = "default") {
        $decimals = 8;

        if ($type == "fiat") {
            $decimals = 6;
            if ($price > 1.2) {
                $decimals = 2;
            } else if ($price < 0.00001) {
                $decimals = 8;
            }
        }

        return rtrim(number_format($price, $decimals, '.', ' '), '0.');
  }

  public static function enqueue_styles() {
    global $post;

    if ( !is_a( $post, 'WP_Post' ) || !has_shortcode( $post->post_content, 'coinpaprika') ) {
      return;
    }

    wp_register_style('coinpaprika-shortcodes', plugins_url('css/shortcodes.css', dirname(__FILE__) ));
    wp_enqueue_style('coinpaprika-shortcodes');
  }
}
