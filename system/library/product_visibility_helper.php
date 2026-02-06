<?php
/**
 * Product Visibility Helper
 * Ensures products appear immediately in catalog without cache delays
 */
class ProductVisibilityHelper {
	private $db;
	private $cache;
	
	public function __construct($db, $cache) {
		$this->db = $db;
		$this->cache = $cache;
	}
	
	/**
	 * Force product to be immediately visible
	 */
	public function forceProductVisible($product_id) {
		// Clear all related caches
		$this->cache->delete('product');
		$this->cache->delete('product.' . (int)$product_id);
		$this->cache->delete('category');
		$this->cache->delete('manufacturer');
		
		// Update product modified date to force refresh
		$this->db->query("UPDATE " . DB_PREFIX . "product SET date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
		
		// Clear SEO URL cache if exists
		$this->cache->delete('seo_url');
		$this->cache->delete('seo_pro');
		
		return true;
	}
	
	/**
	 * Force category products to refresh
	 */
	public function forceCategoryRefresh($category_id) {
		$this->cache->delete('category');
		$this->cache->delete('category.' . (int)$category_id);
		$this->cache->delete('product.category.' . (int)$category_id);
		
		return true;
	}
	
	/**
	 * Clear all product-related caches
	 */
	public function clearAllProductCaches() {
		$cache_keys = array(
			'product',
			'category',
			'manufacturer',
			'seo_url',
			'seo_pro',
			'information'
		);
		
		foreach ($cache_keys as $key) {
			$this->cache->delete($key);
		}
		
		return true;
	}
	
	/**
	 * Verify product is visible in catalog
	 */
	public function isProductVisible($product_id) {
		$query = $this->db->query("SELECT p.status, p.date_available 
			FROM " . DB_PREFIX . "product p 
			WHERE p.product_id = '" . (int)$product_id . "'");
		
		if ($query->num_rows) {
			$product = $query->row;
			
			// Check if enabled
			if (!$product['status']) {
				return false;
			}
			
			// Check if date available is in past
			if (strtotime($product['date_available']) > time()) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get visibility issues for a product
	 */
	public function getVisibilityIssues($product_id) {
		$issues = array();
		
		$query = $this->db->query("SELECT p.*, pd.name 
			FROM " . DB_PREFIX . "product p 
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			WHERE p.product_id = '" . (int)$product_id . "' 
			AND pd.language_id = '1'");
		
		if (!$query->num_rows) {
			$issues[] = 'Product not found';
			return $issues;
		}
		
		$product = $query->row;
		
		// Check status
		if (!$product['status']) {
			$issues[] = 'Product is disabled';
		}
		
		// Check date available
		if (strtotime($product['date_available']) > time()) {
			$issues[] = 'Product date available is in future: ' . $product['date_available'];
		}
		
		// Check if product has category
		$category_query = $this->db->query("SELECT COUNT(*) as total 
			FROM " . DB_PREFIX . "product_to_category 
			WHERE product_id = '" . (int)$product_id . "'");
		
		if ($category_query->row['total'] == 0) {
			$issues[] = 'Product has no categories assigned';
		}
		
		// Check if product has store
		$store_query = $this->db->query("SELECT COUNT(*) as total 
			FROM " . DB_PREFIX . "product_to_store 
			WHERE product_id = '" . (int)$product_id . "'");
		
		if ($store_query->row['total'] == 0) {
			$issues[] = 'Product has no stores assigned';
		}
		
		// Check if product has name
		if (empty($product['name'])) {
			$issues[] = 'Product has no name';
		}
		
		return $issues;
	}
}
