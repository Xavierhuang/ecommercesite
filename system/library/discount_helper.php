<?php
/**
 * Discount and Coupon Validation Helper
 * Validates discount codes, coupons, and promotional rules
 */
class DiscountHelper {
	private $db;
	private $config;
	private $session;
	
	public function __construct($db, $config, $session) {
		$this->db = $db;
		$this->config = $config;
		$this->session = $session;
	}
	
	/**
	 * Validate coupon code
	 */
	public function validateCoupon($code, $customer_id = 0, $cart_total = 0) {
		$errors = array();
		
		// Get coupon
		$query = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "coupon 
			WHERE code = '" . $this->db->escape($code) . "' 
			AND status = '1'
		");
		
		if (!$query->num_rows) {
			return array('valid' => false, 'errors' => array('Coupon code not found or inactive'));
		}
		
		$coupon = $query->row;
		
		// Check date validity
		if ($coupon['date_start'] != '0000-00-00' && strtotime($coupon['date_start']) > time()) {
			$errors[] = 'Coupon is not yet active';
		}
		
		if ($coupon['date_end'] != '0000-00-00' && strtotime($coupon['date_end']) < time()) {
			$errors[] = 'Coupon has expired';
		}
		
		// Check usage limit
		if ($coupon['uses_total'] > 0) {
			$usage_query = $this->db->query("
				SELECT COUNT(*) as total 
				FROM " . DB_PREFIX . "order 
				WHERE coupon_id = '" . (int)$coupon['coupon_id'] . "'
			");
			
			if ($usage_query->row['total'] >= $coupon['uses_total']) {
				$errors[] = 'Coupon has reached maximum usage limit';
			}
		}
		
		// Check per-customer usage limit
		if ($customer_id && $coupon['uses_customer'] > 0) {
			$customer_usage = $this->db->query("
				SELECT COUNT(*) as total 
				FROM " . DB_PREFIX . "order 
				WHERE coupon_id = '" . (int)$coupon['coupon_id'] . "' 
				AND customer_id = '" . (int)$customer_id . "'
			");
			
			if ($customer_usage->row['total'] >= $coupon['uses_customer']) {
				$errors[] = 'You have already used this coupon the maximum number of times';
			}
		}
		
		// Check minimum order total
		if ($coupon['total'] > 0 && $cart_total < $coupon['total']) {
			$errors[] = sprintf('Minimum order total of %s required', $this->formatCurrency($coupon['total']));
		}
		
		// Check if logged in requirement
		if ($coupon['logged'] && !$customer_id) {
			$errors[] = 'You must be logged in to use this coupon';
		}
		
		if (empty($errors)) {
			return array(
				'valid' => true,
				'coupon' => $coupon,
				'discount' => $this->calculateCouponDiscount($coupon, $cart_total)
			);
		}
		
		return array('valid' => false, 'errors' => $errors);
	}
	
	/**
	 * Calculate coupon discount amount
	 */
	private function calculateCouponDiscount($coupon, $cart_total) {
		if ($coupon['type'] == 'F') {
			// Fixed amount
			return min($coupon['discount'], $cart_total);
		} else {
			// Percentage
			return ($cart_total * $coupon['discount']) / 100;
		}
	}
	
	/**
	 * Get product discounts (quantity discounts)
	 */
	public function getProductDiscount($product_id, $quantity, $customer_group_id = 1) {
		$query = $this->db->query("
			SELECT * FROM " . DB_PREFIX . "product_discount 
			WHERE product_id = '" . (int)$product_id . "' 
			AND customer_group_id = '" . (int)$customer_group_id . "' 
			AND quantity <= '" . (int)$quantity . "' 
			AND ((date_start = '0000-00-00' OR date_start < NOW()) 
			AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY quantity DESC, priority ASC, price ASC 
			LIMIT 1
		");
		
		if ($query->num_rows) {
			return $query->row['price'];
		}
		
		return null;
	}
	
	/**
	 * Get product special price
	 */
	public function getProductSpecial($product_id, $customer_group_id = 1) {
		$query = $this->db->query("
			SELECT price FROM " . DB_PREFIX . "product_special 
			WHERE product_id = '" . (int)$product_id . "' 
			AND customer_group_id = '" . (int)$customer_group_id . "' 
			AND ((date_start = '0000-00-00' OR date_start < NOW()) 
			AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY priority ASC, price ASC 
			LIMIT 1
		");
		
		if ($query->num_rows) {
			return $query->row['price'];
		}
		
		return null;
	}
	
	/**
	 * Calculate final product price with all discounts
	 */
	public function calculateProductPrice($product_id, $base_price, $quantity = 1, $customer_group_id = 1) {
		$final_price = $base_price;
		
		// Check for special price
		$special_price = $this->getProductSpecial($product_id, $customer_group_id);
		if ($special_price !== null) {
			$final_price = min($final_price, $special_price);
		}
		
		// Check for quantity discount
		$discount_price = $this->getProductDiscount($product_id, $quantity, $customer_group_id);
		if ($discount_price !== null) {
			$final_price = min($final_price, $discount_price);
		}
		
		return $final_price;
	}
	
	/**
	 * Get available coupons for customer
	 */
	public function getAvailableCoupons($customer_id = 0, $cart_total = 0) {
		$sql = "SELECT * FROM " . DB_PREFIX . "coupon 
			WHERE status = '1' 
			AND (date_start = '0000-00-00' OR date_start <= NOW())
			AND (date_end = '0000-00-00' OR date_end >= NOW())";
		
		if ($cart_total > 0) {
			$sql .= " AND (total = 0 OR total <= '" . (float)$cart_total . "')";
		}
		
		$sql .= " ORDER BY discount DESC";
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	/**
	 * Format currency
	 */
	private function formatCurrency($amount) {
		return '$' . number_format($amount, 2);
	}
	
	/**
	 * Calculate savings percentage
	 */
	public function calculateSavingsPercentage($original_price, $final_price) {
		if ($original_price <= 0) {
			return 0;
		}
		
		$savings = (($original_price - $final_price) / $original_price) * 100;
		return round($savings, 0);
	}
	
	/**
	 * Get bulk discount tiers for product
	 */
	public function getBulkDiscountTiers($product_id, $customer_group_id = 1) {
		$query = $this->db->query("
			SELECT quantity, price, priority 
			FROM " . DB_PREFIX . "product_discount 
			WHERE product_id = '" . (int)$product_id . "' 
			AND customer_group_id = '" . (int)$customer_group_id . "' 
			AND ((date_start = '0000-00-00' OR date_start < NOW()) 
			AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY quantity ASC
		");
		
		return $query->rows;
	}
}
