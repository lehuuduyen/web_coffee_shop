<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'wc_zalopay_settings',
	array(
		'enabled'                       => array(
			'title'       => __( 'Enable/Disable', 'woocommerce-gateway-zalopay' ),
			'label'       => __( 'Enable ZaloPay', 'woocommerce-gateway-zalopay' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'sandboxMode'                      => array(
			'title'       => __( 'Sandbox mode', 'woocommerce-gateway-zalopay' ),
			'label'       => __( 'Enable Sandbox Mode', 'woocommerce-gateway-zalopay' ),
			'type'        => 'checkbox',
			'description' => __( 'Place the payment gateway in sandbox mode.', 'woocommerce-gateway-zalopay' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		),
		'appID'                         => array(
			'title'       => __( 'AppID', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'ZaloPay AppID', 'woocommerce-gateway-zalopay' ),
			'default'     => __( '2554', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'key1'                   => array(
			'title'       => __( 'Mac key (Key 1)', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'ZaloPay  Key 1', 'woocommerce-gateway-zalopay' ),
			'default'     => __( 'sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'key2'                   => array(
			'title'       => __( 'Callback key (Key 2)', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'ZaloPay  Key 2', 'woocommerce-gateway-zalopay' ),
			'default'     => __( 'trMrHtvjo6myautxDUiAcYsVtaeQ8nhf', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'orderDescription'                   => array(
			'title'       => __( 'Description', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'Description payment for order', 'woocommerce-gateway-zalopay' ),
			'default'     => __( '[Tên đối tác] - Thanh toán đơn hàng #', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'gatewayDescription'                   => array(
			'title'       => __( 'Gateway Description', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'Description for gateway', 'woocommerce-gateway-zalopay' ),
			'default'     => __( 'Bạn sẽ được chuyển tiếp đến trang Gateway ZaloPay.', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'gatewayAppleDescription'                   => array(
			'title'       => __( 'Gateway Description', 'woocommerce-gateway-zalopay' ),
			'type'        => 'text',
			'description' => __( 'Description for gateway', 'woocommerce-gateway-zalopay' ),
			'default'     => __( 'Chú ý: Vui lòng mở trong trình duyệt Safari. </br> Bạn sẽ được chuyển tiếp đến trang Gateway ZaloPay.', 'woocommerce-gateway-zalopay' ),
			'desc_tip'    => true,
		),
		'logging'                   => array(
			'title'       => __( 'Logging', 'woocommerce-gateway-zalopay' ),
			'label'       => __( 'Log debug messages', 'woocommerce-gateway-zalopay' ),
			'type'        => 'checkbox',
			'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'woocommerce-gateway-zalopay' ),
			'default'     => 'no',
			'desc_tip'    => true,
		),
		'webhook'                       => array(
			'type'        => 'title',
			'description' => $this->displayAdminSettingsWebhookDescription()
		),
		'redirectURL'                       => array(
			'type'        => 'title',
			'description' => $this->displayAdminSettingsRedirectURLDescription()
		),
	)
);
