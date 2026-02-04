<?php

/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Stripe Payment
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class ControllerCustomerpartnerstriperefund extends Controller
{

	private $error = array();
	private $data = array();

	public function index() {
		$this->getlist();
	}

	public function getlist() {

		$data = array_merge($data = array(), $this->load->language('customerpartner/striperefund'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$filter_array = array(
			'filter_id',
			'filter_name',
			'filter_details',
			'filter_date',
			'filter_amount',
			'page',
			'sort',
			'order',
			'start',
			'limit'
		);

		if (isset($this->request->get['filter_id'])) {
			$filter_id = $this->request->get['filter_id'];
		} else {
			$filter_id = null;
		}
		if (isset($this->request->get['filter_amount'])) {
			$filter_amount = $this->request->get['filter_amount'];
		} else {
			$filter_amount = null;
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ct.id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		$url = '';

		foreach ($filter_array as $unsetKey => $key) {

			if (isset($this->request->get[$key])) {
				$filter_array[$key] = $this->request->get[$key];
			} else {
				if ($key == 'page')
					$filter_array[$key] = 1;
				elseif ($key == 'sort')
					$filter_array[$key] = 'ct.id';
				elseif ($key == 'order')
					$filter_array[$key] = 'DESC';
				elseif ($key == 'start')
					$filter_array[$key] = ($filter_array['page'] - 1) * $this->config->get('config_limit_admin');
				elseif ($key == 'limit')
					$filter_array[$key] = $this->config->get('config_limit_admin');
				else
					$filter_array[$key] = null;
			}
			unset($filter_array[$unsetKey]);
			if (isset($this->request->get[$key])) {
				if ($key == 'filter_name' || $key == 'filter_details' || $key == 'filter_date')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				else
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$data['user_token'] = $this->session->data['user_token'];

		// if (isset($this->request->get['page'])) {
		// 	$url .= '&page=' . $this->request->get['page'];
		// }

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		// $results = $this->model_sale_order->getOrders();
		$this->load->model('extension/payment/wk_stripe');
		$product_total = $this->model_extension_payment_wk_stripe->getTotal($filter_array);

		$results = $this->model_extension_payment_wk_stripe->getStripeOrders($filter_array);

		//$results = $this->model_extension_payment_wk_stripe->getRefundableOrders($result);

		$data['transactions'] = array();

		foreach ($results as $result) {

			$data['transactions'][] = array(
				'selected' => False,
				'id' => $result['order_id'],
				'name' => $result['firstname']. " " .$result['lastname'],
				'value' => $this->currency->format($result['total'], $result['currency_code'],$result['currency_value']),
				'details' => "complete",
				'date' => $result['date_added'],
				'order_id' => $this->url->link('customerpartner/striperefund/info&order_id=' . $result['order_id'], 'user_token=' . $this->session->data['user_token'] . $url, 'SSL')
				// 'order_id' =>$this->url->link('customerpartner/striperefund/info&order_id='.$result['order_id'],'user_token=' . $this->session->data['user_token'],'SSL')
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (!isset($this->error['warning']) && isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		$url = '';

		foreach ($filter_array as $key => $value) {
			if (isset($this->request->get[$key])) {
				
				if ($key == 'filter_name' || $key == 'filter_details' || $key == 'filter_date')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif ($key != 'start' and $key != 'limit' and $key != 'sort')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_name'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . '&sort=ct.firstname' . $url, 'SSL');
		$data['sort_id'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . '&sort=ct.id' . $url, 'SSL');
		$data['sort_date'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . '&sort=ct.date_added' . $url, 'SSL');
		$data['sort_amount'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . '&sort=ct.amount' . $url, 'SSL');
		$data['sort_details'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . '&sort=ct.details' . $url, 'SSL');
		$url = '';

		foreach ($filter_array as $key => $value) {

			if (isset($this->request->get[$key])) {
				if ($key == 'filter_name' || $key == 'filter_details' || $key == 'filter_date')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif ($key != 'page')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $filter_array['page'];
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$url = '';

		foreach ($filter_array as $key => $value) {
			if ($key != 'start' and $key != 'end')
				$data[$key] = $value;
		}
		// if(isset($this->request->get['action']) && $this->request->get['action'] == '')
		// $data['filter_name'] = $filter_name;
		$data['filter_id'] = $filter_id;
		$data['filter_amount'] = $filter_amount;
		$data['filter_name'] = $filter_name;
		$data['filter_date'] = $filter_date;

		$data['sort'] = $sort;
		$data['order'] = strtolower($order);
	
		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');
		$data['column_left'] = $this->load->controller('common/column_left');
		$this->response->setOutput($this->load->view('customerpartner/refund', $data));
	}

	private function validate()
	{

		if (!$this->user->hasPermission('modify', 'customerpartner/striperefund')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function info()
	{
		$data = array();
		$data = array_merge($data, $this->load->language('customerpartner/striperefund'));

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		$order_id = $this->request->get['order_id'];
		$this->load->model('customerpartner/customerpartner');
		$this->load->model('sale/order');

		//Get order information
		$results = $this->model_sale_order->getOrder((int)$order_id);

		if (empty($results)) {
			$this->response->redirect($this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'], true));
		}

		$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

		$data['product'] = $this->language->get('entry_product');
		$data['model'] = $this->language->get('entry_model');
		$data['quantity'] = $this->language->get('entry_quantity');
		$data['unit'] = $this->language->get('entry_unit');
		$data['total'] = $this->language->get('entry_total');
		$data['order'] = $this->language->get('entry_order');
		$data['payment'] = $this->language->get('entry_payment');
		$data['shipping'] = $this->language->get('entry_shipping');
		$data['admin'] = $this->language->get('entry_admin');
		$data['seller'] = $this->language->get('entry_seller');
		$data['by'] = $this->language->get('entry_by');
		$data['refund_status'] = $this->language->get('entry_refund_status');
		$data['refunded'] = $this->language->get('entry_refunded');
		$data['refund'] = $this->language->get('entry_refund');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['heading_title'] = $this->language->get('heading_title');

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		//Getting the order info which pay through stripe
		$this->load->model('extension/payment/wk_stripe');

		//Getting the refund status of each product

		$data['sellers'] = array();		

		foreach ($products as $key => $product) {

			$getSellerInformation = $this->model_extension_payment_wk_stripe->getinfo((int)$order_id, $product['product_id']);

			$seller_id = 0;
			$seller_name = 'Admin';

			if($getSellerInformation && !empty($getSellerInformation)) {
				
				$seller_id = $getSellerInformation['customer_id'];

				$sellerInfo = $this->model_extension_payment_wk_stripe->getName($seller_id);

				$seller_name = ucfirst($sellerInfo['name']);
			}

			$data['sellers'][$seller_id]['seller_id'] = $seller_id;
			$data['sellers'][$seller_id]['seller_name'] = $seller_name;
			$data['sellers'][$seller_id]['order_id'] = $order_id;
				
			$data['sellers'][$seller_id]['refund_amt'] = 0;

			if(!isset($data['sellers'][$seller_id]['products'])) {
				$data['sellers'][$seller_id]['products'] = array();
			}		

			$refund_status = $this->model_extension_payment_wk_stripe->getRefundStatus((int)$order_id, $product['product_id']);

			$info = $this->model_extension_payment_wk_stripe->getinfo((int)$order_id,$product['product_id']);				

			$data['sellers'][$seller_id]['products'][$product['product_id']] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'quantity'   => $product['quantity'],
				'price'		 => $this->currency->format($product['price'], $results['currency_code'],$results['currency_value']),
				'total'		 => $this->currency->format($product['price'] + $product['tax'],$results['currency_code'],$results['currency_value']),
				'refund_status' => (isset($refund_status['status'])) ? $refund_status['status'] : 1,
				'customer' =>  (isset($info['admin'])) ? $this->currency->format($info['customer'], $results['currency_code'],$results['currency_value']) : $this->currency->format(0, $results['currency_code'],$results['currency_value']),
				'admin' =>  (isset($info['admin'])) ? $this->currency->format($info['admin'], $results['currency_code'],$results['currency_value']) : $this->currency->format(0, $results['currency_code'],$results['currency_value']),
			);

			$infostripe = $this->model_extension_payment_wk_stripe->getinfostripe($order_id,$seller_id);
			

			if($infostripe) {
				$data['sellers'][$seller_id]['refund_amt'] = $this->currency->format($infostripe['seller_amount'] / 100, $results['currency_code'],$results['currency_value']);
				$data['sellers'][$seller_id]['refund_status'] = $infostripe['refund_status'];
			}			
		}

		$total = array();

		$data['results'] = $results;

		$data['action'] = $this->url->link('customerpartner/wk_stripe_refund', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['back'] = $this->url->link('customerpartner/striperefund', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');
		$data['column_left'] = $this->load->controller('common/column_left');

		$this->response->setOutput($this->load->view('customerpartner/transaction_info_form', $data));
	}
	public function autocomplete() {

		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email'])) {

			$this->load->model('extension/payment/wk_stripe');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_view'])) {
				$filter_view = $this->request->get['filter_view'];
			} else {
				$filter_view = 0 ;
			}

			if (isset($this->request->get['filter_category'])) {
				$filter_category = $this->request->get['filter_category'];
			} else {
				$filter_category = 0 ;
			}

			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 20;
			}

			$data = array(
				'filter_name'         => $filter_name,
				'filter_all'         => $filter_view,
				'filter_category'         => $filter_category,
				'filter_email'  	  => $filter_email,
				'page'							=> 1,
				'start'               => 0,
				'limit'               => $limit
			);

			$results = $this->model_extension_payment_wk_stripe->getStripeOrdersname($data);

			foreach ($results as $result) {

				$option_data = array();

				$json[] = array(
					'id' 		 => $result['customer_id'],
					'name'       => strip_tags(html_entity_decode($result['firstname'] . " " . $result['lastname'], ENT_QUOTES, 'UTF-8')),
					'email'      => $result['email'],
				);
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}
