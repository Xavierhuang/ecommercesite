<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		// GA4 and Facebook Pixel via AnalyticsHelper (scope 2.1) when config IDs are set
		$data['analytics_helper_html'] = '';
		if ($this->config->get('config_ga4_measurement_id') || $this->config->get('config_facebook_pixel_id')) {
			if (!class_exists('AnalyticsHelper')) {
				require_once(DIR_SYSTEM . 'library/analytics_helper.php');
			}
			$analytics_helper = new AnalyticsHelper($this->config);
			$data['analytics_helper_html'] = $analytics_helper->getGA4TrackingCode() . $analytics_helper->getFacebookPixelCode();
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		$data['robots'] = $this->document->getRobots();
		$request_uri = isset($this->request->server['REQUEST_URI']) ? $this->request->server['REQUEST_URI'] : '';
		$data['current_url'] = rtrim($server, '/') . (strpos($request_uri, '/') === 0 ? $request_uri : '/' . $request_uri);
		$data['og_title'] = $this->document->getTitle();
		$data['og_description'] = $this->document->getDescription();
		$data['og_url'] = $this->document->getOgUrl() ? $this->document->getOgUrl() : $data['current_url'];
		$data['og_type'] = $this->document->getOgType();
		if ($this->document->getOgImage()) {
			$data['og_image'] = $this->document->getOgImage();
		} elseif (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['og_image'] = rtrim($server, '/') . '/image/' . $this->config->get('config_logo');
		} else {
			$data['og_image'] = '';
		}
		$data['og_site_name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		// Use ocsearchcategory module for icon-style search (magnifying glass that opens popup)
		$data['search'] = $this->load->controller('extension/module/ocsearchcategory');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');
		$data['block1'] = $this->load->controller('common/block1');

		return $this->load->view('common/header', $data);
	}
}
