<?php
/**
 * SEO Helper - Comprehensive SEO utilities
 */
class SeoHelper {
	private $db;
	private $config;
	private $cache;
	
	public function __construct($db, $config, $cache) {
		$this->db = $db;
		$this->config = $config;
		$this->cache = $cache;
	}
	
	/**
	 * Generate SEO-friendly URL keyword from product name
	 */
	public function generateKeyword($text) {
		// Convert to lowercase
		$keyword = mb_strtolower($text, 'UTF-8');
		
		// Remove special characters
		$keyword = preg_replace('/[^a-z0-9\s-]/', '', $keyword);
		
		// Replace spaces with hyphens
		$keyword = preg_replace('/[\s]+/', '-', $keyword);
		
		// Remove multiple hyphens
		$keyword = preg_replace('/-+/', '-', $keyword);
		
		// Trim hyphens
		$keyword = trim($keyword, '-');
		
		return $keyword;
	}
	
	/**
	 * Check if SEO URL keyword is unique
	 */
	public function isKeywordUnique($keyword, $store_id = 0, $language_id = 1, $exclude_query = '') {
		$sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "seo_url 
			WHERE keyword = '" . $this->db->escape($keyword) . "' 
			AND store_id = '" . (int)$store_id . "' 
			AND language_id = '" . (int)$language_id . "'";
		
		if ($exclude_query) {
			$sql .= " AND query != '" . $this->db->escape($exclude_query) . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'] == 0;
	}
	
	/**
	 * Generate unique SEO URL keyword
	 */
	public function generateUniqueKeyword($text, $store_id = 0, $language_id = 1, $exclude_query = '') {
		$keyword = $this->generateKeyword($text);
		
		if ($this->isKeywordUnique($keyword, $store_id, $language_id, $exclude_query)) {
			return $keyword;
		}
		
		// Add number suffix if not unique
		$i = 2;
		while (!$this->isKeywordUnique($keyword . '-' . $i, $store_id, $language_id, $exclude_query)) {
			$i++;
			if ($i > 100) break; // Safety limit
		}
		
		return $keyword . '-' . $i;
	}
	
	/**
	 * Generate meta description from product description
	 */
	public function generateMetaDescription($text, $max_length = 160) {
		// Strip HTML tags
		$text = strip_tags($text);
		
		// Remove extra whitespace
		$text = preg_replace('/\s+/', ' ', $text);
		$text = trim($text);
		
		// Truncate to max length
		if (mb_strlen($text) > $max_length) {
			$text = mb_substr($text, 0, $max_length - 3) . '...';
		}
		
		return $text;
	}
	
	/**
	 * Generate meta title from product name
	 */
	public function generateMetaTitle($name, $category_name = '', $brand_name = '') {
		$parts = array($name);
		
		if ($category_name) {
			$parts[] = $category_name;
		}
		
		if ($brand_name) {
			$parts[] = $brand_name;
		}
		
		$parts[] = $this->config->get('config_name');
		
		return implode(' | ', $parts);
	}
	
	/**
	 * Auto-generate SEO URLs for products without them
	 */
	public function autoGenerateProductSeoUrls($limit = 100) {
		// Find products without SEO URLs
		$query = $this->db->query("
			SELECT p.product_id, pd.name, pd.language_id 
			FROM " . DB_PREFIX . "product p
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
			LEFT JOIN " . DB_PREFIX . "seo_url s ON (s.query = CONCAT('product_id=', p.product_id) AND s.language_id = pd.language_id)
			WHERE s.seo_url_id IS NULL
			AND pd.name != ''
			LIMIT " . (int)$limit
		);
		
		$count = 0;
		
		foreach ($query->rows as $row) {
			$keyword = $this->generateUniqueKeyword($row['name'], 0, $row['language_id']);
			
			$this->db->query("
				INSERT INTO " . DB_PREFIX . "seo_url 
				SET store_id = '0', 
					language_id = '" . (int)$row['language_id'] . "', 
					query = 'product_id=" . (int)$row['product_id'] . "', 
					keyword = '" . $this->db->escape($keyword) . "'
			");
			
			$count++;
		}
		
		if ($count > 0) {
			$this->cache->delete('seo_url');
		}
		
		return $count;
	}
	
	/**
	 * Validate and fix SEO URLs
	 */
	public function validateAndFixSeoUrls() {
		$issues = array();
		
		// Find duplicate keywords
		$query = $this->db->query("
			SELECT keyword, store_id, language_id, COUNT(*) as count 
			FROM " . DB_PREFIX . "seo_url 
			GROUP BY keyword, store_id, language_id 
			HAVING count > 1
		");
		
		foreach ($query->rows as $row) {
			$issues[] = 'Duplicate keyword: ' . $row['keyword'] . ' (count: ' . $row['count'] . ')';
		}
		
		// Find invalid keywords (with special characters)
		$query = $this->db->query("
			SELECT seo_url_id, keyword 
			FROM " . DB_PREFIX . "seo_url 
			WHERE keyword REGEXP '[^a-z0-9-]'
		");
		
		foreach ($query->rows as $row) {
			$issues[] = 'Invalid keyword: ' . $row['keyword'];
		}
		
		return $issues;
	}
}
