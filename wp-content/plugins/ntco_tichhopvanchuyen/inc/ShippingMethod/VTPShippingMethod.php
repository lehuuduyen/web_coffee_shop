<?php

namespace Ntvco\ShippingMethod;

use Ntvco\CartShippingContext;
use Ntvco\Courier\Couriers;
use Ntvco\Courier\RequestParameters;
use WC_Order;
use WC_Shipping_Method;

class VTPShippingMethod extends WC_Shipping_Method implements ShippingMethodInterface, AccessTokenAwareInterface {
	use ShippingMethodTrait;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id = Couriers::VTP;
		$this->method_title = esc_html__( 'Viettel Post', 'Ntvco' );
		$this->method_description = wp_kses_post(
			sprintf(
				__( 'Giao hàng qua đơn vị vận chuyển <a href="%s" target="_blank">Viettel Post</a>', 'Ntvco' ),
				esc_url( 'https://viettelpost.vn' )
			)
		);

		$this->supports = [
			'settings',
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		];

		$this->setup_setting_fields();
		$this->setup_instance_fields();

		$this->init_settings();
		$this->init_instance_settings();
		$this->init();

		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			[ $this, 'process_admin_options' ]
		);
	}

	/**
	 * Initialize local pickup.
	 */
	public function init() {
		// Define user set variables.
		$this->title = $this->get_instance_option( 'title', $this->method_title );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_access_token() {
		return $this->get_option( 'access_token' );
	}

	/**
	 * Calculate local pickup shipping.
	 *
	 * @param array $package Package information.
	 */
	public function calculate_shipping( $package = [] ) {
		if ( $package['destination']['country'] !== 'VN' ) {
			return;
		}

		$context = CartShippingContext::create_from_package( $package );
		if ( $context->is_empty_address() ) {
			return;
		}

		// Check address is valid.
		$province = $context->get_province();
		$district = $context->get_district();

		// Bail if the province or district address is not valid.
		if ( $province === null || $district === null ) {
			return;
		}

		$courier = $this->get_courier();
		if ( ! $courier->get_access_token() ) {
			return;
		}

		$ship_info = $this->get_store_info();

		$services = $courier->get_available_services(
			[
				'PRODUCT_PRICE' => $context->cart_subtotal,
				'MONEY_COLLECTION' => $context->cart_subtotal,
				'PRODUCT_WEIGHT' => wc_get_weight( $context->get_total_weight(), 'g' ),

				'SENDER_PROVINCE' => $ship_info['provinceId'],
				'SENDER_DISTRICT' => $ship_info['districtId'],

				'RECEIVER_PROVINCE' => $province->get_code(),
				'RECEIVER_DISTRICT' => $district->get_code(),
			]
		);

		foreach ( $services as $service ) {
			$this->add_rate( [
				'id' => $this->get_rate_id( $service['MA_DV_CHINH'] ),
				'label' => sprintf( '%s (%s)', $this->title, $service['TEN_DICHVU'] ),
				'cost' => $service['GIA_CUOC'],
				'package' => $package,
			] );
		}
	}

	/**
	 * @return array|null
	 */
	public function get_store_info() {
		$stores = $this->get_stores();

		if ( ! $stores ) {
			return null;
		}

		return $stores->find( function ( $data ) {
			return (string) $data['groupaddressId'] === (string) $this->get_option( 'store_id' );
		} );
	}

	/**
	 * Initialise settings form fields.
	 *
	 * @return void
	 */
	public function setup_setting_fields() {
		$form_fields = [];

		$form_fields['username'] = [
			'type' => 'text',
			'title' => esc_html__( 'Username', 'Ntvco' ),
			'description' => esc_html__(
				'Username đăng nhập hệ thống Viettel Post',
				'Ntvco'
			),
		];

		$form_fields['password'] = [
			'type' => 'password',
			'title' => esc_html__( 'Password', 'Ntvco' ),
			'description' => esc_html__(
				'Password đăng nhập hệ thống Viettel Post.',
				'Ntvco'
			),
		];

		if ( $this->get_option( 'username' ) && $this->get_option( 'password' ) ) {
			$form_fields['access_token'] = [
				'type' => 'text',
				'title' => esc_html__( 'Access Token', 'Ntvco' ),
				'custom_attributes' => [
					'readonly' => true,
				],
				'description' => sprintf(
					'<a href="%s" target="_blank" class="button">Request Access Token</a>',
					esc_url( admin_url( 'admin.php?action=vnshipping-get-access-token&shipping-method=viettel_post' ) )
				),
			];
		}

		if ( $this->get_option( 'access_token' ) ) {
			$form_fields['store_title'] = [
				'title' => esc_html__( 'Kho hàng', 'Ntvco' ),
				'type' => 'title',
			];

			$form_fields['store_id'] = [
				'type' => 'radio',
				'title' => esc_html__( 'Lựa chọn kho hàng', 'Ntvco' ),
				'options_callback' => function () {
					$options = [];

					foreach ( $this->get_stores() as $store ) {
						if ( empty( $store['groupaddressId'] ) ) {
							continue;
						}

						$key = sanitize_text_field( $store['groupaddressId'] );

						$options[ $key ] = sprintf(
							'<strong>%1$s (%2$s)</strong> <br/> <span>%3$s</span>',
							esc_html( $store['name'] ?? '' ),
							esc_html( $store['phone'] ?? '' ),
							esc_html( $store['address'] ?? '' )
						);
					}

					return $options;
				},
			];

			$form_fields['sender_tile'] = [
				'title' => esc_html__( 'Thông tin người gửi', 'Ntvco' ),
				'type' => 'title',
			];

			$form_fields['sender_name'] = [
				'title' => esc_html__( 'Tên người gửi', 'Ntvco' ) . '<sup style="color: red;">*</sup>',
				'type' => 'text',
				'description' => '',
				'default' => '',
				'custom_attributes' => [ 'required' => true ],
			];

			$form_fields['sender_phone'] = [
				'title' => esc_html__( 'SĐT người gửi', 'Ntvco' ) . '<sup style="color: red;">*</sup>',
				'type' => 'text',
				'description' => '',
				'default' => '',
				'custom_attributes' => [ 'required' => true ],
			];
		}

		$this->form_fields = $form_fields;
	}

	/**
	 * Initialise instance form fields.
	 *
	 * @return void
	 */
	public function setup_instance_fields() {
		$this->instance_form_fields = [
			'title' => [
				'title' => __( 'Method title', 'woocommerce' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default' => $this->method_title,
				'desc_tip' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function settings_changed( $dirty ) {
		$this->update_option( 'current_store_info', $this->get_store_info() );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function api_token_changed() {
		$this->update_option( 'access_token', null );
		$this->update_option( 'current_store_info', null );
	}

	/**
	 * {@inheritdoc}
	 */
	public function initialize_creation( RequestParameters $parameters, WC_Order $order ) {
		$parameters->set( 'ORDER_NUMBER', (string) $order->get_id() );
		$parameters->set( 'GROUPADDRESS_ID', $this->get_option( 'store_id' ) );
		$parameters->set( 'CUS_ID', $order->get_customer_id() );

		$parameters->set( 'SENDER_FULLNAME', $this->get_option( 'sender_name' ) );
		$parameters->set( 'SENDER_PHONE', $this->get_option( 'sender_phone' ) );

		$parameters->set(
			'LIST_ITEM',
			array_map(
				function ( $item ) {
					/** @var \WC_Order_Item_Product $item */
					$product = $item->get_product();

					$weight = $product ? $product->get_weight() : 0;

					return [
						'PRODUCT_NAME' => $item->get_name(),
						'PRODUCT_PRICE' => $item->get_total(),
						'PRODUCT_WEIGHT' => $weight ? wc_get_weight( $weight, 'g' ) : 100,
						'PRODUCT_QUANTITY' => $item->get_quantity(),
					];
				},
				array_values( $order->get_items() )
			)
		);
	}
}
