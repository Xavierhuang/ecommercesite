<?php

/**
* @version [Supported opencart version 3.x.x.x.]
* @category Webkul
* @package Opencart Markace Stripe Payment
* @author [Webkul] <[<http://webkul.com/>]>
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

class ControllerAccountCustomerpartnerStripeConnect extends Controller {

	private $error = array();

  	public function index() {

  		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/stripeConnect', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || !$this->config->get('payment_wk_stripe_status') || !$this->config->get('module_marketplace_status'))
			$this->response->redirect($this->url->link('account/account'));

    	$this->getlist();
  	}

  	private function getlist() {

		$data = array();
		$data = array_merge($data, $this->language->load('extension/payment/wk_stripe'));

	//	$this->load->language('extension/payment/wk_stripe');
		$this->document->setTitle($this->language->get('heading_title_connect'));

		$this->load->model('extension/payment/wk_stripe');

  		$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_home'],
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title_connect'),
			'href'      => $this->url->link('account/customerpartner/stripeConnect'),
        	'separator' => $this->language->get('text_separator')
      	);

		$lang_array = array('heading_title_connect',
							'text_connect_title',
							'text_connect_description',
							'text_connect_update_description',

							'button_back',
							'button_save',
							);

		foreach($lang_array as $language){
			$data[$language] = $this->language->get($language);
		}

		if($this->config->get('payment_wk_stripe_connect_type'))
			$client_id = $this->config->get('payment_wk_stripe_connect_live_client_id');
		else
			$client_id = $this->config->get('payment_wk_stripe_connect_test_client_id');

		$data['back'] = $this->url->link('account/account');
		$data['stripeConnectUrl'] = 'https://connect.stripe.com/oauth/authorize?response_type=code&client_id='.$client_id.'&stripe_landing='.$this->config->get('payment_wk_stripe_connect_landing').'&scope=read_write&redirect_uri=' . $this->url->link('extension/payment/wk_stripe/stripeConnect','', true);

		$stripeTitle = $this->config->get('payment_wk_stripe_connect_title');
		if(isset($stripeTitle[$this->config->get('config_language_id')]) AND $stripeTitle[$this->config->get('config_language_id')])
			$data['text_connect_title'] = $stripeTitle[$this->config->get('config_language_id')];

		$this->load->model('extension/payment/wk_stripe');
		$chkIsConnected = $this->model_extension_payment_wk_stripe->isConnected();

		if(!$chkIsConnected){

			$stripeDescription = $this->config->get('payment_wk_stripe_connect_description');
			if(isset($stripeDescription[$this->config->get('config_language_id')]) AND $stripeDescription[$this->config->get('config_language_id')]){
				$text = $stripeDescription[$this->config->get('config_language_id')];
				$text = trim(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
				$data['text_connect_description'] = $text;
			}
		}else{
			$data['stripeConnectUrl'] = 'https://dashboard.stripe.com/account/transfers';
			$data['text_connect_description'] = $this->language->get('text_connect_update_description');
		}


		$data['success'] = '';

		if(isset($this->session->data['connect_success'])){
			$data['success'] = $this->language->get('text_connect_success');
			unset($this->session->data['connect_success']);

		}elseif (isset($this->session->data['error'])) {
			if($this->session->data['error'])
				$this->error['warning'] = $this->session->data['error'];
			else
				$this->error['warning'] = $this->language->get('error_connect');
			unset($this->session->data['error']);
		}

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$template = 'account/customerpartner/stripeConnect';

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view($template, $data));
  	}

}
?>
