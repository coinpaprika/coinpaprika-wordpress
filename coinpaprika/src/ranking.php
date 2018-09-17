<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Coinpaprika_Ranking extends WP_Widget {
			/**
			 * Register widget with WordPress.
			 */
			public function __construct() {
				parent::__construct( 'coinpaprika-ranking', esc_html__( 'Coinpaprika ranking', 'coinpaprika' ), array( 'description' => esc_html__( 'Use this widget to display ranking for selected cryptocurrencies', 'coinpaprika' ) ) );
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

				if ( empty( $instance['coin_ids']) ) {
					echo esc_html__( 'Configure widget settings', 'coinpaprika' );
				} else {
					$night_mode = !empty( $instance['style'] ) && $instance['style'] == 'night';
					$update_interval = ! empty( $instance['update_interval'] ) ? $instance['update_interval'] : false;
					$display_currency = !empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;
					$coins = $instance['coin_ids'];
					$min_height = 62 + 36 * count($coins);

					echo '<div class="coinpaprika-market-widget wordpress' . ($night_mode ? ' cp-widget__night-mode' : '') . '" data-currency-list=\'["' . implode('", "', $coins) . '"]\' data-language="' . substr(get_locale(), 0, 2) .'" ' . ($update_interval ? 'data-update-active="true" data-update-timeout="' . $update_interval . '"' : 'data-update-active="false"') . ' data-primary-currency="' . $display_currency . '" style="min-height: ' . $min_height . 'px"></div>';
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
			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';;
			$coin_ids = ! empty( $instance['coin_ids'] ) ? $instance['coin_ids'] : array();
			$display_currency = ! empty( $instance['display_currency'] ) ? $instance['display_currency'] : null;
			$style = ! empty( $instance['style'] ) ? $instance['style'] : 'day';
			$version = ! empty( $instance['version'] ) ? $instance['version'] : 'standard';
			$update_interval = ! empty( $instance['update_interval'] ) ? $instance['update_interval'] : false;

			$api = new Coinpaprika_API();
			$coins = $api->all_coins();
			$display_currencies = $api->display_currencies();
			$versions = array('standard', 'extended');
			$updates = array(false => __('No interval', 'coinpaprika'), '30s' => __('30 seconds', 'coinpaprika'), '1m' => __('1 minute', 'coinpaprika'), '5m' => __('5 minutes', 'coinpaprika'), '10m' => __('10 minutes', 'coinpaprika'), '30m' => __('30 minutes', 'coinpaprika'));
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'coinpaprika' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'coin_ids' ) ); ?>">
					<?php esc_html_e( 'Cryptocurrency:', 'coinpaprika' ); ?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'coin_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'coin_ids[]' ) ); ?>" class="widefat" multiple="true" size="10">
					<?php foreach ( $coins as $coin ) : ?>
						<option value="<?php echo esc_attr( $coin->id ); ?>" <?php selected( true, in_array($coin->id, $coin_ids) ); ?>>
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
			$instance['coin_ids'] = ( ! empty( $new_instance['coin_ids'] ) ) ? $new_instance['coin_ids'] : '';
			$instance['display_currency'] = ( ! empty( $new_instance['display_currency'] ) ) ? sanitize_text_field( $new_instance['display_currency'] ) : '';
			$instance['style'] = ( ! empty( $new_instance['style'] ) ) ? sanitize_text_field( $new_instance['style'] ) : '';
			$instance['update_interval'] = ( ! empty( $new_instance['update_interval'] ) ) ? sanitize_text_field( $new_instance['update_interval'] ) : '';
			return $instance;
		}

		public function enqueue_styles() {
			if ( !is_active_widget( false, false, $this->id_base, true ) ) {
				return;
			}

			wp_enqueue_script('coinpaprika-ranking', 'https://cdn.jsdelivr.net/npm/@coinpaprika/widget-market@1.0.3/dist/widget.min.js', null, null, true);
		}
}
