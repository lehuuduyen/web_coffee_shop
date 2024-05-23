<?php

namespace Ntvco\Courier;

class Couriers {
	public const VTP = 'viettel_post';
	public const GHN = 'giao_hang_nhanh';
	public const GHTK = 'giao_hang_tiet_kiem';

	/**
	 * @var string[]
	 */
	public static $aliases = [
		'vtp' => self::VTP,
		'ghn' => self::GHN,
		'ghtk' => self::GHTK,
	];

	/**
	 * @return array|null
	 */
	public static function getCourier( $name ) {
		if ( array_key_exists( $name, static::$aliases ) ) {
			$name = static::$aliases[ $name ];
		}

		return static::getCouriers()[ $name ] ?? null;
	}

	/**
	 * @return array
	 */
	public static function getCouriers() {
		return [
			static::VTP => [
				'id' => static::VTP,
				'name' => esc_html__( 'Viettel Post', 'Ntvco' ),
				'icon' => untrailingslashit( NTVCO_PLUGIN_DIR_URL ) . '/resources/icons/vtp.png',
			],
			static::GHN => [
				'id' => static::GHN,
				'name' => esc_html__( 'Giao Hàng Nhanh', 'Ntvco' ),
				'icon' => untrailingslashit( NTVCO_PLUGIN_DIR_URL ) . '/resources/icons/ghn.png',
			],
			static::GHTK => [
				'id' => static::GHTK,
				'name' => esc_html__( 'Giao Hàng Tiết Kiệm', 'Ntvco' ),
				'icon' => untrailingslashit( NTVCO_PLUGIN_DIR_URL ) . '/resources/icons/ghtk.png',
			],
		];
	}
}
