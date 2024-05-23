<?php

namespace Ntvco\ShippingMethod;

interface AccessTokenAwareInterface {
	/**
	 * @return string|null
	 */
	public function get_access_token();
}
