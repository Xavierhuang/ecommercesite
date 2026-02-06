<?php
class ControllerExtensionlogprcatc extends Controller {	
	public function getcachedata() {
		$json = false;
		if(!empty($this->request->post['product_ids'])) {
			$this->load->model('extension/logprcatc');
			$json = $this->model_extension_logprcatc->getcachedata($this->request->post['product_ids']);
		} 		
 		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json,true));	
	}
	public function getrsdata() {
 		$json = array();
		
		if(!$this->customer->isLogged() && $this->config->get('config_logprcatc')) { 
			$json['hideprc'] = $this->config->get('config_logprcatc_hideprc');
			
			$btntxt = $this->config->get('config_logprcatc_btntxt');
			$json['btntxt'] = html_entity_decode($btntxt[(int)$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
				
			$json['langlogin'] = $this->load->language('account/login');
			$json['actionlink'] = $this->url->link('extension/logprcatc/checklog');
			$json['registerlink'] = $this->url->link('account/register');
			$json['forgottenlink'] = $this->url->link('account/forgotten');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	public function checklog() {
		$json = array();
		
		$this->load->model('account/customer');
		$this->load->language('account/login');
		
		$data['text_login'] = $this->language->get('text_login');	
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validatelogin()) {
			// Unset guest
			unset($this->session->data['guest']);

			// Default Shipping Address
			$this->load->model('account/address');

			if ($this->config->get('config_tax_customer') == 'payment') {
				$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			if ($this->config->get('config_tax_customer') == 'shipping') {
				$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
			}

			// Wishlist
			if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
				$this->load->model('account/wishlist');

				foreach ($this->session->data['wishlist'] as $key => $product_id) {
					$this->model_account_wishlist->addWishlist($product_id);

					unset($this->session->data['wishlist'][$key]);
				}
			}

			// Add to activity log
			if ($this->config->get('config_customer_activity')) {
				$this->load->model('account/activity');

				$activity_data = array(
					'product_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
				);

				$this->model_account_activity->addActivity('login', $activity_data);
			}
		}
		
		if (isset($this->error['warning'])) {
			$json['error_warning'] = $this->error['warning'];
			$json['text_success'] = false;
		} else {
			$json['text_success'] = true;
			$json['error_warning'] = false;
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	} 
	protected function validatelogin() {
		$this->load->model('account/customer');
		$this->load->language('account/login');
		
		$data['text_login'] = $this->language->get('text_login');
		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
		
		if(substr(VERSION,0,3)>='3.0') { 
			if ($customer_info && !$customer_info['status']) {
				$this->error['warning'] = $this->language->get('error_approved');
			}
		} else {
			if ($customer_info && !$customer_info['approved']) {
				$this->error['warning'] = $this->language->get('error_approved');
			}
		}

		if (!$this->error) {
			if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
			}
		}

		return !$this->error;
	}
}