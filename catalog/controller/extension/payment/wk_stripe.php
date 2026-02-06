<?php

/**
 * @version [Supported opencart version 3.x.x.x.]
 * @category Webkul
 * @package Opencart Marketplace Stripe Payment
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

class ControllerExtensionPaymentWkStripe extends Controller
{

	private $data = array();

	public function index()
	{

		if(isset($this->session->data['stripe_session_id'])) {
			unset($this->session->data['stripe_session_id']);
		}

		$this->language->load('extension/payment/wk_stripe');

		$lang = $this->config->get('config_language_id');
		$stripe_currency = $this->config->get('payment_wk_stripe_currency');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_testmode'] = $this->language->get('text_testmode');

		$this->data['testmode'] = $this->config->get('payment_wk_stripe_mode');

		$stripe_keys = $this->getKeys();

		$this->load->model('tool/image');

		$this->data['stripe_keys'] = $stripe_keys;

		$this->data['action'] = $this->url->link('extension/payment/wk_stripe/stripePayMp');

		//if 1 then stripe popup else not tpl
		$payment_type = $this->config->get('payment_wk_stripe_type');

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->data['enable_shipping'] = $this->config->get('payment_wk_stripe_shipping');
		$this->data['enable_zip'] = false;
		$this->data['enable_email'] = $this->customer->getEmail();
		$this->data['cart_currency'] = (isset($stripe_currency[$order_info['currency_code']]) and $stripe_currency[$order_info['currency_code']]) ? $stripe_currency[$order_info['currency_code']] : 'USD';
		$this->data['cart_amount'] = (int)(100 * $this->currency->format($order_info['total'], $this->data['cart_currency'], '', false));

		if($this->config->get('payment_wk_stripe_mode') && !$this->config->get('config_seo_url')) {

			$error_message = $this->language->get('error_ssl');

		} else {

		$products = $this->cart->getproducts();

		$this->load->model('tool/image');

        $this->data['products'] = array();

        if(isset($this->session->data['reward']) && $this->session->data['reward']) {
			$points_total = 0;

			foreach ($products as $product) {
				if ($product['points']) {
					$points_total += $product['points'];
				}
			}
		}

		foreach ($products as $key => $value) {

			if($value['price'] > 0){

				if(isset($this->session->data['reward']) && $this->session->data['reward']) {
					$points = $this->customer->getRewardPoints();
					if ($value['points']) {
						$discount = $value['total'] * ($this->session->data['reward'] / $points_total);
						$value['price'] -= ($discount / $value['quantity']);
					}
				}

				$this->data['products'][] =  array(
					'amount' => (int)($value['price'] * 100),
					'currency' => $this->data['cart_currency'],
					'name' => $value['name'],
					'quantity' => $value['quantity'],

					'images' => ($value['image']) ? array($this->model_tool_image->resize($value['image'], 100, 100),) : array(),
				);
		    }
		}

		$order_total = $this->model_checkout_order->getOrderTotals($this->session->data['order_id']);

		$sub_total = 0;
		$coupon_price = 0;
		$is_coupon = false;
		$coupon_title = '';

		foreach ($order_total as $key => $total) {
			if ($total['code'] != 'sub_total' && $total['code'] != 'total' && $total['code'] != 'coupon' && $total['code'] != 'voucher' && $total['code'] != 'credit') {

				$total['value'] = $this->currency->format($total['value'], $this->data['cart_currency'], '', false);

               if($total['value'] > 0){
				$this->data['products'][] = array(
					'amount' => (int)($total['value'] * 100),
					'currency' => $this->data['cart_currency'],
					"description" => $total['title'],
					'name'        => $total['code'],
					'quantity'  => 1,
				);
			   }
			} elseif($total['code'] == 'sub_total') {
				$sub_total = (int)($total['value'] * 100);
			} elseif ($total['code'] == 'coupon' || $total['code'] == 'voucher' || $total['code'] == 'credit') {
				$coupon_price += $total['value'];
				$is_coupon = true;

				if(isset($this->session->data['coupon']) && $this->session->data['coupon'] && isset($this->session->data['voucher']) && $this->session->data['voucher']) {
					$coupon_title = "Mixed Coupon";
				} else {
					$coupon_title .= $total['title'];
				}
			}
		}
		if(isset($this->session->data['shipping_address'])) {
			$billing_address = array(
				'address' => array(
					'line1' => $this->session->data['shipping_address']['address_1'],
					'city' => $this->session->data['shipping_address']['city'],
					'country' => $this->session->data['shipping_address']['country'],
					'line2' => $this->session->data['shipping_address']['address_2'],
					'postal_code' => $this->session->data['shipping_address']['postcode'],
					'state' => $this->session->data['shipping_address']['zone'],
				),
				'name' => $this->session->data['shipping_address']['firstname'] . ' ' . $this->session->data['shipping_address']['lastname'],
			);
		} else {
			$billing_address = array();
		}

		require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');

		$stripe_keys = $this->getKeys();
		\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
		\Stripe\Stripe::setAppInfo(
			"Webkul Opencart Marketplace Stripe Plugin",
			"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
		);

		try {

			$checkout_coupon = '';

			if($is_coupon) {

				$coupon_price_cal = $sub_total - ($sub_total + $coupon_price);

				$stripe_coupon = \Stripe\Coupon::create([
					'name'  => $coupon_title,
					'amount_off' => (int)(100 * $this->currency->format($coupon_price_cal, $this->data['cart_currency'] , '' ,false)),
					'duration' => 'once',
					'currency' => $this->data['cart_currency'],
				]);

				if($stripe_coupon) {
					$checkout_coupon = $stripe_coupon['id'];
				}
			}


			if($checkout_coupon) {

				$stripe_session = \Stripe\Checkout\Session::create([
					'success_url' => $this->data['action'],
					'cancel_url' => $this->url->link('checkout/checkout', '', true),
					'payment_method_types' => ['card'],
					'line_items' => $this->data['products'],
					'billing_address_collection' => ($this->data['enable_shipping']) ? 'required' : 'auto',
					'customer_email' => $this->customer->getEmail(),
					'discounts' => [[
						'coupon' => $checkout_coupon,
					]],
					'payment_intent_data' => [
						'shipping' => $billing_address,
					]
				]);

				if (isset($stripe_session['id'])) {
					$this->data['session_id'] = $stripe_session['id'];
					$this->session->data['stripe_session_id'] = $stripe_session['id'];
				}
			} else {
				$stripe_session = \Stripe\Checkout\Session::create([
					'success_url' => $this->data['action'],
					'cancel_url' => $this->url->link('checkout/checkout', '', true),
					'payment_method_types' => ['card'],
					'line_items' => $this->data['products'],
					'billing_address_collection' => ($this->data['enable_shipping']) ? 'required' : 'auto',
					'customer_email' => $this->customer->getEmail(),
					'payment_intent_data' => [
						'shipping' => $billing_address,
					]
				] /*, [
					'stripe_version' => '2018-11-08; checkout_sessions_beta=v1'
				]*/ );

				if (isset($stripe_session['id'])) {
					$this->data['session_id'] = $stripe_session['id'];
					$this->session->data['stripe_session_id'] = $stripe_session['id'];
				}
			}
		} catch (\Stripe\Error\InvalidRequest $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		} catch (\Stripe\Error\Authentication $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		} catch (\Stripe\Error\Card $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		} catch (\Stripe\Error\Permission $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		} catch (\Stripe\Error\RateLimit $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		} catch (\Stripe\Error\Api $e) {
			$error = $e->getJsonBody();
			if (isset($error['error']) && $error['error']['message']) {
				$error_message = $error['error']['message'];
			}
		}
	}

		if (isset($error_message) && !isset($this->data['error_warning'])) {
			$this->data['error_warning'] = $error_message;
		} else {
			$this->data['error_warning'] = '';
		}

		return $this->load->view('extension/payment/wk_stripe', $this->data);
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

	public function getData($useMe, $order_info)
	{

		$returnMe = '';
		$lang = $this->config->get('config_language_id');

		if (isset($useMe[$lang]) and $useMe[$lang]) {
			if (strpos($useMe[$lang], '[') !== false) {
				$explodedData = explode('|', $useMe[$lang]);
				foreach ($explodedData as $key => $value) {
					if (isset($order_info[trim(str_replace(']', '', str_replace('[', '', $value)))])) {
						if (trim(str_replace(']', '', str_replace('[', '', $value))) == 'total')
							$returnMe = $returnMe . ' ' . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
						else
							$returnMe = $returnMe . ' ' . $order_info[trim(str_replace(']', '', str_replace('[', '', $value)))];
					} else {
						$returnMe = $returnMe . ' ' . trim(str_replace(']', '', str_replace('[', '', $value)));
					}
				}
			} else
				$returnMe = $useMe[$lang];
		}

		return $returnMe;
	}

	public function stripeConnectData()
	{

		$keyVal = 'qwerfghjkl;;akd;kad;ka;dka;kd';
		$result = $this->db->query("SELECT
			*,
			AES_DECRYPT(refresh_token,'" . $this->db->escape($keyVal) . "') refresh_token,
			AES_DECRYPT(publishable_key,'" . $this->db->escape($keyVal) . "') publishable_key,
			AES_DECRYPT(token,'" . $this->db->escape($keyVal) . "') token,
			AES_DECRYPT(user_id,'" . $this->db->escape($keyVal) . "') user_id
			FROM " . DB_PREFIX . "order_stripe_seller WHERE customer_id = '" . $this->customer->getId() . "'");
	}

	public function stripeConnect()
	{

		$stripe_keys = $this->getKeys();

		if ($this->config->get('payment_wk_stripe_connect_type'))
			$client_id = $this->config->get('payment_wk_stripe_connect_live_client_id');
		else
			$client_id = $this->config->get('payment_wk_stripe_connect_test_client_id');

		$client_secret = $stripe_keys['secret_key'];

		if (isset($this->request->get['code'])) { // Redirect w/ code
			$code = $this->request->get['code'];

			$token_request_body = array(
				'grant_type' => 'authorization_code',
				'client_id' => $client_id,
				'code' => $code,
				'client_secret' => $client_secret
			);

			$req = curl_init('https://connect.stripe.com/oauth/token');
			curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($req, CURLOPT_POST, true);
			curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));

			// TODO: Additional error handling
			$respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
			$resp = json_decode(curl_exec($req), true);
			curl_close($req);

			if (isset($resp['error'])) {
				$error_warning = $resp['error'] . '<br>'
					. $resp['error_description'];
			} else {
				$this->load->model('extension/payment/wk_stripe');
				$this->model_extension_payment_wk_stripe->addStripeConnect($resp);
				$this->session->data['connect_success'] = '';
				$this->response->redirect($this->url->link('account/customerpartner/stripeConnect'));
			}
		} else if (isset($this->request->get['error'])) { // Error
			$error_warning = $this->request->get['error'] . '<br>'
				. $this->request->get['error_description'];
		} else { // Show OAuth link
			$error_warning = '';
		}

		$this->session->data['error'] = $error_warning;
		$this->response->redirect($this->url->link('account/customerpartner/stripeConnect'));
	}

    public function getSubTotal($cart) {
		$total = 0;

		foreach ($cart as $product) {
			$total += $product['total'];
		}

		return $total;
	}

	public function getStoreCredit() {
		if(isset($this->session->data['order_id']) && $this->session->data['order_id']) {
			$order_total = $this->model_checkout_order->getOrderTotals($this->session->data['order_id']);
			$sub_total = 0;
			$credit_price = 0;
			foreach ($order_total as $key => $total) {
				if($total['code'] == 'sub_total') {
					$sub_total = (int)($total['value'] * 100);
				} else if($total['code'] == 'credit') {
					$credit_price = $total['value'];
				}
			}
			return $sub_total - ($sub_total + $credit_price);
		} else {
			return 0;
		}
	}

	public function sellerAdminData($cart, $zip = '', $payment = false) {

		$this->load->model('account/customerpartnerorder');

		$sub_total = SELF::getSubTotal($cart);

		$store_credit = self::getStoreCredit();

		$seller = array();

		$seller_zip = array();

		if(isset($this->session->data['reward']) && $this->session->data['reward']) {
			$points_total = 0;

			foreach ($cart as $product) {
				if ($product['points']) {
					$points_total += $product['points'];
				}
			}
		}

		if ($cart and is_array($cart))
			foreach ($cart as $product) {

				$entry = 0;

				$seller_zip = $this->db->query("SELECT a.postcode,a.customer_id,a.city,c.iso_code_2 as country,z.code as state,c2c.paypalid FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."customerpartner_to_product c2p ON (p.product_id = c2p.product_id) LEFT JOIN ".DB_PREFIX."customer cu ON(cu.customer_id = c2p.customer_id) LEFT JOIN ".DB_PREFIX."address a ON(cu.address_id = a.address_id) LEFT JOIN ".DB_PREFIX."zone z ON (a.zone_id = z.code) LEFT JOIN ".DB_PREFIX."country c ON (a.country_id = c.country_id) RIGHT JOIN " . DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = a.customer_id) WHERE p.product_id='".(int)$product['product_id']."'")->row;

				$seller_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product['product_id'] . "' ")->row;

				if ($seller_zip || $seller_info) {

					if (!$seller_zip) {
						$seller_zip['customer_id'] = $seller_info['customer_id'];
					}

					if ($this->config->get('marketplace_commission_tax')) {

						$commission_array = $this->model_account_customerpartnerorder->calculateCommission(array('product_id' => $product['product_id'], 'product_total' => $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']), $seller_zip['customer_id']);
					} else {

						$commission_array = $this->model_account_customerpartnerorder->calculateCommission(array('product_id' => $product['product_id'], 'product_total' => $product['total']), $seller_zip['customer_id']);
                    }

                    $total_price_discount = 0;
					$total_commission_discount = 0;

					if (isset($this->session->data['voucher']) && $this->session->data['voucher']) {
						$this->load->model('extension/total/voucher');

						$voucher_info = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);

						if ($voucher_info) {
							$discount = min($voucher_info['amount'], $sub_total);
						} else {
							$discount = 0;
						}
						$unit_discount = $discount / count($cart);

                    	$total_commission_discount += $unit_discount;
                        $total_price_discount +=  $unit_discount;
					}

					if (isset($this->session->data['coupon']) && $this->session->data['coupon']) {
						$this->load->model('extension/total/coupon');

						$coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);

						if($coupon_info['product'] && in_array($product['product_id'], $coupon_info['product'])) {
							if ($coupon_info['type'] == 'F') {
								$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
								$discount = $coupon_info['discount'];
                                $total_commission_discount += $discount;
                                $total_price_discount +=  ($discount / $product['quantity']);

							} elseif ($coupon_info['type'] == 'P') {
								$discount = $product['total'] / 100 * $coupon_info['discount'];

                                $total_commission_discount += $discount;
                                $total_price_discount +=  ($discount / $product['quantity']);

							}
						} else {

							if ($coupon_info['type'] == 'F') {
								$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);

								$discount = $coupon_info['discount'];
                                $unit_discount = $discount / count($cart);

                                $total_commission_discount += $unit_discount;
                                $total_price_discount +=  $unit_discount;

							} elseif ($coupon_info['type'] == 'P') {
								$discount = $product['price'] / 100 * $coupon_info['discount'];
                                $discount *= $product['quantity'];

                                $total_commission_discount += $discount;
                                $total_price_discount +=  $discount;
							}
						}
					}

					if(isset($this->session->data['reward']) && $this->session->data['reward']) {
						$points = $this->customer->getRewardPoints();
						if ($product['points']) {
							$discount = $product['total'] * ($this->session->data['reward'] / $points_total);

                            $total_commission_discount += $discount;
                            $total_price_discount +=  ($discount / $product['quantity']);
						}
					}

					if($store_credit) {
						$store_credit_discount = $store_credit / count($cart);

                    	$total_commission_discount += $store_credit_discount;
                        $total_price_discount +=  $store_credit_discount;
					}

                    if($total_commission_discount && $total_price_discount) {
						$commission_array['customer'] -= $total_commission_discount;

						if($commission_array['customer'] < 0) {
							$commission_array['customer'] = 0;
						}
						$product['total'] -= ($total_price_discount);
						if($product['total'] < 0) {
							$product['total'] = 0;
						}
						$product['price'] -= ($total_price_discount / $product['quantity']);
						if($product['price'] < 0) {
							$product['price'] = 0;
						}
                    }

					//add taxes to seller amount
					if ($this->config->get('config_tax')) {
						$commission_array['customer'] += $this->tax->getTax($product['total'], $product['tax_class_id']); //comment me
						$product['total'] = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
					} // after me


					if ($seller) {
						foreach ($seller as $index => $sellers) {
							if ($sellers['seller'] == $seller_zip['customer_id']) {
								$seller[$index]['name'] = $sellers['name'] . ', ' . $product['name'];
								$seller[$index]['total'] = $sellers['total'] + $product['total'];
								$seller[$index]['price'] = (float)$commission_array['customer'] + (float)$sellers['price'];
								$entry = 1;
							}
						}
						if ($entry == 0) {
							$seller[$seller_zip['customer_id']] = array(
								'seller' => $seller_zip['customer_id'],
								'name' => $product['name'],
								'price' => $commission_array['customer'],
								'total' => $product['total'],
							);
						}
					} else {
						$seller[$seller_zip['customer_id']] = array(
							'seller' => $seller_zip['customer_id'],
							'name' => $product['name'],
							'price' => $commission_array['customer'],
							'total' => $product['total'],
						);
					}

					//admin -> if exists seller
					if ($payment) {
						foreach ($seller as $index => $sellers) {
							if ($sellers['seller'] == 'Admin') {
								$seller[$index]['price'] = (float)$sellers['price'] + (float)$commission_array['commission'];
								// $seller[$index]['total'] = (float)$sellers['total'] + (float)$commission_array['commission'];
								$seller[$index]['name'] = $sellers['name'] . ', Commission';
								$entry = 1;
							}
						}
						if ($entry == 0) {
							$seller[] = array(
								'seller' => 'Admin',
								'name' => 'Commission',
								'price' => (float)$commission_array['commission'],
								'total' => 0,
							);
						}
					}
				} else {

					$total_price_discount = 0;
					$total_commission_discount = 0;

					if (isset($this->session->data['voucher']) && $this->session->data['voucher']) {
						$this->load->model('extension/total/voucher');

						$voucher_info = $this->model_extension_total_voucher->getVoucher($this->session->data['voucher']);

						if ($voucher_info) {
							$discount = min($voucher_info['amount'], $sub_total);
						} else {
							$discount = 0;
						}
						$unit_discount = $discount / count($cart);

                    	$total_commission_discount += $unit_discount;
                        $total_price_discount +=  $unit_discount;
					}

					if (isset($this->session->data['coupon']) && $this->session->data['coupon']) {
						$this->load->model('extension/total/coupon');

						$coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);

						if($coupon_info['product'] && in_array($product['product_id'], $coupon_info['product'])) {
							if ($coupon_info['type'] == 'F') {
								$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
								$discount = $coupon_info['discount'];
                                $total_commission_discount += $discount;
                                $total_price_discount +=  ($discount / $product['quantity']);

							} elseif ($coupon_info['type'] == 'P') {
								$discount = $product['total'] / 100 * $coupon_info['discount'];

                                $total_commission_discount += $discount;
                                $total_price_discount +=  ($discount / $product['quantity']);

							}
						} else {

							if ($coupon_info['type'] == 'F') {
								$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);

								echo $coupon_info['discount'];
								$discount = $coupon_info['discount'];
                                $unit_discount = $discount / count($cart);

                                $total_commission_discount += $unit_discount;
                                $total_price_discount +=  $unit_discount;

							} elseif ($coupon_info['type'] == 'P') {
								$discount = $product['price'] / 100 * $coupon_info['discount'];
                                $discount *= $product['quantity'];

                                $total_commission_discount += $discount;
                                $total_price_discount +=  $discount;
							}
						}
					}

					if(isset($this->session->data['reward']) && $this->session->data['reward']) {
						$points = $this->customer->getRewardPoints();
						if ($product['points']) {
							$discount = $product['total'] * ($this->session->data['reward'] / $points_total);

                            $total_commission_discount += $discount;
                            $total_price_discount +=  ($discount / $product['quantity']);
						}
					}

					if($store_credit) {
						$store_credit_discount = $store_credit / count($cart);

                    	$total_commission_discount += $store_credit_discount;
                        $total_price_discount +=  $store_credit_discount;
					}

                    if($total_commission_discount && $total_price_discount) {
						$product['total'] -= ($total_price_discount);
						if($product['total'] < 0) {
							$product['total'] = 0;
						}
						$product['price'] -= ($total_price_discount / $product['quantity']);
						if($product['price'] < 0) {
							$product['price'] = 0;
						}
                    }

					//add taxes to seller amount
					if ($this->config->get('config_tax'))
						$product['total'] = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
					// $product['total'] += $this->tax->getTax($product['total'], $product['tax_class_id']);

					if ($seller) {
						foreach ($seller as $index => $sellers) {
							if ($sellers['seller'] == 'Admin') {
								$seller[$index]['total'] = $sellers['total'] + $product['total'];
								$seller[$index]['name'] = $sellers['name'] . ', ' . $product['name'];
								$seller[$index]['price'] = (float)$sellers['price'] + (float)$product['total'];
								$entry = 1;
							}
						}
						if ($entry == 0) {
							$zipCode = substr($this->config->get($zip), 0, 8);
							$seller[] = array(
								'seller' => 'Admin',
								'name' => $product['name'],
								'price' => $product['total'],
								'total' => $product['total'],
							);
						}
					} else {
						$seller[] = array(
							'seller' => 'Admin',
							'name' => $product['name'],
							'price' => $product['total'],
							'total' => $product['total'],
						);
					}
				}
			}

		return $seller;
	}

	public function stripePayMp()
	{
		$this->language->load('extension/payment/wk_stripe');

		if ((isset($this->session->data['stripe_session_id']) && $this->session->data['stripe_session_id']) && isset($this->session->data['order_id'])) {

			$order_id = $this->session->data['order_id'];

			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			$total_shipping = 0;

			if (isset($this->session->data['shipping_method'])) {
				$total_shipping = $this->tax->calculate($this->session->data['shipping_method']['cost'],$this->config->get('flat_tax_class_id'),$this->config->get('config_tax'));
			}

			$stripe_currency = $this->config->get('payment_wk_stripe_currency');

			$cart_currency = (isset($stripe_currency[$order_info['currency_code']]) AND $stripe_currency[$order_info['currency_code']]) ? $stripe_currency[$order_info['currency_code']]: 'USD';

			require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');

			$stripe_keys = $this->getKeys();
			\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
			\Stripe\Stripe::setAppInfo(
				"Webkul Opencart Marketplace Stripe Plugin",
				"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
			);

			$error_message = '';

			try {

				// \Stripe\Stripe::setApiVersion("2019-05-16; checkout_sessions_beta=v1");

				$stripe_session = \Stripe\Checkout\Session::retrieve($this->session->data['stripe_session_id']);

				if (isset($stripe_session['payment_intent'])) {

					$this->load->model('extension/payment/wk_stripe');

					$payment_intent = \Stripe\PaymentIntent::retrieve($stripe_session['payment_intent']);
					// echo "<pre>";
					// print_r($payment_intent);
					// echo "</pre>";
					$products = $this->cart->getProducts();
					$this->load->model('account/customerpartnerorder');
					$this->load->model('customerpartner/master');
					$sellers = $this->sellerAdminData($products, '', true);

					if (isset($this->session->data['shipping_method'])) {
						$shipping_method = explode('.', $this->session->data['shipping_method']['code']);

						//wk check
						if (substr($shipping_method[0], 0, 2) == 'wk') {

							$address = '';

							if ($this->customer->isLogged() && isset($this->session->data['shipping_address'])) {
								$address = $this->session->data['shipping_address'];
							} elseif (isset($this->session->data['guest'])) {
								$address = $this->session->data['guest']['shipping'];
							}

							$total_shipping = 0;

							$this->load->model('extension/shipping/' . $shipping_method[0]);

							foreach ($sellers as $key => $seller) {
								if ($seller['total'] > 0) { // escape admin only with commission and blank sellers
									if ($shipping_method[0] != 'wk_multi_shipping') {
										$shipping = $this->{'model_extension_shipping_' . $shipping_method[0]}->getQuote($address, array($seller));

										if (isset($shipping['quote'][$shipping_method[1]]['cost'])) //add mp shipping prices to seller amount
											$sellers[$key]['price'] += $this->tax->calculate($shipping['quote'][$shipping_method[1]]['cost'], $this->config->get($shipping_method[1] . '_tax_class_id'), $this->config->get('config_tax'));
										$sellers[$key]['total'] += $this->tax->calculate($shipping['quote'][$shipping_method[1]]['cost'], $this->config->get($shipping_method[1] . '_tax_class_id'), $this->config->get('config_tax'));
									} elseif (isset($this->session->data['multishippingData'][$seller['seller']]['cost'])) {
										$itsShippingMethod = explode($seller['seller'] . '_', current(explode('.', $this->session->data['multishippingData'][$seller['seller']]['code'])));
										$sellers[$key]['price'] += $this->tax->calculate($this->session->data['multishippingData'][$seller['seller']]['cost'], $this->config->get($itsShippingMethod[1] . '_tax_class_id'), $this->config->get('config_tax'));
										$sellers[$key]['total'] += $this->tax->calculate($this->session->data['multishippingData'][$seller['seller']]['cost'], $this->config->get($itsShippingMethod[1] . '_tax_class_id'), $this->config->get('config_tax'));
									}
								}
							}
						}
					}

					$notify = true;
					if (isset($payment_intent['charges']['data'][0])) {

						$commentAll = $this->language->get('text_transaction_details_mp');

						$charges = $payment_intent['charges']['data'][0];
						$chargeid = $charges['id'];

						if ($charges['failure_message'] || $charges['failure_code']) {
							$this->log->write('STRIPE_PAYMENRT :: Charge failed ' . $charges['failure_message'] . '(' . $charges['failure_code'] . ')');
						}

						$seller_details = $charges['description'];

						$comment = '';
						$comment .= $this->language->get('text_stripe_id') . $charges['id'];
						if ($charges['customer'])
							$comment .= $this->language->get('text_stripe_customer_id') . $charges['customer'];
						$comment .= $this->language->get('text_currency') . strtoupper($charges['currency']);
						$comment .= $this->language->get('text_description') . $charges['description'];
						$comment .= $this->language->get('text_livemode') . ($charges['livemode'] ? $this->language->get('Yes') : $this->language->get('No'));
						$comment .= $this->language->get('text_paid') . ($charges['paid'] ? $this->language->get('Yes') : $this->language->get('No'));

						//card data

						$address_line1   = $charges['billing_details']['address']['line1'];
						$address_line2   = $charges['billing_details']['address']['line2'];
						$address_state   = $charges['billing_details']['address']['state'];
						$address_zip     = $charges['billing_details']['address']['postal_code'];
						$address_country = $charges['billing_details']['address']['country'];
						$address_city    = $charges['billing_details']['address']['city'];

						if ($charges['source']['type'] == 'three_d_secure') {

							$brand = $charges['source']['three_d_secure']['brand'];
							$name = $charges['source']['three_d_secure']['name'];
							$last4 = $charges['source']['three_d_secure']['last4'];
							$exp_month = $charges['source']['three_d_secure']['exp_month'];
							$exp_year = $charges['source']['three_d_secure']['exp_year'];
							$country = $charges['source']['three_d_secure']['country'];
							$fingerprint = $charges['source']['three_d_secure']['fingerprint'];
							$cvc_check = $charges['source']['three_d_secure']['cvc_check'];
							$address_line1_check = $charges['source']['three_d_secure']['address_line1_check'];
							$address_zip_check = $charges['source']['three_d_secure']['address_zip_check'];
						} else {

							
							if(!empty($charges['payment_method_details'])) { 
								$brand = $charges['payment_method_details']['card']['brand'];
								$name = !empty($charges['payment_method_details']['card']['name']) ? $charges['payment_method_details']['card']['name'] : ''; // 
								$last4 = $charges['payment_method_details']['card']['last4'];
								$exp_month = $charges['payment_method_details']['card']['exp_month'];
								$exp_year = $charges['payment_method_details']['card']['exp_year'];
								$country = $charges['payment_method_details']['card']['country'];
								$fingerprint = $charges['payment_method_details']['card']['fingerprint'];
								$cvc_check = !empty($charges['payment_method_details']['card']['checks']['cvc_check']) ? $charges['payment_method_details']['card']['checks']['cvc_check'] : '';
								$address_line1_check = !empty($charges['payment_method_details']['card']['checks']['address_line1_check']) ? $charges['payment_method_details']['card']['checks']['address_line1_check'] : '';
								$address_zip_check = !empty($charges['payment_method_details']['card']['checks']['address_postal_code_check']) ? $charges['payment_method_details']['card']['checks']['address_postal_code_check'] : '';
							} else {
								
								$brand = $charges['source']['card']['brand'];
								$name = $charges['source']['card']['name'];
								$last4 = $charges['source']['card']['last4'];
								$exp_month = $charges['source']['card']['exp_month'];
								$exp_year = $charges['source']['card']['exp_year'];
								$country = $charges['source']['card']['country'];
								$fingerprint = $charges['source']['card']['fingerprint'];
								$cvc_check = $charges['source']['card']['cvc_check'];
								$address_line1_check = $charges['source']['card']['address_line1_check'];
								$address_zip_check = $charges['source']['card']['address_zip_check'];

							}
						}

						//card data
						$comment .= $this->language->get('text_card_data');
						$comment .= $this->language->get('text_card_id');
						$comment .= $this->language->get('text_brand') . $brand;
						$comment .= $this->language->get('text_name') . $name;
						$comment .= $this->language->get('text_last4') . $last4;
						$comment .= $this->language->get('text_exp_month') . $exp_month;
						$comment .= $this->language->get('text_exp_year') . $exp_year;
						$comment .= $this->language->get('text_fingerprint') . $fingerprint;
						$comment .= $this->language->get('text_country') . $country . (file_exists(DIR_IMAGE . 'flags/' . strtolower(trim($country)) . '.png') ? ' <img src="' . HTTP_SERVER . 'image/flags/' . strtolower(trim($country)) . '.png">' : '');
						if ($address_line1)
							$comment .= $this->language->get('text_address1') . $address_line1;
						if ($address_line2)
							$comment .= $this->language->get('text_address2') . $address_line2;
						if ($address_city)
							$comment .= $this->language->get('text_city') . $address_city;
						if ($address_state)
							$comment .= $this->language->get('text_state') . $address_state;
						if ($address_zip)
							$comment .= $this->language->get('text_zip') . $address_zip;
						if ($address_country)
							$comment .= $this->language->get('text_add_country') . $address_country;

						//checks
						$comment .= $this->language->get('text_cvc_check') . ucwords($cvc_check);
						$comment .= $this->language->get('text_address_check') . ucwords($address_line1_check);
						$comment .= $this->language->get('text_address_zip_check') . ucwords($address_zip_check);

						//$comment .= "<br/><a onclick='refund(".$order_id.");'>".$this->language->get('text_refund').'</a>';

						$commentAll .= $comment . '<br><br>-----------------------------------------<br><br>'; // for admin order history

						$charge_details = array(
							'id' => $chargeid,
							'customer' => $charges['customer'],
							'amount' => $charges['amount'],
							'currency' => $charges['currency'],
							'description' => $charges['description'],
							'livemode' => $charges['livemode'],
							'paid' => $charges['paid'],
							'card_id' => '',
							'brand' => $brand,
							'name' => $name,
							'last4' => $last4,
							'exp_month' => $exp_month,
							'exp_year' => $exp_year,
							'fingerprint' => $fingerprint,
							'country' => $country,
							'address_line1' => $address_line1,
							'address_line2' => $address_line2,
							'address_city' => $address_city,
							'address_state' => $address_state,
							'address_zip' => $address_zip,
							'address_country' => $address_country,
							'cvc_check' => $cvc_check,
							'address_line1_check' => $address_line1_check,
							'address_zip_check' => $address_zip_check,

						);

						//$this->log->write($charge_details);
						// map order with stripe

						$this->model_extension_payment_wk_stripe->addsripeIdwithorderid($order_id, $chargeid);


						//Insert into order stripe
						$this->model_extension_payment_wk_stripe->addIntoOrderStripe($charge_details);

						foreach ($products as $product) {
							$this->model_extension_payment_wk_stripe->addStatus($order_id, $product['product_id']);
						}

						foreach($sellers as $seller){

							$seller_amount = $this->currency->format($seller['price'], $cart_currency , '' ,false);

							$seller_keys = $this->model_extension_payment_wk_stripe->isConnected($seller['seller']);

							if($seller['seller'] != 'Admin' && $seller_keys){

								if($seller_amount > 0) {
									$seller_data = $this->model_customerpartner_master->getProfile($seller['seller']);
									$this->log->write($seller_data);
									if($seller_keys){

										$seller_charges = \Stripe\Transfer::create([
											"amount" => (int)($seller_amount * 100),
											"currency" => $cart_currency,
											"destination" => $seller_keys['user_id'],
											"source_transaction" => $chargeid,
											"transfer_group" => "Seller Transfer Order_id:-" . $order_id,
										]);

										$this->model_extension_payment_wk_stripe->addTransaction($seller['seller'], $seller_amount * 100, $comment, $order_id);

										$this->model_extension_payment_wk_stripe->addTransfer($seller['seller'], $seller_amount * 100, $order_id, $seller_charges->id, $seller_charges->balance_transaction, $chargeid);
									}
								} else {
									$this->model_extension_payment_wk_stripe->addTransfer($seller['seller'], $seller_amount * 100, $order_id, 0, 0, $chargeid);
								}
							} else {

								$this->model_extension_payment_wk_stripe->addTransfer($seller['seller'], $seller_amount * 100, $order_id, 0, 0, $chargeid);
							}
						}

						$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_wk_stripe_success_status'));

						if ($cvc_check != 'pass') {
							$commentAll = $this->language->get('text_cvc_chk_failed');
							$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_wk_stripe_cvc_status'), $commentAll, $notify);
						} elseif ($address_line1_check != 'pass') {
							$commentAll = $this->language->get('text_address_ckeck_failed');
							$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_wk_stripe_addess_status'), $commentAll, $notify);
						} elseif ($address_zip_check != 'pass') {
							$commentAll = $this->language->get('text_address_zip_ckeck_failed');
							$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_wk_stripe_zip_status'), $commentAll, $notify);
						}

						$this->session->data['success'] = $this->language->get('text_success');
						$this->response->redirect($this->url->link('checkout/success', '', true));
					} else {
						$this->session->data['error'] = $this->language->get('error_charges');
						$this->response->redirect($this->url->link('checkout/checkout', '', true));
					}



				} else {
					$this->session->data['error'] = $this->language->get('error_payment_intent');
					$this->response->redirect($this->url->link('checkout/checkout', '', true));
				}
			} catch (\Stripe\Error\InvalidRequest $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			} catch (\Stripe\Error\Authentication $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			} catch (\Stripe\Error\Card $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			} catch (\Stripe\Error\Permission $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			} catch (\Stripe\Error\RateLimit $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			} catch (\Stripe\Error\Api $e) {
				$error = $e->getJsonBody();
				if (isset($error['error']) && $error['error']['message']) {
					$error_message = $error['error']['message'];
				}
			}
			$this->session->data['error'] = $error_message;
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		} else {

			$this->session->data['error'] = $this->language->get('error_stripe_id');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}
	}
		public function webhook()
    {
        if (!$this->config->get('payment_wk_stripe_webhook_status')) {
            echo "webhook is not enabled from webkul stripe extension";
            http_response_code(400);
            exit();
        }

        require_once DIR_IMAGE . '../stripe-lib/Stripe.php';

        $keys = $this->getKeys();

        \Stripe\Stripe::setApiKey($keys['secret_key']);

        $endpoint_secret = $this->config->get('payment_wk_stripe_webhook_secret');

        $payload = @file_get_contents('php://input');

        $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException$e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException$e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        $this->load->model('extension/payment/wk_stripe');


		$this->load->language('extension/payment/wk_stripe');

        // Handle the event

    			switch ($event->type) {

            case 'payment_intent.succeeded':
                $insert_arr = array(
                    'comment' => sprintf($this->language->get('text_charge_refund_success'), $event->data->object->id),
                    'event_encoded' => $event,
                );
                $this->model_extension_payment_wk_stripe->addEvent($insert_arr);
						break;

						case 'charge.refunded':

							$order_id = $this->model_extension_payment_wk_stripe->checkorderByChargeId($event->data->object->id);

							if(isset($order_id))
							{
								 $seller_id = $this->model_extension_payment_wk_stripe->getSellerIdByOrderId($order_id['order_id']);


								 $result = 	$this->refundsection($order_id['order_id'], $seller_id['customer_id'] , $event->data->object->id,$event->data->object->amount);
								 if($result){
								 				$insert_arr = array(
													 'comment' => $result,
													 'event_encoded' => $event,
											 	);
								 }

							 }else{
								 	$insert_arr = array(
										 'comment' => sprintf($this->language->get('text_charge_refund'), $event->data->object->id),
										 'event_encoded' => $event,
									);
							 }
						 $this->model_extension_payment_wk_stripe->addEvent($insert_arr);

						break;
            case 'balance.available':
                $pending_amount = $event->data->object->pending[0]['amount'] / 100 . ' ' . strtoupper($event->data->object->pending[0]['currency']);

                $available_amount = $event->data->object->available[0]['amount'] / 100 . ' ' . strtoupper($event->data->object->pending[0]['currency']);

                $insert_arr = array(
                    'comment' => sprintf($this->language->get('text_balance_available'), $available_amount, $pending_amount),
                    'event_encoded' => $event
                );

                $this->model_extension_payment_wk_stripe->addEvent($insert_arr);
                break;
        }

        http_response_code(200);
    }


	public function refundsection($order_id,$seller_id,$chargeid,$refund_amount)
	{

		  $this->load->model('extension/payment/wk_stripe');

			//Get the refund status
				$this->load->language('extension/payment/wk_stripe');
			$status = $this->model_extension_payment_wk_stripe->getSellerRefundStatus($order_id, (int)$seller_id);

				if(!empty($status) && $status['refund_status'] == 0) {

					$reverse_amt = $refund_amt = ($refund_amount/100);
					if($reverse_amt > 0) {
						$balance_transactions = $this->model_extension_payment_wk_stripe->getTransaction($order_id, (int)$seller_id);

						//for connected seller or not
						if (isset($balance_transactions[0]['transfer_id']) && $balance_transactions[0]['transfer_id']) {
							//reverse transfer and refund amount
							$this->stripeRefund($reverse_amt, $balance_transactions, (int)$seller_id, $refund_amt);
							$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, (int)$seller_id);
							$message = sprintf($this->language->get('text_charge_refund_success'), $event->data->object->id);
						} else {
						//	for non connected seller
							require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');

							$stripe_keys = $this->getKeys();
							\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);

							\Stripe\Stripe::setAppInfo(
								"Webkul Opencart Marketplace Stripe Plugin",
								"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
							);


							$re = \Stripe\Refund::create(array(
								'charge' => $chargeid,
								'amount'   => (int)($reverse_amt * 100)
							));

							$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, $seller_id);
							$message = sprintf($this->language->get('text_charge_refund_Non_seller_success'), $chargeid);

						}
					}else{
							$this->model_extension_payment_wk_stripe->UpdateSellerStatus($order_id, $seller_id);

							$message = sprintf($this->language->get('text_refund_ammount_is_zero'), $chargeid);
					}

				}else{
						$message = sprintf($this->language->get('text_order_not_found'), $chargeid);
				}

				return $message;
	}

		public function stripeRefund($reverse_amt, $balance_transactions, $seller_id, $refund_amt)
		{
			require_once(DIR_IMAGE . '../stripe-lib/Stripe.php');
			$stripe_keys = $this->getKeys();
			\Stripe\Stripe::setApiKey($stripe_keys['secret_key']);
			// \Stripe\Stripe::setApiVersion("2019-05-16");
			\Stripe\Stripe::setAppInfo(
				"Webkul Opencart Marketplace Stripe Plugin",
				"https://store.webkul.com/Opencart-Marketplace-Stripe-Payment-Gateway.html"
			);

			$tr = \Stripe\Transfer::retrieve($balance_transactions[0]['transfer_id']);
			$tranfer_remain = $tr->__toArray(true);

			//To check whether the reversal amount of tranfer is less than tranfered amount
			if ($reverse_amt * 100 > $tranfer_remain['amount'] - $tranfer_remain['amount_reversed'])
				$reverse_amt = ($tranfer_remain['amount'] - $tranfer_remain['amount_reversed']) / 100;


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

?>
