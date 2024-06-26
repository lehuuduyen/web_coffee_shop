<?php

namespace Ntvco\Traits;

trait SingletonTrait {
	/**
	 * @var static
	 */
	private static $instance;

	/**
	 * Creates a new instance of a singleton class (via late static binding),
	 * accepting a variable-length argument list.
	 *
	 * @return static
	 */
	final public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Prevents cloning the singleton instance.
	 *
	 * @return void
	 */
	final public function __clone() {
	}

	/**
	 * Prevents un-serializing the singleton instance.
	 *
	 * @return void
	 */
	final public function __wakeup() {
	}
}
