<?php

/**
* @version [Supported opencart version 3.x.x.x.]
* @category Webkul
* @package Opencart Marketplace Stripe Payment
* @author [Webkul] <[<http://webkul.com/>]>
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

class ModelExtensionPaymentWkStripe extends Model {
	/**
	 * [creating tables for keep the stripe details]
	 * @return [type] [description]
	 */
	public function createTable(){
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_stripe (
					                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					                        `order_id` INT(100) NOT NULL ,
					                        `stripe_id` varchar(100) NOT NULL ,
					                        `customer_id` varchar(200) NOT NULL ,
					                        `description` varchar(200) NOT NULL ,
					                        `amount` int(100) NOT NULL ,
					                        `currency` varchar(100) NOT NULL ,
					                        `livemode` int(10) NOT NULL ,
					                      	`paid` int(10) NOT NULL ,
					                        `card_id` varchar(200) NOT NULL ,
					                        `brand` varchar(100) NOT NULL ,
					                        `name` varchar(100) NOT NULL ,
					                        `last4` varchar(15) NOT NULL ,
					                        `exp_month` int(10) NOT NULL ,
					                        `exp_year` int(10) NOT NULL ,
					                        `fingerprint` varchar(200) NOT NULL ,
					                        `country` varchar(100) NOT NULL ,
					                        `address1` varchar(100) NOT NULL ,
					                        `address2` varchar(15) NOT NULL ,
					                        `city` varchar(200) NOT NULL ,
					                        `state` varchar(200) NOT NULL ,
					                        `zip` varchar(100) NOT NULL ,
					                        `address_country` varchar(100) NOT NULL ,
					                        `cvc_check` varchar(15) NOT NULL ,
					                        `address_check` varchar(15) NOT NULL ,
					                        `address_zip_check` varchar(15) NOT NULL ,
					                        PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8 ;"
										);

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_stripe_seller (
					                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					                        `customer_id` INT(100) NOT NULL ,
					                        `token` BLOB NOT NULL ,
					                        `refresh_token` BLOB NOT NULL ,
					                        `publishable_key` BLOB NOT NULL ,
					                      	`user_id` BLOB NOT NULL ,
					                        `token_type` varchar(100) NOT NULL ,
					                        `scope` varchar(200) NOT NULL ,
					                        `livemode` INT(10) NOT NULL ,
					                        `date` Date NOT NULL ,
					                        PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8 ;"
										);
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "refund_status (
					                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					                        `order_id` INT(10) NOT NULL ,
					                      	`product_id` INT(10) NOT NULL ,
					                        `status` INT(2) NOT NULL ,
					                        PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8 ;"
										);
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_refund (
					                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					                        `order_id` INT(100) NOT NULL ,
					                        `seller_id` INT(100) NOT NULL ,
					                        `seller_amount` INT(100) NOT NULL ,
					                        `transfer_id` varchar(100) NOT NULL ,
					                        `transfer_transaction` varchar(100) NOT NULL ,
					                        `charge_id` varchar(100) NOT NULL ,
					                        `refund_status` INT(2) NOT NULL ,
					                        PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8 ;"
										);

										$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_stripe_capture (
										`order_id`          INT(100) NOT NULL,
										`payment_intent_id` VARCHAR(500) NOT NULL,
										`capture_status`    TINYINT(1) DEFAULT 0,
										`refund_status`     TINYINT(1) DEFAULT 0,
										`cancel_status`     TINYINT(1) DEFAULT 0)"
											);

											$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_stripe_webhook_log (
											`log_id`          INT(11) NOT NULL AUTO_INCREMENT,
											`order_id` INT(11) DEFAULT 0,
											`comment` TEXT NOT NULL,
											`event_encoded` TEXT DEFAULT '',
											`date_added`    DATETIME NOT NULL,
											PRIMARY KEY (`log_id`) ) DEFAULT CHARSET=utf8 ;"
											);

										
											$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "order_stripepay_map (
												`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
												`order_id` INT(10) NOT NULL ,
												`paycharges_id` VARCHAR(100) NOT NULL ,
												`date_added`    DATETIME NOT NULL,
												PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8;");
	}

				/**
			* [to get the information of order stripe capture]
			* @param  [type] $order_id [id of order]

			*/

			public function getCaptureInfo($order_id)
			{
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_stripe_capture WHERE order_id = '" . (int) $order_id . "'");
					return $query->row;
			}
	/**
	 * [to get the information of order]
	 * @param  [type] $order_id [id of order]
	 * @return [type]           [product of that order]
	 */
	public function getOrderData($order_id){
		$result = $this->db->query("SELECT * FROM ".DB_PREFIX."order_stripe os WHERE os.order_id = '".(int)$order_id."'")->row;
		return $result;
	}

	/**
	 * [getting details of the transaction]
	 * @param  [type] $order_id  [transaction about order id]
	 * @param  [type] $seller_id [seller id about which transaction detail to get]
	 * @return [type]            [transaction id and charge id other detail corresponding to orderid and sellerid]
	 */
	public function getTransaction($order_id,$seller_id){
  		$transactionData = $this->db->query("SELECT transfer_id,transfer_transaction,seller_amount,charge_id
			FROM ".DB_PREFIX."order_refund WHERE order_id = '".$order_id."'AND seller_id = '".$seller_id."'")->rows;
  		   return $transactionData;
  	}

  	/**
  	 * [getting detail from order table to display in list of stripe transactoion]
  	 * @param  [type] $order_id [description]
  	 * @return [type]           [description]
  	 */
  	public function getinfo($order_id,$product_id){
  		$info= $this->db->query("SELECT customer_id,price,admin,customer FROM ".DB_PREFIX."customerpartner_to_order WHERE order_id = '".(int)$order_id."' AND product_id='" . (int)$product_id . "'")->row;
  		return $info;
  	}

		public function getinfostripe($order_id, $seller_id){
			$info= $this->db->query("SELECT seller_id,seller_amount,refund_status FROM ".DB_PREFIX."order_refund WHERE order_id = '".(int)$order_id."' AND seller_id='" . (int)$seller_id . "'")->row;
			return $info;
		}

  	/**
  	 * [getting detail about stripe orders]
  	 * @param  [type] $filter_array [description]
  	 * @return [type]               [description]
  	 */
  	public function getStripeOrders($filter_array){

  	// $results= $this->db->query("SELECT * FROM ".DB_PREFIX."order WHERE payment_method = '".$payment_method[1]['name']."'")->rows;
  	// return $results;
  	$sql = "SELECT * FROM `".DB_PREFIX."order` ct WHERE payment_code = 'wk_stripe' AND order_status_id <> 0";

		if (!empty($filter_array['filter_name'])) {
			$sql .= " AND LCASE(CONCAT(ct.firstname, ' ', ct.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_name'])) . "%'";
		}

		if (!empty($filter_array['filter_date'])) {
			$sql .= " AND LCASE(ct.date_added) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_date'])) . "%'";
		}

		if (!empty($filter_array['filter_id'])) {
			$sql .= " AND ct.order_id = '" . (float)$this->db->escape($filter_array['filter_id']) . "'";
		}

		if (!empty($filter_array['filter_amount'])) {
			$sql .= " AND ct.total = '" . (float)$this->db->escape($filter_array['filter_amount']) . "'";
		}

		$sort_data = array(
			'ct.order_id',
			'ct.amount',
			'ct.date_added',
			'c.firstname',

		);

		if (isset($filter_array['sort']) && in_array($filter_array['sort'], $sort_data)) {
			if($filter_array['sort']=='ct.amount')
			$sql .= " ORDER BY total" ;
			else
			$sql .= " ORDER BY " . $filter_array['sort'];
		} else {
			$sql .= " ORDER BY ct.order_id";
		}
		if (isset($filter_array['order']) && ($filter_array['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($filter_array['start']) || isset($filter_array['limit'])) {
			if ($filter_array['limit'] < 1) {
				$filter_array['limit'] = 10;
			}
			if ($filter_array['start'] < 0) {
				$filter_array['start'] = 0;
			}else{
				$filter_array['start'] = ($filter_array['page']-1)*$filter_array['limit'];
			}

			$sql .= " LIMIT " . (int)$filter_array['start'] . "," . (int)$filter_array['limit'];
		}
		$result=$this->db->query($sql);

		return $result->rows;
	}

	  /**
  	 * [getting status of refund of product i.e paid or not ]
  	 * @param  [type] $order_id   [description]
  	 * @param  [type] $product_id [description]
  	 * @return [type]             [description]
  	 */
  	public function getSellerRefundStatus($order_id,$seller_id)
  	{
  		 $result= $this->db->query("SELECT * FROM ". DB_PREFIX . "order_refund WHERE order_id= '".$order_id."' AND seller_id= '".$seller_id."'")->row;
  		 return $result;
  	}

  	/**
  	 * [getting status of refund of product i.e paid or not ]
  	 * @param  [type] $order_id   [description]
  	 * @param  [type] $product_id [description]
  	 * @return [type]             [description]
  	 */
  	public function getRefundStatus($order_id,$product_id)
  	{
  		 $result= $this->db->query("SELECT status FROM ". DB_PREFIX . "refund_status WHERE order_id= '".$order_id."' AND product_id= '".$product_id."'")->row;
  		 return $result;
	}

	/**
  	 * [UpdateStatus after refund the amount]
  	 * @param [type] $order_id   [description]
  	 * @param [type] $product_id [description]
  	 */
  	public function UpdateSellerStatus($order_id,$seller_id){
		$this->db->query("UPDATE ".DB_PREFIX."order_refund SET refund_status = 1 WHERE order_id = '".$order_id."' AND seller_id = '".$seller_id."'");

		if($seller_id) {
			$product_info = $this->db->query("SELECT product_id FROM ".DB_PREFIX."customerpartner_to_order WHERE order_id = '".(int)$order_id."' AND customer_id='" . (int)$seller_id . "'")->rows;
		} else {
			$product_info = $this->db->query("SELECT product_id FROM ".DB_PREFIX."order_product WHERE order_id = '".(int)$order_id."' AND product_id NOT IN(SELECT product_id FROM " . DB_PREFIX . "customerpartner_to_order WHERE order_id = '".(int)$order_id."')")->rows;
		}

		if($product_info) {
			foreach ($product_info as $key => $product) {
				$this->db->query("UPDATE ".DB_PREFIX."refund_status SET status = 0 WHERE order_id = '".$order_id."' AND product_id = '".$product['product_id']."'");
			}
		}

	}

	  /**
  	 * [UpdateStatus after refund the amount]
  	 * @param [type] $order_id   [description]
  	 * @param [type] $product_id [description]
  	 */
  	public function UpdateStatus($order_id,$product_id){
  		$this->db->query("UPDATE ".DB_PREFIX."refund_status SET status = 0 WHERE order_id = '".$order_id."' AND product_id = '".$product_id."'");
  	}

  	/**
  	 * [getChargeid which is used in reverse transfer and refund according to order id]
  	 * @param  [type] $order_id [description]
  	 * @return [type]           [description]
  	 */
  	public function getChargeid($order_id){
  		$result=$this->db->query("SELECT stripe_id FROM ". DB_PREFIX . "order_stripe WHERE order_id= '".$order_id."'")->row;
  		return $result;
  	}

  	/**
  	 * [getName of the seller correspomnding to product]
  	 * @param  [type] $seller_id [description]
  	 * @return [type]            [description]
  	 */
  	public function getName($seller_id){
  		$result = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) as name FROM ".DB_PREFIX."customer WHERE customer_id = '".(int)$seller_id."'")->row;
		return $result;

	  }

	  public function getSellerNameByProduct($product_id) {
		$result = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id='" . (int)$product_id . "'")->row;

		if($result && $result['customer_id']) {
			$name = $this->getName($result['customer_id']);
			return $name;
		} else {
			return 'Admin';
		}
	}
  	/**
  	 * [getTotalRefund amount for transfer to customer with tax]
  	 * @param  [type] $order_id   [description]
  	 * @param  [type] $product_id [description]
  	 * @return [type]             [description]
  	 */
  	public function getTotalRefund($order_id,$product_id){
	  	$result = $this->db->query("SELECT total,tax FROM ".DB_PREFIX."order_product WHERE order_id = '".(int)$order_id."' AND product_id = '".(int)$product_id."'")->row;
			return $result;
	}

	/**
	 * [getTotal number of product in the list]
	 * @param  [type] $filter_array [description]
	 * @return [type]               [description]
	 */
	public function getTotal($filter_array){

		$sql = "SELECT COUNT(DISTINCT o.order_id) AS total FROM `" . DB_PREFIX . "order` o WHERE payment_code = 'wk_stripe' AND order_status_id !='0'";

		if (!empty($filter_array['filter_name'])) {
			$sql .= " AND LCASE(CONCAT(o.firstname, ' ', o.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_name'])) . "%'";
		}

		if (!empty($filter_array['filter_date'])) {
			$sql .= " AND LCASE(o.date_added) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_date'])) . "%'";
		}

		if (!empty($filter_array['filter_id'])) {
			$sql .= " AND o.order_id = '" . (float)$this->db->escape($filter_array['filter_id']) . "'";
		}

		if (!empty($filter_array['filter_amount'])) {
			$sql .= " AND o.total = '" . (float)$this->db->escape($filter_array['filter_amount']) . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];

	}

	public function getRefundableOrders($results=array()){

		$resul = array();

		foreach ($results as $result) {
			$res = $this->db->query("SELECT * FROM ".DB_PREFIX."order_refund WHERE order_id = '".(int)$result['order_id']."'")->row;
			if(!empty($res)){
				$resul[]= $this->db->query("SELECT * FROM ".DB_PREFIX."order WHERE payment_code = 'wk_stripe' AND order_status_id <> 0 And order_id = '".$res['order_id']."'")->row;
			}
		}
		return $resul;
	}

	public function getStripeOrdersname($filter_array){

	// $results= $this->db->query("SELECT * FROM ".DB_PREFIX."order WHERE payment_method = '".$payment_method[1]['name']."'")->rows;
	// return $results;
	$sql = "SELECT * FROM ".DB_PREFIX."order ct WHERE payment_code = 'wk_stripe' AND order_status_id <> 0";

	if (!empty($filter_array['filter_name'])) {
		$sql .= " AND LCASE(CONCAT(ct.firstname, ' ', ct.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_name'])) . "%'";
	}

	if (!empty($filter_array['filter_date'])) {
		$sql .= " AND LCASE(ct.date_added) LIKE '%" . $this->db->escape(utf8_strtolower($filter_array['filter_date'])) . "%'";
	}

	if (!empty($filter_array['filter_id'])) {
		$sql .= " AND ct.order_id = '" . (float)$this->db->escape($filter_array['filter_id']) . "'";
	}

	if (!empty($filter_array['filter_amount'])) {
		$sql .= " AND ct.total = '" . (float)$this->db->escape($filter_array['filter_amount']) . "'";
	}

	$sort_data = array(
		'ct.order_id',
		'ct.amount',
		'ct.date_added',
		'c.firstname',

	);

	$sql .= " Group By ct.customer_id";

	if (isset($filter_array['sort']) && in_array($filter_array['sort'], $sort_data)) {
		if($filter_array['sort']=='ct.amount')
		$sql .= " ORDER BY total" ;
		else
		$sql .= " ORDER BY " . $filter_array['sort'];
	} else {
		$sql .= " ORDER BY ct.order_id";
	}
	if (isset($filter_array['order']) && ($filter_array['order'] == 'DESC')) {
		$sql .= " DESC";
	} else {
		$sql .= " ASC";
	}

	if (isset($filter_array['start']) || isset($filter_array['limit'])) {
		if ($filter_array['limit'] < 1) {
			$filter_array['limit'] = 10;
		}
		if ($filter_array['start'] < 0) {
			$filter_array['start'] = 0;
		}else{
			$filter_array['start'] = ($filter_array['page']-1)*$filter_array['limit'];
		}

		$sql .= " LIMIT " . (int)$filter_array['start'] . "," . (int)$filter_array['limit'];
	}
	$result=$this->db->query($sql);

	return $result->rows;
	}
}
?>
