<?php

namespace Ntvco\Courier;

class ShippingStatus {
	/**
	 * Returns the shipping statuses.
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return [
			'' => esc_html__( 'Không xác định', 'Ntvco' ),
			'ready_to_pick' => esc_html__( 'Chờ lấy hàng', 'Ntvco' ),
			'picking' => esc_html__( 'Đang lấy hàng', 'Ntvco' ),
			'money_collect_picking' => esc_html__( 'Đang thu tiền người gửi', 'Ntvco' ),
			'picked' => esc_html__( 'Lấy hàng thành công', 'Ntvco' ),
			'cancel' => esc_html__( 'Đã hủy đơn hàng', 'Ntvco' ),
			'storing' => esc_html__( 'Lưu kho', 'Ntvco' ),
			'transporting' => esc_html__( 'Đang luân chuyển kho', 'Ntvco' ),
			'sorting' => esc_html__( 'Đang được phân loại', 'Ntvco' ),
			'delivering' => esc_html__( 'Đang giao hàng', 'Ntvco' ),
			'money_collect_delivering' => esc_html__( 'Đang thu tiền người nhận', 'Ntvco' ),
			'delivered' => esc_html__( 'Giao thành công', 'Ntvco' ),
			'delivery_fail' => esc_html__( 'Giao hàng thất bại', 'Ntvco' ),
			'waiting_to_return' => esc_html__( 'Đang chờ trả hàng', 'Ntvco' ),
			'return' => esc_html__( 'Trả hàng', 'Ntvco' ),
			'return_transporting' => esc_html__( 'Luân chuyển kho trả', 'Ntvco' ),
			'return_sorting' => esc_html__( 'Phân loại hàng trả', 'Ntvco' ),
			'returning' => esc_html__( 'Đang trả hàng', 'Ntvco' ),
			'return_fail' => esc_html__( 'Trả hàng thất bại', 'Ntvco' ),
			'returned' => esc_html__( 'Trả hàng thành công', 'Ntvco' ),
			'exception' => esc_html__( 'Hàng ngoại lệ', 'Ntvco' ),
			'lost' => esc_html__( 'Hàng bị mất', 'Ntvco' ),
			'damage' => esc_html__( 'Hàng bị vỡ hoặc hư hỏng', 'Ntvco' ),
		];
	}

	/**
	 * Get the given status for display.
	 *
	 * @param string $name
	 * @return string
	 */
	public static function get_status_name( $name ) {
		static $statuses;

		if ( ! $statuses ) {
			$statuses = self::get_statuses();
		}

		return $statuses[ $name ] ?? '';
	}
}
