<?php
/**
 * Analytics Integration Helper
 * Google Analytics 4 and Facebook Pixel integration
 */
class AnalyticsHelper {
	private $config;
	
	public function __construct($config) {
		$this->config = $config;
	}
	
	/**
	 * Generate Google Analytics 4 tracking code
	 */
	public function getGA4TrackingCode() {
		$ga4_id = $this->config->get('config_ga4_measurement_id');
		
		if (empty($ga4_id)) {
			return '';
		}
		
		return <<<HTML
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$ga4_id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$ga4_id}');
</script>
HTML;
	}
	
	/**
	 * Generate Facebook Pixel tracking code
	 */
	public function getFacebookPixelCode() {
		$pixel_id = $this->config->get('config_facebook_pixel_id');
		
		if (empty($pixel_id)) {
			return '';
		}
		
		return <<<HTML
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixel_id}');
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" style="display:none" 
src="https://www.facebook.com/tr?id={$pixel_id}&ev=PageView&noscript=1"/>
</noscript>
HTML;
	}
	
	/**
	 * Track product view
	 */
	public function trackProductView($product) {
		$ga4_code = $this->config->get('config_ga4_measurement_id') ? $this->generateGA4ProductView($product) : '';
		$fb_code = $this->config->get('config_facebook_pixel_id') ? $this->generateFBProductView($product) : '';
		
		return $ga4_code . $fb_code;
	}
	
	/**
	 * Generate GA4 product view event
	 */
	private function generateGA4ProductView($product) {
		$product_json = json_encode(array(
			'item_id' => $product['product_id'],
			'item_name' => $product['name'],
			'price' => $product['price'],
			'item_category' => $product['category'] ?? ''
		));
		
		return <<<HTML
<script>
gtag('event', 'view_item', {
  items: [{$product_json}]
});
</script>
HTML;
	}
	
	/**
	 * Generate Facebook Pixel product view event
	 */
	private function generateFBProductView($product) {
		return <<<HTML
<script>
fbq('track', 'ViewContent', {
  content_ids: ['{$product['product_id']}'],
  content_name: '{$product['name']}',
  content_type: 'product',
  value: {$product['price']},
  currency: 'USD'
});
</script>
HTML;
	}
	
	/**
	 * Track add to cart
	 */
	public function trackAddToCart($product, $quantity = 1) {
		$ga4_code = $this->config->get('config_ga4_measurement_id') ? $this->generateGA4AddToCart($product, $quantity) : '';
		$fb_code = $this->config->get('config_facebook_pixel_id') ? $this->generateFBAddToCart($product, $quantity) : '';
		
		return $ga4_code . $fb_code;
	}
	
	/**
	 * Generate GA4 add to cart event
	 */
	private function generateGA4AddToCart($product, $quantity) {
		$value = $product['price'] * $quantity;
		
		return <<<HTML
<script>
gtag('event', 'add_to_cart', {
  items: [{
    item_id: '{$product['product_id']}',
    item_name: '{$product['name']}',
    price: {$product['price']},
    quantity: {$quantity}
  }],
  value: {$value},
  currency: 'USD'
});
</script>
HTML;
	}
	
	/**
	 * Generate Facebook Pixel add to cart event
	 */
	private function generateFBAddToCart($product, $quantity) {
		$value = $product['price'] * $quantity;
		
		return <<<HTML
<script>
fbq('track', 'AddToCart', {
  content_ids: ['{$product['product_id']}'],
  content_name: '{$product['name']}',
  content_type: 'product',
  value: {$value},
  currency: 'USD'
});
</script>
HTML;
	}
	
	/**
	 * Track purchase/conversion
	 */
	public function trackPurchase($order) {
		$ga4_code = $this->config->get('config_ga4_measurement_id') ? $this->generateGA4Purchase($order) : '';
		$fb_code = $this->config->get('config_facebook_pixel_id') ? $this->generateFBPurchase($order) : '';
		
		return $ga4_code . $fb_code;
	}
	
	/**
	 * Generate GA4 purchase event
	 */
	private function generateGA4Purchase($order) {
		$items = array();
		foreach ($order['products'] as $product) {
			$items[] = array(
				'item_id' => $product['product_id'],
				'item_name' => $product['name'],
				'price' => $product['price'],
				'quantity' => $product['quantity']
			);
		}
		
		$items_json = json_encode($items);
		
		return <<<HTML
<script>
gtag('event', 'purchase', {
  transaction_id: '{$order['order_id']}',
  value: {$order['total']},
  currency: 'USD',
  tax: {$order['tax']},
  shipping: {$order['shipping']},
  items: {$items_json}
});
</script>
HTML;
	}
	
	/**
	 * Generate Facebook Pixel purchase event
	 */
	private function generateFBPurchase($order) {
		$content_ids = array();
		foreach ($order['products'] as $product) {
			$content_ids[] = $product['product_id'];
		}
		$content_ids_json = json_encode($content_ids);
		
		return <<<HTML
<script>
fbq('track', 'Purchase', {
  content_ids: {$content_ids_json},
  content_type: 'product',
  value: {$order['total']},
  currency: 'USD'
});
</script>
HTML;
	}
	
	/**
	 * Track search
	 */
	public function trackSearch($search_term) {
		if (!$this->config->get('config_ga4_measurement_id')) {
			return '';
		}
		
		return <<<HTML
<script>
gtag('event', 'search', {
  search_term: '{$search_term}'
});
</script>
HTML;
	}
}
