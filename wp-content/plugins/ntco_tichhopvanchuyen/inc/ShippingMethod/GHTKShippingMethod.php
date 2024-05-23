<?php

namespace Ntvco\ShippingMethod;

use Ntvco\CartShippingContext;
use Ntvco\Courier\Couriers;
use Ntvco\Courier\Exception\RequestException;
use Ntvco\Courier\Factory;
use Ntvco\Courier\RequestParameters;
use WC_Order;
use WC_Shipping_Method;

class GHTKShippingMethod extends WC_Shipping_Method implements ShippingMethodInterface {
	use ShippingMethodTrait;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id = Couriers::GHTK;
		$this->method_title = __( 'Giao Hàng Tiết Kiệm', 'Ntvco' );
		$this->method_description = __( 'Giao hàng qua đơn vị Giao Hàng Tiết Kiệm', 'Ntvco' );

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
		$this->title = $this->get_instance_option( 'title', 'Giao Hàng Tiết Kiệm' );
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

		$province = $context->get_province();
		$district = $context->get_district();
		$ward = $context->get_ward();

		// Bail if the province or district address is not valid.
		if ( $province === null || $district === null ) {
			return;
		}

		$courier = Factory::createFromShippingMethod( $this );
		$debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );

		try {
			$feeInfo = $courier->get_shipping_fee( [
				'pick_address_id' => (string) $this->get_option( 'shop_id' ),
				'pick_province' => '',
				'pick_district' => '',
				'pick_ward' => '',

				'address' => $context->destination['address_1'],
				'province' => $province->name,
				'district' => $district->name,
				'ward' => $ward->name ?? '',

				'weight' => wc_get_weight( $context->get_total_weight(), 'g' ) ?: 1000,
				'value' => $context->contents_cost,

				'transport' => $this->get_option( 'default_transport', 'road' ),
			] );

			if ( isset( $feeInfo['delivery'] ) && $feeInfo['delivery'] ) {
				$this->add_rate( [
					'id' => $this->get_rate_id(),
					'label' => $this->title,
					'cost' => $feeInfo['fee'],
					'package' => $package,
				] );
			}
		} catch ( RequestException $e ) {
			if ( $debug_mode ) {
				wc_add_notice( $e->getMessage() );
			}
		}
	}

	/**
	 * Initialise settings form fields.
	 *
	 * @return void
	 */
	public function setup_setting_fields() {
		$form_fields = [];

		$form_fields['api_token'] = [
			'type' => 'password',
			'title' => esc_html__( 'API Token', 'Ntvco' ),
			'description' => esc_html__(
				'API token do Giao Hàng Tiết Kiệm cung cấp. Truy cập **GHTK / Sửa thông tin cửa hàng**',
				'Ntvco'
			),
			'custom_attributes' => [ 'required' => true ],
		];

		$form_fields['is_debug'] = [
			'type' => 'checkbox',
			'title' => esc_html__( 'Môi trường thử nghiệm?', 'Ntvco' ),
			'label' => esc_html__( 'API của dành cho môi trường thử nghiệm?', 'Ntvco' ),
			'default' => 'yes',
		];

		if ( $this->get_option( 'api_token' ) ) {
			$form_fields['shop_id'] = [
				'type' => 'radio',
				'title' => esc_html__( 'Lựa chọn kho hàng:', 'Ntvco' ),
				'options_callback' => function () {
					$options = [];

					$stores = $this->get_stores();
					if ( null === $stores ) {
						return [];
					}

					foreach ( $this->get_stores() as $store ) {
						if ( empty( $store['pick_address_id'] ) ) {
							continue;
						}

						$key = sanitize_text_field( $store['pick_address_id'] );

						$options[ $key ] = sprintf(
							'<strong>%1$s - %2$s (%3$s)</strong> <br/> <span>%4$s</span>',
							esc_html( $key ),
							esc_html( $store['pick_name'] ?? '' ),
							esc_html( $store['pick_tel'] ?? '' ),
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

			$form_fields['shipping_tile'] = [
				'title' => esc_html__( 'Tính giá', 'Ntvco' ),
				'type' => 'title',
			];

			$form_fields['default_transport'] = [
				'type' => 'radio',
				'title' => esc_html__( 'Phương thức vâng chuyển', 'Ntvco' ),
				'default' => 'road',
				'options' => [
					'road' => 'Đường bộ (road)',
					'fly' => 'Đường bay (fly)',
				],
				'description' => esc_html__(
					'Nếu phương thức vận chuyển không được hỗ trợ tại địa chỉ người nhận thì GHTK sẽ tự động nhảy về PTVC mặc định.',
					'Ntvco'
				),
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
				'default' => __( 'Giao Hàng Tiết Kiệm', 'Ntvco' ),
				'desc_tip' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function initialize_creation( RequestParameters $parameters, WC_Order $order ) {
		$orderInfo = $parameters->get( 'order' ) ?: [];

		$orderInfo['pick_name'] = $this->get_option( 'sender_name' );
		$orderInfo['pick_tel'] = $this->get_option( 'sender_phone' );

		$parameters->set( 'order', $orderInfo );

		$parameters->set(
			'products',
			array_map(
				function ( $item ) {
					/** @var \WC_Order_Item_Product $item */
					$product = $item->get_product();

					$weight = $product ? $product->get_weight() : 0;

					return [
						'name' => $item->get_name(),
						'price' => $item->get_total(),
						'weight' => $weight ? wc_get_weight( $weight, 'g' ) : 100,
						'quantity' => $item->get_quantity(),
						'product_code' => $item->get_product_id(),
					];
				},
				array_values( $order->get_items() )
			)
		);
	}
}
