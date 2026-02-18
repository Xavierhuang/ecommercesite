<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Log class
*/
class Log {
	private $handle;
	
	/**
	 * Constructor
	 *
	 * @param	string	$filename
 	*/
	public function __construct($filename) {
		$path = DIR_LOGS . $filename;
		if (!is_dir(DIR_LOGS)) {
			@mkdir(DIR_LOGS, 0755, true);
		}
		$handle = @fopen($path, 'a');
		$this->handle = (is_resource($handle)) ? $handle : null;
	}
	
	/**
     * 
     *
     * @param	string	$message
     */
	public function write($message) {
		if ($this->handle !== null && is_resource($this->handle)) {
			@fwrite($this->handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
		}
	}
	
	/**
     * 
     *
     */
	public function __destruct() {
		if ($this->handle !== null && is_resource($this->handle)) {
			fclose($this->handle);
			$this->handle = null;
		}
	}
}