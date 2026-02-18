<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Document class
*/
class Document {
	private $title;
	private $description;
	private $keywords;
	private $links = array();
	private $robots = 'index, follow';
	private $og_image = '';
	private $og_url = '';
	private $og_type = 'website';
	private $styles = array();
	private $stylespts = array();
	private $scripts = array();
	private $scriptpts = array();

	/**
     * 
     *
     * @param	string	$title
     */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
     * 
	 * 
	 * @return	string
     */
	public function getTitle() {
		return $this->title;
	}

	/**
     * 
     *
     * @param	string	$description
     */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
     * 
     *
     * @param	string	$description
	 * 
	 * @return	string
     */
	public function getDescription() {
		return $this->description;
	}

	/**
     * 
     *
     * @param	string	$keywords
     */
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
     *
	 * 
	 * @return	string
     */
	public function getKeywords() {
		return $this->keywords;
	}
	
	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
     */
	public function addLink($href, $rel) {
		$this->links[$href] = array(
			'href' => $href,
			'rel'  => $rel
		);
	}

	/**
     * 
	 * 
	 * @return	array
     */
	public function getLinks() {
		return $this->links;
	}

	/**
	 * Set robots meta content (e.g. 'index, follow' or 'noindex, nofollow').
	 *
	 * @param string $robots
	 */
	public function setRobots($robots) {
		$this->robots = $robots;
	}

	/**
	 * @return string
	 */
	public function getRobots() {
		return $this->robots;
	}

	/**
	 * Set Open Graph image URL (full URL for og:image).
	 *
	 * @param string $url
	 */
	public function setOgImage($url) {
		$this->og_image = $url;
	}

	public function getOgImage() {
		return $this->og_image;
	}

	/**
	 * Set Open Graph URL (canonical page URL for og:url).
	 *
	 * @param string $url
	 */
	public function setOgUrl($url) {
		$this->og_url = $url;
	}

	public function getOgUrl() {
		return $this->og_url;
	}

	/**
	 * Set Open Graph type (e.g. 'website' or 'product').
	 *
	 * @param string $type
	 */
	public function setOgType($type) {
		$this->og_type = $type;
	}

	public function getOgType() {
		return $this->og_type;
	}

	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
	 * @param	string	$media
     */
	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[$href] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	/**
     * 
     * 
     * @return	array
     */
	public function getStyles() {
		return $this->styles;
	}

	/**
     * Add Purple Tree Multivendor style (same structure as addStyle for templates that use stylespts).
     *
     * @param	string	$href
     * @param	string	$rel
     * @param	string	$media
     */
	public function addStylepts($href, $rel = 'stylesheet', $media = 'screen') {
		$this->stylespts[$href] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	/**
     * 
     * @return	array
     */
	public function getStylespts() {
		return $this->stylespts;
	}

	/**
     * Add Purple Tree Multivendor script (same structure as addScript for templates that use scriptpts).
     *
     * @param	string	$href
     * @param	string	$postion
     */
	public function addScriptpts($href, $postion = 'header') {
		$this->scriptpts[$postion][$href] = $href;
	}

	/**
     * Get Purple Tree Multivendor scripts for position.
     *
     * @param	string	$postion
     * @return	array
     */
	public function getScriptspts($postion = 'header') {
		if (isset($this->scriptpts[$postion])) {
			return $this->scriptpts[$postion];
		}
		return array();
	}

	/**
     * 
     *
     * @param	string	$href
     * @param	string	$postion
     */
	public function addScript($href, $postion = 'header') {
		$this->scripts[$postion][$href] = $href;
	}

	/**
     * 
     *
     * @param	string	$postion
	 * 
	 * @return	array
     */
	public function getScripts($postion = 'header') {
		if (isset($this->scripts[$postion])) {
			return $this->scripts[$postion];
		} else {
			return array();
		}
	}
}