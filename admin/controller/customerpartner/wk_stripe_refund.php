<?php

/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Stripe Payment
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerCustomerpartnerWkStripeRefund extends Controller {
	public $balance_transactions;
	private $data = array();

	public function index() {

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$this->load->language("customerpartner/striperefund");
			$order_id = $this->request->post['order_id_num'];

			$this->load->model('extension/payment/wk_stripe');
			//check if any product is selected
			if (isset($_POST['selected'])) {

				foreach ($_POST['selected'] as $value) {

					//Get the refund status
					$status = $this->model_extension_payment_wk_stripe->getSellerRefundStatus($order_id, (int)$value);
					
					if(!empty($status) && $status['refund_status'] == 0) {

						$reverse_amt = $refund_amt = ($status['seller_amount']/100);

						if($reverse_amt > 0) {

							$balance_transactions = $this->model_extension_payment_wk_stripe->getTransaction($order_id, (int)$value);

							//for connected seller or not
							if (isset($balance_transactions[0]['transfer_id']) && $balance_transactions[0]['transfer_id']) {
								//reverse transfer and refund amount
								$this->stripeRefund($reverse_amt, $balance_transactions, (int)$value, $refund_amt);
								$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, $value);
							} else {
								//for non connected seller
								require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');
	
								$stripe_keys = $this->getKeys();
								\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
								// \Stripe\Stripe::setApiVersion("2020-08-27");
								\Stripe\Stripe::setAppInfo(
									"Webkul Opencart Marketplace Stripe Plugin",
									"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
								);
	
								$chargeid = $this->model_extension_payment_wk_stripe->getChargeid($order_id);
	
								$re = \Stripe\Refund::create(array(
									'charge' => $chargeid['stripe_id'],
									'amount'   => (int)($reverse_amt * 100)
								));
	
								$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, $value);
							}
						} else {
							$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, $value);
						}

					} else {
						$this->session->data['error_warning'] = $this->language->get('error');
					}

				}

				if (!isset($this->session->data['error_warning'])) {
					$this->session->data['success'] = $this->language->get('success');
				}
				$this->response->redirect($this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			 }else {
				$this->session->data['error_warning'] = $this->language->get('select_error');
				$this->response->redirect($this->url->link('customerpartner/striperefund/info&order_id=' . $order_id, 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
		} else {
			$this->session->data['error_warning'] = $this->language->get('select_error');
			$this->response->redirect($this->url->link('customerpartner/striperefund/info&order_id=' . $order_id, 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}
	}

	public function getKeys()
	{

		$testmode = $this->config->get('payment_wk_stripe_mode');

		if ($testmode)
			$stripe_keys = array(
				"secret_key"      => $this->config->get('payment_wk_stripe_live_key'),
				"publishable_key" => $this->config->get('payment_wk_stripe_live_publish_key')
			);
		else
			$stripe_keys = array(
				"secret_key"      => $this->config->get('payment_wk_stripe_test_key'),
				"publishable_key" => $this->config->get('payment_wk_stripe_test_publish_key')
			);

		return $stripe_keys;
	}

	public function stripeRefund($reverse_amt, $balance_transactions, $i, $refund_amt)
	{
		require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');
		$stripe_keys = $this->getKeys();
		\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
		\Stripe\Stripe::setApiVersion("2019-05-16");
		\Stripe\Stripe::setAppInfo(
			"Webkul Opencart Marketplace Stripe Plugin",
			"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
		);

		$tr = \Stripe\Transfer::retrieve($balance_transactions[0]['transfer_id']);
		$tranfer_remain = $tr->toArray(true);

		//To check whether the reversal amount of tranfer is less than tranfered amount
		if ($reverse_amt * 100 > $tranfer_remain['amount'] - $tranfer_remain['amount_reversed'])
			$reverse_amt = ($tranfer_remain['amount'] - $tranfer_remain['amount_reversed']) / 100;

		if($reverse_amt > 0){
			$transfer_reverse = \Stripe\Transfer::createReversal(
				$balance_transactions[0]['transfer_id'],
				array(
					'amount' => (int)($reverse_amt * 100),
				)
			);

			$re = \Stripe\Refund::create(array(
				'charge' => $balance_transactions[0]['charge_id'],
				'amount'   => (int)($reverse_amt * 100)
			));
		}

	}

}