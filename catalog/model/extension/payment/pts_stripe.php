<?php
	class ModelExtensionPaymentPtsStripe extends Model {
		public function getMethod($address, $total) {
			$this->load->language('extension/payment/pts_stripe');
			$cartData=array();
			$cartData= $this->cart->getProducts();
			$code='pts_stripe';
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_pts_stripe_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			if($this->config->get('payment_pts_stripe_status') == 1 ){
			if ($this->config->get('payment_pts_stripe_total_min') < $total && $total <  $this->config->get('payment_pts_stripe_total_max')) {
				$status = true;
				} elseif (!$this->config->get('payment_pts_stripe_geo_zone_id')) {
				$status = true;
				} elseif ($query->num_rows) {
				$status = true;
				} else {
				$status = false;
			}
			} else {
				$status = false;	
			}
			
			$currencies = array(
			'AUD',
			'CAD',
			'EUR',
			'GBP',
			'JPY',
			'USD',
			'NZD',
			'CHF',
			'HKD',
			'SGD',
			'SEK',
			'DKK',
			'PLN',
			'NOK',
			'HUF',
			'CZK',
			'ILS',
			'MXN',
			'MYR',
			'BRL',
			'PHP',
			'TWD',
			'THB',
			'TRY',
			'RUB',
			'INR'
			);
			
			if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
				$status = false;
			}
			
			$method_data = array();
			if ($status) {
			if($code != '') {
				$method_data = array(
				'code'       => $code,
				'title'      => $this->config->get('payment_pts_stripe_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_pts_stripe_sort_order')
				);
			}
			}
			
			return $method_data;
		}	
	public function getCommissionData($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_commissions WHERE order_id = '" . (int)$order_id . "'");
		if($query->num_rows){
			return $query->rows;
		} else {
			return NULL;
		}
	
		}

		public function getInvoieId($order_id,$seller_id) {
		$query = $this->db->query("SELECT link_id FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice_items WHERE order_id = '" . (int)$order_id . "' AND seller_id = '" . (int)$seller_id . "'");
			if($query->num_rows){
				return $query->row['link_id'];
			} else {
				return NULL;
			}
		}
		
		public function getInvoieData($invoice_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_commission_invoice WHERE id = '" . (int)$invoice_id . "'");
			if($query->num_rows){
				return $query->row;
			} else {
				return NULL;
			}
		}		
		
		public function getStripeAccountID($seller_id) {
		$query = $this->db->query("SELECT account_id FROM " . DB_PREFIX . "purpletree_stripe_account WHERE seller_id = '" . (int)$seller_id . "'");
			if($query->num_rows){
				return $query->row['account_id'];
			} else {
				return NULL;
			}
		}		
		public function getCommissionsforinvoide($commission_id = NULL){
			
			$sql = "SELECT * FROM " . DB_PREFIX . "purpletree_vendor_commissions WHERE id ='".(int)$commission_id."'";
			$query  = $this->db->query($sql);
			return $query->row;
		}
		public function getCommissionTotal($order_id,$seller_id) {            
            $query = $this->db->query("SELECT value FROM ". DB_PREFIX ."purpletree_order_total WHERE order_id='".(int)$order_id."' AND seller_id='".(int)$seller_id."' AND code='total'");
			if ($query->num_rows) {
				return $query->row['value'];
			}
			return false;
		}
			public function savelinkid($total_price,$total_commission,$total_pay_amount){
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_commission_invoice SET total_amount='".(float)$total_price."',total_commission='".(float)$total_commission."', total_pay_amount='".(float)$total_pay_amount."', created_at ='".date('Y-m-d')."'");
			return $this->db->getLastId();
		}

		
		public function saveCommisionInvoice($data=array(),$link_id = NULL){
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_commission_invoice_items SET order_id ='".(int)$data['order_id']."', product_id='".(int)$data['product_id']."', seller_id='".(int)$data['seller_id']."', commission_fixed='".(int)$data['commission_fixed']."', commission_percent='".(float)$data['commission_percent']."', commission_shipping='".(float)$data['commission_shipping']."', total_commission ='".(int)$data['commission']."', link_id ='".(int)$link_id."'");
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_commissions SET invoice_status = 1 WHERE id='".(int)$data['id']."'");
		} 
		public function saveTranDetail($data=array()){
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_payments SET invoice_id ='".(int)$data['invoice_id']."', seller_id='".(int)$data['seller_id']."', transaction_id='".$this->db->escape($data['transaction_id'])."', amount='".(float)$data['amount']."', payment_mode='".$this->db->escape($data['payment_mode'])."', status='".$this->db->escape($data['status'])."',created_at=NOW(),updated_at=NOW() ");
		} 
		public function saveTranHistory($data=array()){
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_payment_settlement_history SET invoice_id ='".(int)$data['invoice_id']."', transaction_id='".$this->db->escape($data['transaction_id'])."', comment='".$this->db->escape($data['comment'])."', payment_mode='".$this->db->escape($data['payment_mode'])."', status_id='".$this->db->escape($data['status_id'])."',created_date=NOW(),modified_date=NOW()");
		} 
	}
