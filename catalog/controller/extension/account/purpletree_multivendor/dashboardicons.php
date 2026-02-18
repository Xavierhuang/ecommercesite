<?php
class ControllerExtensionAccountPurpletreeMultivendorDashboardicons extends Controller {
		private $error = array();
		
		public function index(){
			
			if (!$this->customer->isLogged()) {
				$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true);
				
				$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/sellerlogin', '', true));
			}
			$this->load->model('extension/purpletree_multivendor/dashboard');
			$this->model_extension_purpletree_multivendor_dashboard->checkSellerApproval();
			$store_detail = $this->customer->isSeller();
			if(!isset($store_detail['store_status'])){
				$this->response->redirect($this->url->link('account/account', '', true));
				}else{
				$stores=array();
						if(isset($store_detail['multi_store_id'])){
							$stores=explode(',',$store_detail['multi_store_id']);
						}
						
					if(isset($store_detail['store_status']) && !in_array($this->config->get('config_store_id'),$stores)){	
					$this->response->redirect($this->url->link('account/account','', true));
				}
			}	
			
			$this->load->language('purpletree_multivendor/dashboard');
			$this->load->model('extension/purpletree_multivendor/dashboard');
			$data['seller_orders'] = array();
			
			
			if (isset($this->session->data['error_warning'])) {
				$data['error_warning'] = $this->session->data['error_warning'];
				
				unset($this->session->data['error_warning']);
				} else {
				$data['error_warning'] = '';
			}
			if (isset($this->session->data['success_stripe_connect'])) {
				$data['success'] = $this->session->data['success_stripe_connect'];
				
				unset($this->session->data['success_stripe_connect']);
				} else {
				$data['success'] = '';
			}
			
			if (isset($this->session->data['error_stripe_connect_warning'])) {
				$data['error_warning'] = $this->session->data['error_stripe_connect_warning'];
				
				unset($this->session->data['error_stripe_connect_warning']);
				} else {
				$data['error_warning'] = '';
			}
			$url ='';
			///Help code///	
			//$data['helplink'] = "https://www.purpletreesoftware.com/knowledgebase/tag/opencart-multivendor-seller";
			$data['helplink'] = "https://cutt.ly/WCoMvAX";
			if (defined ('DISABLED_PTS_HELP')){if(DISABLED_PTS_HELP == 0){$data['helpcheck'] = 1;}else{$data['helpcheck'] = 0;}}else{$data['helpcheck'] = 1;}
			if ($this->request->server['HTTPS']) {
				$data['helpimage'] = HTTPS_SERVER . 'image/catalog/help.png';
			 } else {
				$data['helpimage'] = HTTP_SERVER . 'image/catalog/help.png';
			}
			/// End Help code///
			$data['breadcrumbs'] = array();
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home','',true)
			);
			
			$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title1'),
			'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
			);
			
			$data['totalorders'] = $this->model_extension_purpletree_multivendor_dashboard->getCountSeen($this->customer->getId());

			 // start Dashboard icons  section//

				$dashboard_icons=array();
                $dashboard_icons=$this->config->get('module_purpletree_multivendor_icons_status');
				if(!empty($dashboard_icons)) {
		         foreach($dashboard_icons as $key => $value){
					$data[$key]=0;
					$data[$value]=1;
				}
				}
		    // End Dashboard icons  section//

			$data['totaladminmessages'] = $this->model_extension_purpletree_multivendor_dashboard->getCountAdminMessageSeen($this->customer->getId());
			$data['totalenqures'] = $this->model_extension_purpletree_multivendor_dashboard->getCountSeen1($this->customer->getId());
			$data['isSeller'] = $this->customer->isSeller();
			$store_id = (isset($data['isSeller']['id'])?$data['isSeller']['id']:'');
			$this->load->model('localisation/order_status');
			$this->document->setTitle($this->language->get('heading_title1'));
			$data['heading_title']=$this->language->get('heading_title1');
			/////////////////////////
			$data['text_Manage_Downloads'] = $this->language->get('text_Manage_Downloads');
			$data['text_reviews'] = $this->language->get('text_reviews');
			$data['text_Seller_Account'] = $this->language->get('text_Seller_Account');
			$data['text_Customer_Enquiries'] = $this->language->get('text_Customer_Enquiries');
			$data['text_Bulk_product_upload'] = $this->language->get('text_Bulk_product_upload');
			$data['text_Orders'] = $this->language->get('text_Orders');
			$data['text_Seller_Store'] = $this->language->get('text_Seller_Store');
			$data['text_Store_information'] = $this->language->get('text_Store_information');
			$data['text_View_Store'] = $this->language->get('text_View_Store');
			$data['text_Subscription_Invoice'] = $this->language->get('text_Subscription_Invoice');
			$data['text_Seller_Payments'] = $this->language->get('text_Seller_Payments');
			$data['text_Subscription_plan'] = $this->language->get('text_Subscription_plan');
			$data['text_Payments'] = $this->language->get('text_Payments');
			$data['text_Commisions'] = $this->language->get('text_Commisions');
			$data['text_shiiping_rate'] = $this->language->get('text_shiiping_rate');
			$data['text_Manage_products'] = $this->language->get('text_Manage_products');
			$data['text_seller_template_product'] = $this->language->get('text_seller_template_product');
			$data['text_blog_post'] = $this->language->get('text_blog_post');
			$data['text_blog_comment'] = $this->language->get('text_blog_comment');
			$data['text_commissioninvoice'] = $this->language->get('text_commissioninvoice');
			$data['text_sellerenquiries'] = $this->language->get('text_sellerenquiries');
			$data['text_sellercoupons'] = $this->language->get('text_sellercoupons');
			$data['text_seller_returns'] = $this->language->get('text_seller_returns');
			///////////////////
			$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
			$data['column_left'] = $this->load->controller('extension/account/purpletree_multivendor/common/column_left');
			$data['footer'] = $this->load->controller('extension/account/purpletree_multivendor/common/footer');
			$data['header'] = $this->load->controller('extension/account/purpletree_multivendor/common/header');
			
			
			$data['sellerprofile'] = $this->url->link('extension/account/edit', '', true);
			$data['downloadsitems'] = $this->url->link('extension/account/purpletree_multivendor/downloads', '', true);
			$data['sellerstore'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore', '', true);
			$data['sellerproduct'] = $this->url->link('extension/account/purpletree_multivendor/sellerproduct', '', true);
			$orderstatus = 0;
			$end_date_to = date('Y-m-d');
			$end_date_from = date('Y-m-d', strtotime("-30 days"));				
			$data['sellerorder'] = $this->url->link('extension/account/purpletree_multivendor/sellerorder', 'filter_date_from='.$end_date_from.'&filter_date_to=' .$end_date_to.'', true);
			$data['sellercommission'] = $this->url->link('extension/account/purpletree_multivendor/sellercommission', '', true);
			$data['sellerpayment'] = $this->url->link('extension/account/purpletree_multivendor/sellerpayment', '', true);
			$data['removeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/removeseller', '', true);
			$data['becomeseller'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/becomeseller', '', true);
			$data['sellerview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/storeview&seller_store_id='.$store_id, '', true);
			$data['sellerreview'] = $this->url->link('extension/account/purpletree_multivendor/sellerstore/sellerreview', '', true);
			$data['sellerenquiry'] = $this->url->link('extension/account/purpletree_multivendor/sellercontact/sellercontactlist', '', true);
			$data['dashboardicons'] = $this->url->link('extension/account/purpletree_multivendor/dashboardicons', '', true);
			$data['dashboard'] = $this->url->link('extension/account/purpletree_multivendor/dashboard', '', true);
			if($this->config->get('module_purpletree_multivendor_shippingtype')){
				$data['shipping'] = $this->url->link('extension/account/purpletree_multivendor/sellergeozone', '', true);
				}else{
				$data['shipping'] = $this->url->link(	'extension/account/purpletree_multivendor/shipping', '', true);
			}
			$data['bulkproductupload'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);
			
			$data['purpletree_multivendor_subscription_plans'] = $this->config->get('module_purpletree_multivendor_subscription_plans');
			if($this->config->get('module_purpletree_multivendor_subscription_plans')==1){
				
				$data['subscriptionplan'] = $this->url->link('extension/account/purpletree_multivendor/subscriptionplan', '', true);
				
				$data['subscriptions'] = $this->url->link('extension/account/purpletree_multivendor/subscriptions', '', true);
				
			}
			$data['seller_blog_status'] = $this->config->get('module_purpletree_sellerblog_status');
			if($this->config->get('module_purpletree_sellerblog_status')){
				$data['sellerblogpost'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogpost', '', true);
				$data['sellerblogcomment'] = $this->url->link('extension/account/purpletree_multivendor/sellerblogcomment', '', true);
			}
			$data['commissioninvoice'] = $this->url->link('extension/account/purpletree_multivendor/commissioninvoice', '', true);
			$data['module_purpletree_multivendor_seller_product_template'] = $this->config->get('module_purpletree_multivendor_seller_product_template');
			if($data['module_purpletree_multivendor_seller_product_template'] == 1){
				$data['seller_template_product'] = $this->url->link('extension/account/purpletree_multivendor/sellertemplateproduct', '', true);
			}
			//stripe connect	
			
			$data['text_stripe_connect']= $this->language->get('text_stripe_connect');
			$data['stripe_status'] = $stripe_status = $this->config->get('payment_pts_stripe_status');
			
			if ($this->config->get('payment_pts_stripe_debug')) {
				if($stripe_status){
					$this->log->write('Stripe payment mathod is enable');
				} else {
					$this->log->write('Stripe payment mathod is disable');
				}
			 }
			if($stripe_status){
				$data['a_href']='';
				$data['a']='';
				$data['stripe_connect_eligible'] = false;
				$store_ok = !empty($store_detail) && isset($store_detail['store_status']);
				if ($store_ok) {
					$payment_mode = $this->config->get('payment_pts_stripe_payment_mode');
					$stripe = array();
					if($payment_mode){
						$client_id=$this->config->get('payment_pts_stripe_client_id_live');
					} else {
						$client_id=$this->config->get('payment_pts_stripe_client_id_test');
					}
					if ($client_id==NULL) {
						if ($this->config->get('payment_pts_stripe_debug')) {
							$this->log->write('Client Id is blank. Please enter client id in stripe payment setting');
						}
					}
					if($client_id!=NULL){
						$data['stripe_connect_eligible'] = true;
						$use_account_link = $this->config->get('payment_pts_stripe_use_account_link') !== '0';
						if ($use_account_link) {
							$stripe_connect = str_replace('&amp;', '&', $this->url->link('extension/account/purpletree_multivendor/stripeconnect', 'start=1', true));
						} else {
							$redirect_uri = str_replace('&amp;', '&', $this->url->link('extension/account/purpletree_multivendor/stripeconnect', '', true));
							$stripe_connect = 'https://connect.stripe.com/oauth/authorize?response_type=code&client_id='.$client_id.'&scope=read_write&redirect_uri=' . urlencode($redirect_uri);
						}
						$data['a_href']='<a href="'.$stripe_connect.'">';
						$data['a']='</a>';
						$this->load->model('extension/purpletree_multivendor/stripeconnect');
						$num_of_acc1 = $this->model_extension_purpletree_multivendor_stripeconnect->checkAccountExistwithsellerid($this->customer->getId());
						if($num_of_acc1){
							$data['text_stripe_connect']= $this->language->get('text_stripe_connected');
							$data['a_href']='';
							$data['a']='';
						}
					}
				} else {
					$data['text_stripe_connect_required'] = $this->language->get('text_stripe_connect_required');
				}
			}
	//stripe connect
			// Onboarding checkpoints (scope 2.1.5, 2.2)
			$data['onboarding_steps'] = $this->getOnboardingCheckpoints($store_detail, $data);

			$data['sellerenquiries'] = $this->url->link('extension/account/purpletree_multivendor/sellerenquiries', '', true);
			$data['sellercoupons'] = $this->url->link('extension/account/purpletree_multivendor/sellercoupons', '', true);
			$data['seller_product_returns'] = $this->url->link('extension/account/purpletree_multivendor/product_returns', '', true);
			$this->response->setOutput($this->load->view('account/purpletree_multivendor/dashboardicons', $data));
		}

		/**
		 * Onboarding checkpoints for suppliers (scope 2.1.5, 2.2).
		 * Returns list of steps with done (bool) and label/link.
		 */
		private function getOnboardingCheckpoints($store_detail, $data) {
			$steps = array();
			$store_id = isset($store_detail['id']) ? (int)$store_detail['id'] : 0;

			$steps[] = array(
				'label' => 'Store information completed',
				'done'  => !empty($store_detail['store_name']),
				'link'  => isset($data['sellerstore']) ? $data['sellerstore'] : ''
			);

			$stripe_done = false;
			if ($this->config->get('payment_pts_stripe_status')) {
				$this->load->model('extension/purpletree_multivendor/stripeconnect');
				$stripe_done = (bool)$this->model_extension_purpletree_multivendor_stripeconnect->checkAccountExistwithsellerid($this->customer->getId());
			} else {
				$stripe_done = true;
			}
			$steps[] = array(
				'label' => 'Payment (Stripe) connected',
				'done'  => $stripe_done,
				'link'  => ''
			);

			$product_count = 0;
			if ($store_id) {
				$q = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "purpletree_vendor_products WHERE seller_id = '" . (int)$this->customer->getId() . "'");
				$product_count = (int)$q->row['total'];
			}
			$steps[] = array(
				'label' => 'At least one product added',
				'done'  => $product_count > 0,
				'link'  => isset($data['sellerproduct']) ? $data['sellerproduct'] : ''
			);

			return $steps;
		}
	}
?>