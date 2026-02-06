<?php
/**
 * Multi-Vendor Shipping Helper
 * Handles shipping calculations for multi-seller orders
 */
class MultiVendorShippingHelper {
	private $db;
	private $config;
	
	public function __construct($db, $config) {
		$this->db = $db;
		$this->config = $config;
	}
	
	/**
	 * Split order items by seller
	 */
	public function splitOrderBySeller($order_items) {
		$seller_orders = array();
		
		foreach ($order_items as $item) {
			$seller_id = $item['seller_id'] ?? 0; // 0 = admin/marketplace
			
			if (!isset($seller_orders[$seller_id])) {
				$seller_orders[$seller_id] = array(
					'seller_id' => $seller_id,
					'items' => array(),
					'subtotal' => 0,
					'weight' => 0
				);
			}
			
			$seller_orders[$seller_id]['items'][] = $item;
			$seller_orders[$seller_id]['subtotal'] += $item['price'] * $item['quantity'];
			$seller_orders[$seller_id]['weight'] += ($item['weight'] ?? 0) * $item['quantity'];
		}
		
		return $seller_orders;
	}
	
	/**
	 * Calculate shipping per seller
	 */
	public function calculateShippingPerSeller($seller_orders, $address) {
		$shipping_costs = array();
		$total_shipping = 0;
		
		foreach ($seller_orders as $seller_id => $order) {
			// Get seller shipping settings
			$seller_shipping = $this->getSellerShippingSettings($seller_id);
			
			if ($seller_shipping['free_shipping_min'] && $order['subtotal'] >= $seller_shipping['free_shipping_min']) {
				$shipping_cost = 0;
			} else {
				// Calculate based on weight/distance
				$shipping_cost = $this->calculateShippingCost(
					$order['weight'],
					$order['subtotal'],
					$seller_shipping,
					$address
				);
			}
			
			$shipping_costs[$seller_id] = array(
				'cost' => $shipping_cost,
				'method' => $seller_shipping['method'],
				'seller_name' => $seller_shipping['seller_name']
			);
			
			$total_shipping += $shipping_cost;
		}
		
		return array(
			'per_seller' => $shipping_costs,
			'total' => $total_shipping
		);
	}
	
	/**
	 * Get seller shipping settings
	 */
	private function getSellerShippingSettings($seller_id) {
		if ($seller_id == 0) {
			// Use default marketplace shipping
			return array(
				'seller_name' => $this->config->get('config_name'),
				'method' => 'Standard Shipping',
				'flat_rate' => $this->config->get('config_shipping_flat_rate') ?: 5.00,
				'free_shipping_min' => $this->config->get('config_free_shipping_min') ?: 100.00,
				'weight_rate' => 0.50 // per kg
			);
		}
		
		// Get seller-specific shipping settings
		$query = $this->db->query("
			SELECT cs.*, 
				   CONCAT(c.firstname, ' ', c.lastname) as seller_name
			FROM " . DB_PREFIX . "customerpartner_to_customer cs
			LEFT JOIN " . DB_PREFIX . "customer c ON (cs.customer_id = c.customer_id)
			WHERE cs.customer_id = '" . (int)$seller_id . "'
		");
		
		if ($query->num_rows) {
			return array(
				'seller_name' => $query->row['seller_name'] ?: 'Seller #' . $seller_id,
				'method' => $query->row['shipping_method'] ?: 'Standard Shipping',
				'flat_rate' => $query->row['shipping_flat_rate'] ?: 5.00,
				'free_shipping_min' => $query->row['free_shipping_min'] ?: 100.00,
				'weight_rate' => $query->row['shipping_weight_rate'] ?: 0.50
			);
		}
		
		// Default fallback
		return array(
			'seller_name' => 'Seller #' . $seller_id,
			'method' => 'Standard Shipping',
			'flat_rate' => 5.00,
			'free_shipping_min' => 100.00,
			'weight_rate' => 0.50
		);
	}
	
	/**
	 * Calculate shipping cost based on weight and settings
	 */
	private function calculateShippingCost($weight, $subtotal, $settings, $address) {
		// Start with flat rate
		$cost = $settings['flat_rate'];
		
		// Add weight-based cost if weight > 5kg
		if ($weight > 5) {
			$extra_weight = $weight - 5;
			$cost += $extra_weight * $settings['weight_rate'];
		}
		
		// Zone-based multiplier (simplified)
		$zone_multiplier = $this->getZoneMultiplier($address);
		$cost *= $zone_multiplier;
		
		return round($cost, 2);
	}
	
	/**
	 * Get shipping zone multiplier based on address
	 */
	private function getZoneMultiplier($address) {
		// Simplified zone logic - can be expanded
		$country = $address['country_id'] ?? 0;
		
		// Same country = 1.0, international = 2.0
		$store_country = $this->config->get('config_country_id');
		
		if ($country == $store_country) {
			return 1.0;
		} else {
			return 2.0;
		}
	}
	
	/**
	 * Generate shipping breakdown for display
	 */
	public function generateShippingBreakdown($shipping_costs) {
		$breakdown = array();
		
		foreach ($shipping_costs['per_seller'] as $seller_id => $data) {
			$breakdown[] = array(
				'title' => 'Shipping from ' . $data['seller_name'],
				'cost' => $data['cost'],
				'text' => $this->formatCurrency($data['cost'])
			);
		}
		
		return $breakdown;
	}
	
	/**
	 * Format currency
	 */
	private function formatCurrency($amount) {
		return '$' . number_format($amount, 2);
	}
	
	/**
	 * Validate shipping address completeness
	 */
	public function validateShippingAddress($address) {
		$required = array('firstname', 'lastname', 'address_1', 'city', 'postcode', 'country_id', 'zone_id');
		$errors = array();
		
		foreach ($required as $field) {
			if (empty($address[$field])) {
				$errors[] = 'Missing required field: ' . $field;
			}
		}
		
		return empty($errors) ? true : $errors;
	}
}
