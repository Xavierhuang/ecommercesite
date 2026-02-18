<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Proxy class
* Uses internal array to avoid PHP 8.2+ dynamic property deprecation.
*/
class Proxy {
	private $data = array();

    /**
     * @param	string	$key
     */
	public function __get($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

    /**
     * @param	string	$key
     * @param	mixed	$value
     */
	public function __set($key, $value) {
		$this->data[$key] = $value;
	}

	public function __isset($key) {
		return isset($this->data[$key]);
	}

	public function __call($key, $args) {
		$arg_data = array();

		$args = func_get_args();

		foreach ($args as $arg) {
			if ($arg instanceof Ref) {
				$arg_data[] =& $arg->getRef();
			} else {
				$arg_data[] =& $arg;
			}
		}

		if (isset($this->data[$key])) {
			return call_user_func_array($this->data[$key], $arg_data);
		} else {
			$trace = debug_backtrace();

			exit('<b>Notice</b>:  Undefined property: Proxy::' . $key . ' in <b>' . $trace[1]['file'] . '</b> on line <b>' . $trace[1]['line'] . '</b>');
		}
	}
}