<?php
class ControllerExtensionAccountPurpletreeMultivendorStripeconnect extends Controller {
	public function index() {
		$this->load->model('extension/purpletree_multivendor/stripeconnect');
		$payment_mode = $this->config->get('payment_pts_stripe_payment_mode');
		$stripe = $payment_mode
			? array('secret_key' => $this->config->get('payment_pts_stripe_secret_key_live'), 'publishable_key' => $this->config->get('payment_pts_stripe_publish_key_live'))
			: array('secret_key' => $this->config->get('payment_pts_stripe_secret_key_test'), 'publishable_key' => $this->config->get('payment_pts_stripe_publish_key_test'));

		$use_account_link = $this->config->get('payment_pts_stripe_use_account_link') !== '0';
		$account_link_return = isset($this->request->get['account_link']) && isset($this->request->get['token']);
		$start_account_link = isset($this->request->get['start']) && (int)$this->request->get['start'] === 1;

		if ($account_link_return && !empty($this->request->get['token'])) {
			$token = $this->request->get['token'];
			$row = $this->model_extension_purpletree_multivendor_stripeconnect->getPendingByToken($token);
			if ($row) {
				$data = array('seller_id' => (int)$row['seller_id'], 'account_id' => $row['account_id'], 'livemode' => (int)$row['livemode'], 'scope' => 'read_write');
				$res = $this->model_extension_purpletree_multivendor_stripeconnect->insertStripeAccount($data);
				$this->model_extension_purpletree_multivendor_stripeconnect->deletePendingByToken($token);
				if ($res) {
					$this->session->data['success_stripe_connect'] = 'Stripe account has been connected successfully.';
				} else {
					$this->session->data['error_stripe_connect_warning'] = 'Stripe account could not be saved. Please try again or contact support.';
				}
			} else {
				$this->session->data['error_stripe_connect_warning'] = 'Invalid or expired link. Please click Connect again from your dashboard.';
			}
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
			return;
		}

		if ($start_account_link && $use_account_link && !empty($stripe['secret_key'])) {
			$store_detail = $this->customer->isSeller();
			$seller_id = (int)$this->customer->getId();
			if (empty($store_detail) || !isset($store_detail['store_status'])) {
				$this->session->data['error_stripe_connect_warning'] = 'Your seller store is not active yet. Complete and get your store approved first, then try Connect again.';
				$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
				return;
			}
			$pay_lib_root = DIR_SYSTEM . 'library/purpletree_multivendor/stripe_payment/';
			require_once($pay_lib_root . 'stripe/stripe-php/init.php');
			try {
				\Stripe\Stripe::setApiKey($stripe['secret_key']);
				$account = \Stripe\Account::create(array('type' => 'express', 'country' => 'US'));
				$return_base = str_replace('&amp;', '&', $this->url->link('extension/account/purpletree_multivendor/stripeconnect', '', true));
				$token = bin2hex(random_bytes(24));
				$this->model_extension_purpletree_multivendor_stripeconnect->insertPendingAccountLink($seller_id, $account->id, $payment_mode ? 1 : 0, $token);
				$return_url = $return_base . (strpos($return_base, '?') !== false ? '&' : '?') . 'account_link=1&token=' . urlencode($token);
				$refresh_url = str_replace('&amp;', '&', $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true));
				$link = \Stripe\AccountLink::create(array(
					'account' => $account->id,
					'refresh_url' => $refresh_url,
					'return_url' => $return_url,
					'type' => 'account_onboarding'
				));
				$this->response->redirect($link->url);
				return;
			} catch (Exception $e) {
				if ($this->config->get('payment_pts_stripe_debug')) {
					$this->log->write('Stripe Account Link start: ' . $e->getMessage());
				}
				$this->session->data['error_stripe_connect_warning'] = 'Could not start Stripe onboarding. Please try again or contact support.';
				$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
				return;
			}
		}

		$code = isset($this->request->get['code']) ? trim($this->request->get['code']) : '';
		$oauth_error = isset($this->request->get['error']) ? $this->request->get['error'] : '';
		$oauth_error_desc = isset($this->request->get['error_description']) ? $this->request->get['error_description'] : '';

		if (!empty($oauth_error)) {
			$msg = 'Stripe connection was not completed.';
			if ($oauth_error === 'access_denied') {
				$msg = 'You cancelled or did not approve the Stripe connection. You can try Connect again when ready.';
			} elseif (!empty($oauth_error_desc)) {
				$msg = 'Stripe: ' . $oauth_error_desc;
			}
			if ($this->config->get('payment_pts_stripe_debug')) {
				$this->log->write('Stripe connect OAuth redirect error: ' . $oauth_error . ' - ' . $oauth_error_desc);
			}
			$this->session->data['error_stripe_connect_warning'] = $msg;
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
			return;
		}
		if (empty($code)) {
			$this->session->data['error_stripe_connect_warning'] = 'Stripe did not return an authorization code. Please try Connect again.';
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
			return;
		}

		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://connect.stripe.com/oauth/token');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'client_secret' => $stripe['secret_key'],
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]));
			$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$raw = curl_exec($ch);
			if (curl_errno($ch)) {
				if ($this->config->get('payment_pts_stripe_debug')) {
					$this->log->write('Stripe connect curl error: ' . curl_error($ch));
				}
				$this->session->data['error_stripe_connect_warning'] = 'Connection to Stripe failed. Please try again.';
				curl_close($ch);
				$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
				return;
			}
            curl_close($ch);

			$result = $raw ? json_decode($raw) : null;
			if (!$result) {
				if ($this->config->get('payment_pts_stripe_debug')) {
					$this->log->write('Stripe connect invalid response: ' . substr($raw, 0, 500));
				}
				$this->session->data['error_stripe_connect_warning'] = 'Stripe returned an invalid response. Please try Connect again.';
				$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
				return;
			}

            $store_detail = $this->customer->isSeller();
            $seller_id = $this->customer->isLogged();
            if ($this->config->get('payment_pts_stripe_debug')) {
				$this->log->write('Seller Id: ' . $seller_id);
			}
            if(isset($result->stripe_user_id)){
                if (empty($store_detail) || !isset($store_detail['store_status'])) {
                    $this->session->data['error_stripe_connect_warning'] = 'Your seller store is not active yet. Complete and get your store approved first, then try Connect again.';
                    if ($this->config->get('payment_pts_stripe_debug')) {
                        $this->log->write('Stripe connect: store not active or store_detail missing');
                    }
                } elseif(isset($store_detail['store_status'])){
                    $data=array(
                    'seller_id'=>$seller_id,
                    'account_id'=>$result->stripe_user_id,
                    'livemode'=>$result->livemode,
                    'scope'=>$result->scope
                    );
                    /* $num_of_acc= $this->model_extension_purpletree_multivendor_stripeconnect->checkAccountExist($data['account_id']);
                    if($num_of_acc){
                        $this->session->data['error_stripe_connect_warning']='Account already exist';
                        if ($this->config->get('payment_pts_stripe_debug')) {
                                $this->log->write('Account already exist');
                        }
                    } */
                    $res='';
                    //if(!$num_of_acc){
                    $res= $this->model_extension_purpletree_multivendor_stripeconnect->insertStripeAccount($data);
                    //}
                    if($res){
                        $this->session->data['success_stripe_connect']='Stripe account has been connected successfully';
                        if ($this->config->get('payment_pts_stripe_debug')) {
                            $livemode='Test';
                            if($data['livemode']){
                                $livemode='Live';
                            }
                                $this->log->write('Account has been created successfully');
                                $this->log->write('Account Id: ' .$data['account_id']);
                                $this->log->write('Live Mode: ' .$livemode);
                                $this->log->write('Scope: ' .$data['scope']);
                        }	
                    } else {
                        $this->session->data['error_stripe_connect_warning'] = 'Stripe account could not be saved. Please try again or contact support.';
                        if ($this->config->get('payment_pts_stripe_debug')) {
                            $this->log->write('Account is not created');
                        }
                    }
                }
			} else {
                $errMsg = isset($result->error_description) ? $result->error_description : (isset($result->error) ? $result->error : 'unknown');
                if ($this->config->get('payment_pts_stripe_debug')) {
                    $this->log->write('Stripe connect error: ' . $errMsg);
                }
                $userMsg = 'Stripe did not complete the connection: ' . $errMsg;
                if (isset($result->error) && $result->error === 'invalid_grant') {
                    $userMsg = 'The connection link expired or was already used. Please click Connect again to get a new link.';
                }
                $this->session->data['error_stripe_connect_warning'] = $userMsg;
            }
		} catch (Exception $e) {
			if ($this->config->get('payment_pts_stripe_debug')) {
				$this->log->write('Stripe connect exception: ' . $e->getMessage());
			}
			$this->session->data['error_stripe_connect_warning'] = 'An error occurred while connecting to Stripe. Please try again or contact support.';
		}
		$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/dashboardicons'));
	}	
}
?>