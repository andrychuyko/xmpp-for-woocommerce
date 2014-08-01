<?php
/**
 * WC_XMPP class.
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
class WC_XMPP extends WC_Integration {

	/**
	 * __consturct()
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->id                 = 'xmpp';
		$this->method_title       = __( 'XMPP', 'wc_xmpp' );
		$this->method_description = __( 'XMPP makes it easy to send real-time notifications to your Android and iOS devices.', 'wc_xmpp' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables
		$this->enabled 		= isset( $this->settings['enabled'] ) && $this->settings['enabled'] == 'yes' ? true : false;
		$this->user          	= isset( $this->settings['user'] ) ? $this->settings['user'] : '';
		$this->pass          	= isset( $this->settings['pass'] ) ? $this->settings['pass'] : '';
		$this->host            	= isset( $this->settings['host'] ) ? $this->settings['host'] : '';
		$this->port            	= isset( $this->settings['port'] ) ? $this->settings['port'] : '';
		$this->domain           = isset( $this->settings['domain'] ) ? $this->settings['domain'] : '';
		$this->rec            	= isset( $this->settings['rec'] ) ? $this->settings['rec'] : '';
		$this->priority         = isset( $this->settings['priority'] ) ? $this->settings['priority'] : '';
		$this->debug            = isset( $this->settings['debug'] ) && $this->settings['debug'] == 'yes' ? true : false;

		// Notices
		$this->notify_new_order  = isset( $this->settings['notify_new_order'] ) && $this->settings['notify_new_order'] == 'yes' ? true : false;
		$this->notify_free_order = isset( $this->settings['notify_free_order'] ) && $this->settings['notify_free_order'] == 'yes' ? true : false;
		$this->notify_backorder  = isset( $this->settings['notify_backorder'] ) && $this->settings['notify_backorder'] == 'yes' ? true : false;
		$this->notify_no_stock   = isset( $this->settings['notify_no_stock'] )  && $this->settings['notify_no_stock'] == 'yes' ? true : false;
		$this->notify_low_stock  = isset( $this->settings['notify_low_stock'] ) && $this->settings['notify_low_stock'] == 'yes' ? true : false;

		// Actions
		add_action( 'woocommerce_update_options_integration_xmpp', array( &$this, 'process_admin_options') );
		add_action( 'init', array( $this, 'wc_xmpp_init' ), 10 );

		if ( $this->notify_new_order )
			add_action( 'woocommerce_thankyou', array( $this, 'notify_new_order' ) );
		if ( $this->notify_backorder )
			add_action( 'woocommerce_product_on_backorder', array( $this, 'notify_backorder' ) );
		if ( $this->notify_no_stock )
			add_action( 'woocommerce_notify_no_stock', array( $this, 'notify_no_stock' ) );
		if ( $this->notify_low_stock )
			add_action( 'woocommerce_notify_low_stock', array( $this, 'notify_low_stock' ) );

	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		global $woocommerce;

		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'wc_xmpp' ),
				'label'       => __( 'Enable sending of notifications', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'user' => array(
				'title'       => __( 'User', 'wc_xmpp' ),
				'description' => __( '', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'pass' => array(
				'title'       => __( 'Password', 'wc_xmpp' ),
				'description' => __( '', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'host' => array(
				'title'       => __( 'Host', 'wc_xmpp' ),
				'description' => __( '', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'port' => array(
				'title'       => __( 'Port', 'wc_xmpp' ),
				'description' => __( '', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'domain' => array(
				'title'       => __( 'Domain', 'wc_xmpp' ),
				'description' => __( '', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'rec' => array(
				'title'       => __( 'Recipients', 'wc_xmpp' ),
				'description' => __( 'divider ;', 'wc_xmpp' ),
				'type'        => 'text',
				'default'     => '',
			),
			'debug' => array(
				'title'       => __( 'Debug', 'wc_xmpp' ),
				'description' => __( 'Enable debug logging', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'notifications' => array(
				'title'       => __( 'Notifications', 'wc_xmpp' ),
				'type'        => 'title',
			),
			'notify_new_order' => array(
				'title'       => __( 'New Order', 'wc_xmpp' ),
				'label'       => __( 'Send notification when a new order is received.', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'notify_free_order' => array(
				'title'       => __( 'Free Order', 'wc_xmpp' ),
				'label'       => __( 'Send notification when an order totals $0.', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'notify_backorder' => array(
				'title'       => __( 'Back Order', 'wc_xmpp' ),
				'label'       => __( 'Send notification when a product is back ordered.', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'notify_no_stock' => array(
				'title'       => __( 'No Stock', 'wc_xmpp' ),
				'label'       => __( 'Send notification when a product has no stock.', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'notify_low_stock' => array(
				'title'       => __( 'Low Stock', 'wc_xmpp' ),
				'label'       => __( 'Send notification when a product hits the low stock.', 'wc_xmpp' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			),
			'test_button' => array(
				'type'        => 'test_button',
			),

		);

	} // End init_form_fields()

	/**
	 * wc_xmpp_init
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function wc_xmpp_init() {

		if ( isset($_GET['wc_test']) && ($_GET['wc_test']==1)){
			$title   = __( 'Test Notification', 'wc_xmpp');
			$message = sprintf(__( 'This is a test notification from %s', 'wc_xmpp'), get_bloginfo('name'));
			$url     = get_admin_url();

			$this->send_notification( $title, $message, $url);

			wp_safe_redirect( get_admin_url() . 'admin.php?page=wc-settings&tab=integration&section=xmpp' );
		}
	}

	/**
	 * notify_new_order
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function notify_new_order( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );

		// Send notifications if order total is greater than $0
		// Or if free order notification is enabled
		if ( 0 < absint( $order->order_total ) || $this->notify_free_order ) {
			$order_items = "";
			$exclude = array("type", "item_meta", "tax_class", "product_id", "variation_id", "line_subtotal", "line_tax", "line_subtotal_tax");
			$replace = array("name" => "Товар", "qty" => "Количество", "line_total" => "На сумму", "pa_base" => "Основа", "pa_size" => "Размер", "pa_color" => "Цвет");
			foreach ($order->get_items() as $item_id => $id) {
				foreach ($id as $key => $value) {
					if (!in_array($key, $exclude)) {
						$order_items .= strtr($key, $replace).": ".urldecode($value)."\r\n";
					}
				}
				$order_items .= "\r\n";
			}
			$title   = sprintf( __( 'Заказ %d', 'wc_xmpp'), $order_id );
			$message = //$order->billing_first_name . " " . $order->billing_last_name."\r\n".
				   $order->get_formatted_shipping_address()."\r\n".
				   $order_items.
				   "Общая сумма: ".$order->order_total.$this->xmpp_get_currency_symbol();
			$url     = get_admin_url();

			$this->send_notification( $title, $message, $url );

			$this->add_log( __('Order items', 'wc_xmpp') .  "\n" . print_r($order,true) ); 
		}

	}

	/**
	 * notify_backorder
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function notify_backorder( $args ) {
		global $woocommerce;

		$product = $args['product'];
		$title   = sprintf( __( 'Product Backorder', 'wc_xmpp'), $order_id );
		$message = sprintf( __( 'Product (#%d %s) is on backorder.', 'wc_xmpp'), $product->id, $product->get_title() );
		$url     = get_admin_url();

		$this->send_notification( $title, $message, $url );

	}

	/**
	 * notify_no_stock
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function notify_no_stock( $product ) {
		global $woocommerce;

		$title   = __( 'Product Out of Stock', 'wc_xmpp');
		$message = sprintf( __( 'Product %s %s is now out of stock.', 'wc_xmpp'), $product->id, $product->get_title()  );
		$url     = get_admin_url();

		$this->send_notification( $title, $message, $url );

	}

	/**
	 * notify_low_stock
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function notify_low_stock( $product ) {
		global $woocommerce;

		// get order details
		$title   = __( 'Product Low Stock', 'wc_xmpp');
		$message = sprintf( __( 'Product %s %s now has low stock.', 'wc_xmpp'), $product->id, $product->get_title() );
		$url     = get_admin_url();

		$this->send_notification( $title, $message, $url );

	}

	/**
	 * send_notification
	 *
	 * Send notification when new order is received
	 *
	 * @access public
	 * @return void
	 */
	function send_notification( $title, $message, $url = '' ) {

		if ( ! class_exists( 'XMPP_Api' ) )
			include_once( 'xmpp.class.php' );

		 $webi_conf['user']=$this->user;
		 $webi_conf['pass']=$this->pass;
		 $webi_conf['host']=$this->host;
		 $webi_conf['port']=$this->port;
		 $webi_conf['domain']=$this->domain;
  
 
		 $webi_conf['logtxt']=false;
		 $webi_conf['log_file_name']="loggerxmpp.log";
		 $webi_conf['tls_off'] = 0;

		$xmpp = new XMPP($webi_conf);
		$xmpp->connect(); 

		$this->add_log( __('Sending: ', 'wc_xmpp') .
							"\nТема: ".  substr(get_bloginfo('wpurl'), 7, 999)." ". $title .
							"\nСообщение: ". $message);

		try {
			$rec = explode(';', $this->rec);
			for ($i = 0; $i < count($rec); $i++) {
				$this->add_log( __('Rec: ', 'wc_xmpp') . "\n" . $rec[$i] );
				$xmpp->sendMessage($rec[$i], substr(get_bloginfo('wpurl'), 7, 999)." ". $title ."\n". $message); 
			}

		} catch ( Exception $e ) {
			$this->add_log( sprintf(__('Error: Caught exception from send method: %s', 'wc_xmpp'), $e->getMessage() ) );
		}
		
	}

	/**
	 * generate_test_button_html()
	 *
	 * @access public
	 * @return void
	 */
	function generate_test_button_html() {
		ob_start();
		?>
		<tr valign="top" id="service_options">
			<th scope="row" class="titledesc"><?php _e( 'Send Test', 'wc_xmpp' ); ?></th>
			<td >
			<p><a href="<?php echo get_admin_url(); ?>admin.php?page=wc-settings&tab=integration&section=xmpp&wc_test=1" class="button" ><?php _e('Send Test Notification', 'wc_xmpp'); ?></a></p>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * add_log
	 *
	 * @access public
	 * @return void
	 */
	function add_log( $message ) {

		if ( ! $this->debug ) return;

		$time = date_i18n( 'm-d-Y @ H:i:s -' );
		$handle = fopen( WC_XMPP_DIR . 'debug_xmpp.log', 'a' );
		if ( $handle ) {
			fwrite( $handle, $time . " " . $message . "\n" );
			fclose( $handle );
		}

	}

	/**
	 * xmpp_get_currency_symbol
	 *
	 * @access public
	 * @return string
	 * @since 1.0.2
	 */
	function xmpp_get_currency_symbol() {
		$currency = get_woocommerce_currency();

		switch ( $currency ) {
			case 'BRL' :
				$currency_symbol = '&#82;&#36;';
				break;
			case 'AUD' :
			case 'CAD' :
			case 'MXN' :
			case 'NZD' :
			case 'HKD' :
			case 'SGD' :
			case 'USD' :
				$currency_symbol = '$';
				break;
			case 'EUR' :
				$currency_symbol = '€';
				break;
			case 'CNY' :
			case 'RMB' :
			case 'JPY' :
				$currency_symbol = '¥‎';
				break;
			case 'RUB' :
				$currency_symbol = 'руб.';
				break;
			case 'KRW' : $currency_symbol = '₩'; break;
			case 'TRY' : $currency_symbol = 'TL'; break;
			case 'NOK' : $currency_symbol = 'kr'; break;
			case 'ZAR' : $currency_symbol = 'R'; break;
			case 'CZK' : $currency_symbol = 'Kč'; break;
			case 'MYR' : $currency_symbol = 'RM'; break;
			case 'DKK' : $currency_symbol = 'kr'; break;
			case 'HUF' : $currency_symbol = 'Ft'; break;
			case 'IDR' : $currency_symbol = 'Rp'; break;
			case 'INR' : $currency_symbol = '₹'; break;
			case 'ILS' : $currency_symbol = '₪'; break;
			case 'PHP' : $currency_symbol = '₱'; break;
			case 'PLN' : $currency_symbol = 'zł'; break;
			case 'SEK' : $currency_symbol = 'kr'; break;
			case 'CHF' : $currency_symbol = 'CHF'; break;
			case 'TWD' : $currency_symbol = 'NT$'; break;
			case 'THB' : $currency_symbol = '฿'; break;
			case 'GBP' : $currency_symbol = '£'; break;
			case 'RON' : $currency_symbol = 'lei'; break;
			default    : $currency_symbol = ''; break;
		}

		return apply_filters( 'xmpp_currency_symbol', $currency_symbol, $currency );
	}

} /* class WC_XMPP */