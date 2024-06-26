<?php

namespace Ntvco\Courier\Exception;

class InvalidAddressDataException extends InvalidParameterException {

	/**
	 * @param bool        $check
	 * @param string|null $message
	 * @param int         $code
	 */
	public static function throwIf( $check, $message = null, $code = 0 ) {
		if ( $check ) {
			$message = $message ?? esc_html__( 'The request address is invalid or unsupported!', 'Ntvco' );

			throw new static( $message, $code );
		}
	}
}
