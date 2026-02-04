<?php
/**
	* @version [Supported opencart version 3.x.x.x.]
	* @category Webkul
	* @package Payment
	* @author [Webkul] <[<http://webkul.com/>]>
	* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
	* @license https://store.webkul.com/license.html
*/
class ControllerExtensionPaymentWkStripe extends Controller {

	private $error = array();

	public function install(){
		$this->load->model('extension/payment/wk_stripe');
		$this->model_extension_payment_wk_stripe->createTable();
	}

	public function index() {

		$this->language->load('extension/payment/wk_stripe');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            foreach ($this->request->post['payment_wk_stripe_connect_description'] as $key => $description) {
				$this->request->post['payment_wk_stripe_connect_description'][$key] = str_replace('script','p', $description);
			}
			$this->model_setting_setting->editSetting('payment_wk_stripe', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true));
		}

		$data['entry_tran_description_placeholder'] = $this->language->get('entry_tran_description_placeholder');

		$err_arr = array(
		  'warning',
		  'username',
		  'password',
		  'payment_wk_stripe_title',
		  'payment_wk_stripe_test_key',
		  'payment_wk_stripe_test_publish_key',
		  'payment_wk_stripe_live_key',
		  'payment_wk_stripe_live_publish_key',
		  'payment_wk_stripe_min',
			'payment_wk_stripe_webhook_secret'
		);

		foreach ($err_arr as $key => $value) {
			if (isset($this->error[$value])) {
				$data['error_' . $value] = $this->error[$value];
			} else {
				$data['error_' . $value] = '';
			}
		}

		if (isset($this->session->data['success'])) {
	    		$data['success'] = $this->session->data['success'];

		        unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false,
		);

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
            'separator' => ' :: ',
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/wk_stripe', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: ',
        );

        $data['action'] = $this->url->link('extension/payment/wk_stripe', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=payment', true);

		$data['token'] = $this->session->data['user_token'];

		$this->load->model('tool/image');

		if (isset($this->request->post['wk_stripe_logo']) && is_file(DIR_IMAGE . $this->request->post['wk_stripe_logo'])) {
			$data['wk_stripe_img'] = $this->model_tool_image->resize($this->request->post['wk_stripe_logo'], 100, 100);
		} elseif ($this->config->get('wk_stripe_logo') && is_file(DIR_IMAGE . $this->config->get('wk_stripe_logo'))) {
			$data['wk_stripe_img'] = $this->model_tool_image->resize($this->config->get('wk_stripe_logo'), 100, 100);
		} else {
			$data['wk_stripe_img'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$config = array(
						'payment_wk_stripe_status',
						'payment_wk_stripe_sort_order',
						'payment_wk_stripe_success_status',
						'payment_wk_stripe_refund_status',
						'payment_wk_stripe_zip_status',
						'payment_wk_stripe_addess_status',
						'payment_wk_stripe_cvc_status',
						'payment_wk_stripe_min',
						'payment_wk_stripe_max',
						'payment_wk_stripe_test_key',
						'payment_wk_stripe_test_publish_key',
						'payment_wk_stripe_live_key',
						'payment_wk_stripe_live_publish_key',
						'payment_wk_stripe_webhook',
						'payment_wk_stripe_mode',
						'payment_wk_stripe_customer_discount',
						'payment_wk_stripe_type',
						'payment_wk_stripe_embed',
						'payment_wk_stripe_shipping',
						'payment_wk_stripe_billing',
						'payment_wk_stripe_subscription',
						'payment_wk_stripe_subscription_allow',
						'payment_wk_stripe_connect_test_client_id',
						'payment_wk_stripe_connect_live_client_id',
						'payment_wk_stripe_connect_landing',
						'payment_wk_stripe_connect_type',
						'payment_wk_stripe_work',
						'payment_wk_stripe_transfer',
						'payment_wk_stripe_webhook_status',
						'payment_wk_stripe_webhook_secret',
						);

		foreach($config as $value){
			if (isset($this->request->post[$value])) {
				$data[$value] = $this->request->post[$value];
			} else {
				$data[$value] = $this->config->get($value);
			}
		}

		$config_array = array( 'payment_wk_stripe_title',
							   'payment_wk_stripe_zone',
							   'payment_wk_stripe_customergroups',
							   'payment_wk_stripe_currency',
								 'payment_wk_stripe_connect_title',
							   'payment_wk_stripe_connect_description',
							 );

		foreach($config_array as $value){
			if (isset($this->request->post[$value])) {
				$data[$value] = $this->request->post[$value];
			} elseif($this->config->get($value)) {
				$data[$value] = $this->config->get($value);
			}else{
				$data[$value] = array();
			}
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (version_compare(VERSION, '2.1', '>=')) {
			$this->load->model('customer/customer_group');
			$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		} else {
			$this->load->model('sale/customer_group');
			$data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		}

		$data['webhook_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG . 'index.php?route=extension/payment/wk_stripe/webhook' : HTTP_CATALOG . 'index.php?route=extension/payment/wk_stripe/webhook';
		$data['text_webhook_info'] = sprintf($this->language->get('text_webhook_info'), $this->url->link('stripe/log', 'user_token=' . $this->session->data['user_token'], true));



		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();
		$data['conn_url'] = HTTP_CATALOG;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/wk_stripe', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/wk_stripe')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		 $tempArray = array('curl_init', 'json_decode', 'mb_detect_encoding');

	        foreach ($tempArray as $key => $val) {
	            if (!function_exists($val)) {
	                $this->error['warning'] = $this->language->get('error_' . $val);
	                return false;
	            }

		if(isset($this->request->post['payment_wk_stripe_title'])) {
			foreach ($this->request->post['payment_wk_stripe_title'] as $language_id => $value) {
				if(trim($value) == '') {
					$this->error['payment_wk_stripe_title'][$language_id] = $this->language->get('error_title');
				}
			}
		}

		if(isset($this->request->post['payment_wk_stripe_mode']) && $this->request->post['payment_wk_stripe_mode']) {

			if(isset($this->request->post['payment_wk_stripe_live_key']) && trim($this->request->post['payment_wk_stripe_live_key']) == '') {
				$this->error['payment_wk_stripe_live_key'] = $this->language->get('error_payment_wk_stripe_live_key');
			}

			if(isset($this->request->post['payment_wk_stripe_live_publish_key']) && trim($this->request->post['payment_wk_stripe_live_publish_key']) == '') {
				$this->error['payment_wk_stripe_live_publish_key'] = $this->language->get('error_payment_wk_stripe_live_publish_key');
			}

		} else {
			if(isset($this->request->post['payment_wk_stripe_test_key']) && trim($this->request->post['payment_wk_stripe_test_key']) == '') {
				$this->error['payment_wk_stripe_test_key'] = $this->language->get('error_payment_wk_stripe_test_key');
			}

			if(isset($this->request->post['payment_wk_stripe_test_publish_key']) && trim($this->request->post['payment_wk_stripe_test_publish_key']) == '') {
				$this->error['payment_wk_stripe_test_publish_key'] = $this->language->get('error_payment_wk_stripe_test_publish_key');
			}
		}
		if(isset($this->request->post['payment_wk_stripe_min']) && isset($this->request->post['payment_wk_stripe_max']) && $this->request->post['payment_wk_stripe_min'] > $this->request->post['payment_wk_stripe_max']){
			$this->error['payment_wk_stripe_min'] = $this->language->get('error_payment_wk_stripe_min');
		}

		if ($this->request->post['payment_wk_stripe_webhook_status'] && (!isset($this->request->post['payment_wk_stripe_webhook_secret']) || !trim($this->request->post['payment_wk_stripe_webhook_secret']))) {
				$this->error['payment_wk_stripe_webhook_secret'] = $this->language->get('error_payment_wk_stripe_webhook_secret');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}

	public function returnStripe () {
		$this->load->language('payment/wk_stripe');
		if(isset($this->request->get['order_id']) AND $this->request->get['order_id'] AND $this->validate()){
			$this->load->model('extension/payment/wk_stripe');
			$getData = $this->model_extension_payment_wk_stripe->getOrderData($this->request->get['order_id']);
			if($getData){
				require_once(DIR_IMAGE. '../stripe-lib/Stripe.php');
				$stripe_keys = $this->getKeys();
				\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
		                //\Stripe\Stripe::setApiVersion("2020-08-27");
		                \Stripe\Stripe::setAppInfo(
		                    "Webkul Opencart Stripe Plugin",
		                    "https://store.webkul.com/Opencart-Stripe-Payment-Gateway.html"
		                );
				try {
					$ch = Stripe_Charge::retrieve($getData['stripe_id']);
					$re = $ch->refunds->create();
				}catch(Stripe_Error $e) {
					$error = $e->getMessage();
					$this->log->write('STRIPE_PAYMENRT :: Charge failed ' . $error);
				}

				if(!isset($error)){
					$this->load->model('sale/order');
					$this->model_sale_order->addOrderHistory($this->request->get['order_id'],array('order_status_id' => $this->config->get('wk_stripe_refund_status'),'comment' => '<b>Refunded</b>','notify' => false ));
					$this->session->data['success'] = $this->language->get('text_refund');
					$this->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
				}else{
					$this->session->data['error_stripe'] = $error;
					$this->redirect($this->url->link('sale/order/info&order_id='.$this->request->get['order_id'], 'user_token=' . $this->session->data['user_token'], true));
				}
			}
		}else{
			$this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	private function getKeys() {

		$testmode = $this->config->get('wk_stripe_mode');

		if($testmode)
			$stripe_keys = array(
			  "secret_key"      => $this->config->get('wk_stripe_live_key'),
			  "publishable_key" => $this->config->get('wk_stripe_live_publish_key')
			);
		else
			$stripe_keys = array(
			  "secret_key"      => $this->config->get('wk_stripe_test_key'),
			  "publishable_key" => $this->config->get('wk_stripe_test_publish_key')
			);

		return $stripe_keys;
	}

	 }
?>
