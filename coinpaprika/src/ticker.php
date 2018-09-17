<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coinpaprika_Ticker extends WP_Widget {
			/**
			 * Register widget with WordPress.
			 */
			public function __construct() {
				parent::__construct( 'coinpaprika-ticker', esc_html__( 'Coinpaprika ticker', 'coinpaprika' ), array( 'description' => esc_html__( 'Use this widget to display most important metrics for one selected cryptocurrency', 'coinpaprika' ) ) );
				add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles'));
			}

			/**
			 * Front-end display of widget.
			 *
			 * @see WP_Widget::widget()
			 *
			 * @param array $args     Widget arguments.
			 * @param array $instance Saved values from database.
			 */
			public function widget( $args, $instance ) {
				echo $args['before_widget'];
				if ( ! empty( $instance['title'] ) ) {
					echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
				}

				if ( empty( $instance['coin_id']) ) {
					echo esc_html__( 'Configure widget settings', 'coinpaprika' );
				} else {
					$api = new Coinpaprika_API();
					$display_currency = !empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;
					$extended = !empty( $instance['version'] ) && $instance['version'] == 'extended';
					$night_mode = !empty( $instance['style'] ) && $instance['style'] == 'night';
					$widget_data = $api->widget_data($instance['coin_id'], $display_currency);
					echo '<div class="coinpaprika-currency-widget' . ($night_mode ? ' cp-widget__night-mode' : '') . ' cp-lang-' . get_locale() .'">' .
						'<div class="cp-widget__header">' .
								'<div class="cp-widget__img cp-widget__img-' . $instance['coin_id'] . '">' .
									'<img src="https://coinpaprika.com/coin/' . $instance['coin_id'] . '/logo.png" />' .
								'</div>' .
							'<div class="cp-widget__main">' .
								( !empty( $widget_data ) ? $this->widget_content( $widget_data ) : $this->widget_no_data() ) .
							'</div>' .
						'</div>' .
						($extended ? $this->widget_content_extended( $widget_data ) : '') .
				 '</div>';
			 }
				echo $args['after_widget'];
			}

			private function widget_no_data() {
				return '<div class="cp-widget__main-no-data cp-translation translation_message">' . esc_html__( 'Data is currently unavailable', 'coinpaprika' ) . '</div>';
			}

			private function widget_content($widget_data) {
					return '<h3><a href="' . $this->coin_link($widget_data->id) . '">' .
					'<span class="nameTicker">' . $widget_data->name . '</span>' .
					'<span class="symbolTicker">' . $widget_data->symbol . '</span>' .
					'</a></h3>' .
					'<strong>' .
					'<span class="priceTicker parseNumber">' . $this->parse_number($widget_data->price) .  '</span> ' .
					'<span class="primaryCurrency">' . $widget_data->quote_symbol . ' </span>' .
					'<span class="price_change_24hTicker cp-widget__rank cp-widget__rank-' . (($widget_data->price_change_24h > 0) ? "up" : (($widget_data->price_change_24h < 0) ? "down" : "neutral")) . '">(' . round($widget_data->price_change_24h, 2) . '%)</span>' .
					'</strong>' .
					'<span class="cp-widget__rank-label"><span class="cp-translation translation_rank">' . esc_html__( 'Rank', 'coinpaprika' ) . '</span> <span class="rankTicker">' . $widget_data->rank .'</span></span>';
			}

			private function widget_content_extended($widget_data) {
				return '<div class="cp-widget__details">' . $this->widget_content_ath($widget_data) . $this->widget_content_volume($widget_data) . $this->widget_market_cap($widget_data) . '</div>';
			}

			private function widget_content_ath($widget_data) {
				return $this->widget_value_change(esc_html__( 'ATH', 'coinpaprika' ), $widget_data->price_ath, $widget_data->percent_from_price_ath);
			}

			private function widget_content_volume($widget_data) {
					return $this->widget_value_change(esc_html__( 'Volume 24h', 'coinpaprika' ), $widget_data->volume_24h, $widget_data->volume_24h_change_24h);
			}

			private function widget_market_cap($widget_data) {
					return $this->widget_value_change(esc_html__( 'Market cap', 'coinpaprika' ), $widget_data->market_cap, $widget_data->market_cap_change_24h);
			}

			private function widget_value_change($label, $value, $percent_change) {
				return '<div>' .
					'<small>' . $label . '</small>' .
					'<div>' .
					'<span>' . $this->parse_number($value) . '</span>' .
					'<span class="symbolTicker showDetailsCurrency"></span>' .
					'</div>' .
					'<span class="cp-widget__rank cp-widget__rank-' . (($percent_change > 0) ? "up" : (($percent_change < 0) ? "down" : "neutral")) . '">' . round($percent_change, 2) . '%</span>' .
					'</div>';
			}

			private function parse_number($number) {
				if ($number > 100000){
					$formatted = (string)round($number, 0);
					$parameter = 'K';
					$shorted = substr($formatted, 0, -1);

					if ($number > 1000000000){
						$shorted = substr($formatted, 0, -7);
						$parameter = 'B';
					} else if ($number > 1000000){
						$shorted = substr($formatted, 0, -4);
						$parameter = 'M';
					}

					$natural = substr($shorted, 0, -2);
					$decimal = substr($shorted, - 2);
					return number_format($natural . '.' . $decimal, 2, '.', ' ') . ' ' . $parameter;
				} else {
					$isDecimal = ($number % 1) > 0;
					if ($isDecimal){
						$precision = 2;
						if ($number < 1){
							$precision = 8;
						} else if ($number < 10){
							$precision = 6;
						} else if ($number < 1000){
							$precision = 4;
						}
						return number_format($number, $precision, '.', ' ');
					} else {
						return number_format($number, 2, '.', ' ');
					}
				}
			}

			private function coin_link($id) {
				return 'https://coinpaprika.com/coin/' . $id . '/?utm_source=widget&utm_medium=wordpress&utm_campaign=trends';
			}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$coin_id = ! empty( $instance['coin_id'] ) ? $instance['coin_id'] : null;
			$display_currency = ! empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;
			$style = ! empty( $instance['style'] ) ? $instance['style'] : 'day';
			$version = ! empty( $instance['version'] ) ? $instance['version'] : 'standard';

			$api = new Coinpaprika_API();
			$coins = $api->all_coins();
			$display_currencies = $api->display_currencies();
			$versions = array('standard', 'extended');
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'coinpaprika' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'coin_id' ) ); ?>">
					<?php esc_html_e( 'Cryptocurrency:', 'coinpaprika' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'coin_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'coin_id' ) ); ?>" class="widefat">
					<?php foreach ( $coins as $coin ) : ?>
						<option value="<?php echo esc_attr( $coin->id ); ?>" <?php selected( $coin_id, $coin->id ); ?>>
							<?php echo esc_html( $coin->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'display_currency' ) ); ?>">
					<?php esc_html_e( 'Display in:', 'coinpaprika' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'display_currency' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_currency' ) ); ?>" class="widefat">
					<?php foreach ( $display_currencies as $code => $name ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $display_currency, $code ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>">
					<?php esc_html_e( 'Widget style:', 'coinpaprika' ); ?>
				</label><br/>
				<input
						type="radio"
						<?php checked( 'day', $style ); ?>
						id="<?php echo $this->get_field_id( 'style' ); ?>"
						name="<?php echo $this->get_field_name('style'); ?>"
						value="day"
				/>
				<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php echo esc_html__( 'day', 'coinpaprika' ); ?></label>
				<input
						type="radio"
						<?php checked( 'night', $style ); ?>
						id="<?php echo $this->get_field_id( 'style' ); ?>"
						name="<?php echo $this->get_field_name('style'); ?>"
						value="night"
				/>
				<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php echo esc_html__( 'night', 'coinpaprika' ); ?></label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'version' ) ); ?>">
					<?php esc_html_e( 'Widget version:', 'coinpaprika' ); ?>
				</label><br/>
				<input
						type="radio"
						<?php checked( 'standard', $version ); ?>
						id="<?php echo $this->get_field_id( 'version' ); ?>"
						name="<?php echo $this->get_field_name('version'); ?>"
						value="standard"
				/>
				<label for="<?php echo $this->get_field_id( 'version' ); ?>"><?php echo esc_html__( 'standard', 'coinpaprika' ); ?></label>
				<input
						type="radio"
						<?php checked( 'extended', $version ); ?>
						id="<?php echo $this->get_field_id( 'version' ); ?>"
						name="<?php echo $this->get_field_name('version'); ?>"
						value="extended"
				/>
				<label for="<?php echo $this->get_field_id( 'version' ); ?>"><?php echo esc_html__( 'extended', 'coinpaprika' ); ?></label>
			</p>
			<?php
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
			$instance['coin_id'] = ( ! empty( $new_instance['coin_id'] ) ) ? sanitize_text_field( $new_instance['coin_id'] ) : '';
			$instance['display_currency'] = ( ! empty( $new_instance['display_currency'] ) ) ? sanitize_text_field( $new_instance['display_currency'] ) : '';
			$instance['style'] = ( ! empty( $new_instance['style'] ) ) ? sanitize_text_field( $new_instance['style'] ) : '';
			$instance['version'] = ( ! empty( $new_instance['version'] ) ) ? sanitize_text_field( $new_instance['version'] ) : '';
			return $instance;
		}

		public function enqueue_styles() {
			if ( !is_active_widget( false, false, $this->id_base, true ) ) {
				return;
			}

			wp_register_style('coinpaprika-ticker', plugins_url('css/widget.min.css', dirname(__FILE__) ), null, COINPAPRIKA_PLUGIN_VERSION);
			wp_enqueue_style('coinpaprika-ticker');
		}
}
