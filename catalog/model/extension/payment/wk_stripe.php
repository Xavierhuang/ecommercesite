<?php

/**
* @version [Supported opencart version 2.3.x.x.]
* @category Webkul
* @package Opencart Marketplace Stripe Payment
* @author [Webkul] <[<http://webkul.com/>]>
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

class ModelExtensionPaymentWkStripe extends Model {

  	public function getMethod($address, $total) {

		if(isset($this->session->data['stripe_session_id'])) {
			unset($this->session->data['stripe_session_id']);
		}
		$this->language->load('extension/payment/wk_stripe');

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			$customer_group_id = 0;
		}

		$status = true;

		if ($this->config->get('payment_wk_stripe_min') > 0 && $this->config->get('payment_wk_stripe_max') > 0 && ( $this->config->get('payment_wk_stripe_min') > $total || $this->config->get('payment_wk_stripe_max') < $total) ) {
			$status = false;
		} elseif ($this->config->get('payment_wk_stripe_zone')) {

			$query = $this->db->query("SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id IN ('" . implode(",", $this->config->get('payment_wk_stripe_zone')) . "') AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')")->row;

			if(!$query AND !in_array(0, $this->config->get('payment_wk_stripe_zone'))){
				$status = false;
			}elseif ($this->config->get('payment_wk_stripe_customergroups')) {
				if(!in_array((int)$customer_group_id, $this->config->get('payment_wk_stripe_customergroups'))){
					$status = false;
				}
			}else{
				$status = false;
			}
		}else {
			$status = false;
		}
		$stripe_curriencies = $this->config->get('payment_wk_stripe_currency');

    if(!isset($stripe_curriencies[trim(strtoupper($this->session->data['currency']))]) || !$stripe_curriencies[trim(strtoupper($this->session->data['currency']))])
				$status = false;

		$method_data = array();

		if ($status) {
			$title = $this->config->get('payment_wk_stripe_title');
      		$method_data = array(
        		'code'       => 'wk_stripe',
        		'title'      => isset($title[$this->config->get('config_language_id')]) ? $title[$this->config->get('config_language_id')] : $this->language->get('text_title'),
        		'terms'      => '',
				'sort_order' => $this->config->get('payment_wk_stripe_sort_order'),
      		);
		}

      if($this->config->get('module_marketplace_status')) {
          return $method_data;
      }
  	}

  	public function isConnected($id = ''){
  		if(!$id)
  			$id = $this->customer->getId();

  		$keyVal = 'qwerfghjkl;;akd;kad;ka;dka;kd';
  		$sellerData = $this->db->query("SELECT
			*,
			AES_DECRYPT(refresh_token,'".$this->db->escape($keyVal)."') refresh_token,
			AES_DECRYPT(publishable_key,'".$this->db->escape($keyVal)."') publishable_key,
			AES_DECRYPT(token,'".$this->db->escape($keyVal)."') token,
			AES_DECRYPT(user_id,'".$this->db->escape($keyVal)."') user_id
			FROM ".DB_PREFIX."order_stripe_seller WHERE customer_id = '".$id."'")->row;

  		return $sellerData;
  	}

  	public function addTransaction($seller,$amount,$comment,$order_id){
  		$this->db->query("UPDATE ".DB_PREFIX."customerpartner_to_order SET paid_status = 1 WHERE order_id = '".$order_id."' AND customer_id = '".$seller."'");

  		if (version_compare(VERSION,'2.2.0.0','>=')) {
  			$this->db->query("INSERT INTO ".DB_PREFIX."customerpartner_to_transaction SET customer_id = '".$seller."', order_id = '".$order_id."', amount = '".((float)$amount/100)."', text = '".$this->currency->format(((float)$amount/100),$this->session->data['currency'])."', details = '".$this->db->escape($comment)."', `date_added` = NOW() ");
  		} else {
  			$this->db->query("INSERT INTO ".DB_PREFIX."customerpartner_to_transaction SET customer_id = '".$seller."', order_id = '".$order_id."', amount = '".((float)$amount/100)."', text = '".$this->currency->format(((float)$amount/100))."', details = '".$this->db->escape($comment)."', `date_added` = NOW() ");
  		}

  	}

  	public function addStripeConnect($resp){
  		//entry in table for seller tokens
  		$keyVal = 'qwerfghjkl;;akd;kad;ka;dka;kd';
		$this->db->query("DELETE FROM ".DB_PREFIX."order_stripe_seller WHERE customer_id = '".$this->customer->getId()."'");
		$this->db->query("INSERT INTO ".DB_PREFIX."order_stripe_seller SET
		  customer_id = '".$this->customer->getId()."',
		 `token` = AES_ENCRYPT('".$this->db->escape($resp['access_token'])."','".$this->db->escape($keyVal)."'),
		 `refresh_token` = AES_ENCRYPT('".$this->db->escape($resp['refresh_token'])."','".$this->db->escape($keyVal)."'),
		 `publishable_key` = AES_ENCRYPT('".$this->db->escape($resp['stripe_publishable_key'])."','".$this->db->escape($keyVal)."'),
		 `user_id` = AES_ENCRYPT('".$this->db->escape($resp['stripe_user_id'])."','".$this->db->escape($keyVal)."'),
		 `livemode` = '".$this->db->escape($resp['livemode'])."',
		 `token_type` = '".$this->db->escape($resp['token_type'])."'");
  	}
  	public function addTransfer($seller_id,$seller_amount,$order_id,$transfer_id,$transfer_transaction,$chargeid){
  		$this->db->query(
  						"INSERT INTO `".DB_PREFIX."order_refund` SET
				  		`order_id` = '".$order_id."',
				  		transfer_id = '".$this->db->escape($transfer_id)."',
				  		transfer_transaction = '".$this->db->escape($transfer_transaction)."',
				  		charge_id = '".$chargeid."',
						`seller_id` = '".$this->db->escape($seller_id)."',
						`seller_amount` = '".$seller_amount."'");

  	}

  	public function addIntoOrderStripe($charge){

  		$order_id = $this->session->data['order_id'];

  		$this->db->query(
  						"INSERT INTO `".DB_PREFIX."order_stripe` SET
				  		`order_id` = '".$order_id."',
				  		stripe_id = '".$this->db->escape($charge['id'])."',
						`customer_id` = '".$this->db->escape($charge['customer'])."',
						`amount` = '".$this->db->escape($charge['amount'])."',
						`currency` = '".$this->db->escape($charge['currency'])."',
						`description` = '".$this->db->escape($charge['description'])."',
						`livemode` = '".$this->db->escape($charge['livemode'])."',
						`paid` = '".$this->db->escape($charge['paid'])."',

						`card_id` = '',
						`brand` = '".$this->db->escape($charge['brand'])."',
						`name` = '".$this->db->escape($charge['name'])."',
						`last4` = '".$this->db->escape($charge['last4'])."',
						`exp_month` = '".$this->db->escape($charge['exp_month'])."' ,
						`exp_year` = '".$this->db->escape($charge['exp_year'])."',
						`fingerprint` = '".$this->db->escape($charge['fingerprint'])."' ,
						`country` = '".$this->db->escape($charge['country'])."',
						`address1` = '".$this->db->escape($charge['address_line1'])."',
						`address2` = '".$this->db->escape($charge['address_line2'])."',
						`city` = '".$this->db->escape($charge['address_city'])."' ,
						`state` = '".$this->db->escape($charge['address_state'])."',
						`zip` = '".$this->db->escape($charge['address_zip'])."',
						`address_country` = '".$this->db->escape($charge['address_country'])."',

						`cvc_check` = '".$this->db->escape($charge['cvc_check'])."',
						`address_check` = '".$this->db->escape($charge['address_line1_check'])."',
						`address_zip_check` = '".$this->db->escape($charge['address_zip_check'])."'"
						);
  	}
  	public function addStatus($order_id,$product_id){
  		$this->db->query("INSERT INTO " . DB_PREFIX . "refund_status SET status = 1 , order_id = '".$order_id."', product_id= '".$product_id."'");
  	}
  	public function getAmounts($order_id,$seller_id){
  		$result = $this->db->query("SELECT customer FROM ".DB_PREFIX."customerpartner_to_order WHERE order_id = '".(int)$order_id."'AND customer_id = '".(int)$seller_id."'")->rows;
		return $result;
  	}

	  public function addEvent($data)
	  {
		  $sql = "INSERT INTO " . DB_PREFIX . "order_stripe_webhook_log SET comment = '" . $data['comment'] . "', event_encoded = '" . json_encode($data['event_encoded']) . "', date_added='" . date('Y-m-d H-i-s') . "'";

		  $this->db->query($sql);
	  }

    public function addsripeIdwithorderid($order_id,$charged_id)
    {
      $sql = "INSERT INTO " . DB_PREFIX . "order_stripepay_map SET paycharges_id = '" . $charged_id . "', order_id = '" . $order_id . "', date_added ='" . date('Y-m-d H-i-s') . "'";

      $this->db->query($sql);
    }
    public function checkorderByChargeId($charged_id){
      $result = "SELECT order_id FROM ".DB_PREFIX."order_stripepay_map  WHERE paycharges_id = '". $charged_id."'";

    return  $this->db->query($result)->row;
    }

  public function getSellerIdByOrderId($orderId) {

    $result = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_order WHERE order_id='" . (int)$orderId . "'")->row;

    return $result;
  }

  /**
   * [getting status of refund of product i.e paid or not ]
   * @param  [type] $order_id   [description]
   * @param  [type] Seller_id   [description]
   * @return [type]             [description]
   */
  public function getSellerRefundStatus($order_id,$seller_id)
  {
     $result= $this->db->query("SELECT * FROM ". DB_PREFIX . "order_refund WHERE order_id= '".$order_id."' AND seller_id= '".$seller_id."'")->row;
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
}
?>
