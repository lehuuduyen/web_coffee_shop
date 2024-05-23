<?php

namespace Ntvco\ShippingMethod;

use Ntvco\Courier\RequestParameters;
use WC_Order;

interface ShippingMethodInterface {
	/**
	 * @param RequestParameters $parameters
	 * @param WC_Order          $order
	 * @return void
	 */
	public function initialize_creation( RequestParameters $parameters, WC_Order $order );
}
