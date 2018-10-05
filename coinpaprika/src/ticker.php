<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coinpaprika_Ticker extends WP_Widget {
			/**
			 * Register widget with WordPress.
			 */
			public function __construct() {
				parent::__construct( 'coinpaprika-ticker', esc_html__( 'Coin Ticker (by Coinpaprika)', 'coinpaprika' ), array( 'description' => esc_html__( 'Use this widget to display most important metrics for one selected cryptocurrency', 'coinpaprika' ) ) );
				add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles'));
			}

			static $MODULE_PRICE = 'price';
			static $MODULE_MARKET = 'market_details';
			static $MODULE_CHART = 'chart';

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

				if ( empty( $instance['coin_id'] ) ) {
					echo esc_html__( 'Configure widget settings', 'coinpaprika' );
				} else {
					$night_mode = !empty( $instance['style'] ) && $instance['style'] == 'night';
					$update_interval = ! empty( $instance['update_interval'] ) ? $instance['update_interval'] : false;
					$display_currency = !empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;

					$modules = !empty( $instance['widget_module'] ) ? $instance['widget_module'] : array(self::$MODULE_PRICE);

					$min_height = 0;
					if ( in_array(self::$MODULE_PRICE, $modules) ) {
						$min_height += 116;
					}

					if ( in_array(self::$MODULE_MARKET, $modules) ) {
						$min_height += 100;
					}

					if ( in_array(self::$MODULE_CHART, $modules) ) {
						$min_height += 315;
					}

					echo '<div class="coinpaprika-currency-widget wordpress' . ($night_mode ? ' cp-widget__night-mode' : '') . '" data-currency="' . $instance['coin_id'] . '" data-modules=\'["' . implode('", "', $modules) . '"]\' data-language="' . substr(get_locale(), 0, 2) .'" ' . ($update_interval ? 'data-update-active="true" data-update-timeout="' . $update_interval . '"' : 'data-update-active="false"') . ' data-primary-currency="' . $display_currency . '" style="min-height: ' . $min_height . 'px"></div>';
			 }

				echo $args['after_widget'];
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
			$display_currency = ! empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;
			$style = ! empty( $instance['style'] ) ? $instance['style'] : 'day';
			$modules = !empty( $instance['widget_module'] ) ? $instance['widget_module'] : array(self::$MODULE_PRICE);
			$update_interval = ! empty( $instance['update_interval'] ) ? $instance['update_interval'] : false;

			$api = new Coinpaprika_API();
			$coins = $api->all_coins();
			$display_currencies = $api->display_currencies();
			$versions = array('standard', 'extended');
			$updates = array(false => __('No interval', 'coinpaprika'), '30s' => __('30 seconds', 'coinpaprika'), '1m' => __('1 minute', 'coinpaprika'), '5m' => __('5 minutes', 'coinpaprika'), '10m' => __('10 minutes', 'coinpaprika'), '30m' => __('30 minutes', 'coinpaprika'));

			$coin_id = ! empty( $instance['coin_id'] ) ? $instance['coin_id'] : $coins[0]->id;
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
				<label for="<?php echo esc_attr( $this->get_field_id( 'widget_module' ) ); ?>">
					<?php esc_html_e( 'Widget modules:', 'coinpaprika' ); ?>
				</label><br/>
				<input
						type="checkbox"
						<?php checked( true, in_array(self::$MODULE_PRICE, $modules) ); ?>
						id="<?php echo $this->get_field_id( 'widget_module' ); ?>"
						name="<?php echo $this->get_field_name('widget_module[]'); ?>"
						value="<?php echo self::$MODULE_PRICE; ?>"
						disabled
				/>
				<label for="<?php echo $this->get_field_id( 'widget_module' ); ?>"><?php echo esc_html__( 'Price', 'coinpaprika' ); ?></label>

				<input
						type="checkbox"
						<?php checked( true, in_array(self::$MODULE_MARKET, $modules) ); ?>
						id="<?php echo $this->get_field_id( 'widget_module' ); ?>"
						name="<?php echo $this->get_field_name('widget_module[]'); ?>"
						value="<?php echo self::$MODULE_MARKET; ?>"
				/>
				<label for="<?php echo $this->get_field_id( 'widget_module' ); ?>"><?php echo esc_html__( 'Markets Metrics', 'coinpaprika' ); ?></label>

				<input
						type="checkbox"
						<?php checked( true, in_array(self::$MODULE_CHART, $modules) ); ?>
						id="<?php echo $this->get_field_id( 'widget_module' ); ?>"
						name="<?php echo $this->get_field_name('widget_module[]'); ?>"
						value="<?php echo self::$MODULE_CHART; ?>"
				/>
				<label for="<?php echo $this->get_field_id( 'widget_module' ); ?>"><?php echo esc_html__( 'Price Chart', 'coinpaprika' ); ?></label>

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
				<label for="<?php echo esc_attr( $this->get_field_id( 'update_interval' ) ); ?>">
					<?php esc_html_e( 'Update interval:', 'coinpaprika' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'update_interval' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'update_interval' ) ); ?>" class="widefat">
					<?php foreach ( $updates as $key => $name ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $update_interval, $key ); ?>>
							<?php echo esc_html( $name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
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
			$instance['widget_module'] = ( ! empty( $new_instance['widget_module'] ) ) ? array_merge(array(self::$MODULE_PRICE), $new_instance['widget_module']) : array(self::$MODULE_PRICE);
			$instance['update_interval'] = ( ! empty( $new_instance['update_interval'] ) ) ? sanitize_text_field( $new_instance['update_interval'] ) : '';
			return $instance;
		}

		public function enqueue_styles() {
			if ( !is_active_widget( false, false, $this->id_base, true ) ) {
				return;
			}

			wp_enqueue_script('coinpaprika-ticker', 'https://unpkg.com/@coinpaprika/widget-currency/dist/widget.min.js', null, null, true);
		}
}
