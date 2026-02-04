<?php
class ControllerExtensionPaymentPtsStripe extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/pts_stripe');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

				if($this->request->post['payment_pts_stripe_total_min']==''){
					$this->request->post['payment_pts_stripe_total_min']=0;
				}
				if($this->request->post['payment_pts_stripe_total_max']==''){
					$this->request->post['payment_pts_stripe_total_max']=99999999;
				}
			$this->model_setting_setting->editSetting('payment_pts_stripe', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_payment_method_title'])) {
			$data['error_payment_method_title'] = $this->error['error_payment_method_title'];
		} else {
			$data['error_payment_method_title'] = '';
		}
		
		if (isset($this->error['error_e_secret_key'])) {
			$data['error_secret_key'] = $this->error['error_e_secret_key'];
		} else {
			$data['error_secret_key'] = '';
		}
		if (isset($this->error['error_payment_secret_key_test'])) {
			$data['error_payment_secret_key_test'] = $this->error['error_payment_secret_key_test'];
		} else {
			$data['error_payment_secret_key_test'] = '';
		}
		if (isset($this->error['error_payment_publish_key_test'])) {
			$data['error_payment_publish_key_test'] = $this->error['error_payment_publish_key_test'];
		} else {
			$data['error_payment_publish_key_test'] = '';
		}
		
		if (isset($this->error['error_payment_secret_key_live'])) {
			$data['error_payment_secret_key_live'] = $this->error['error_payment_secret_key_live'];
		} else {
			$data['error_payment_secret_key_live'] = '';
		}
		if (isset($this->error['error_payment_publish_key_live'])) {
			$data['error_payment_publish_key_live'] = $this->error['error_payment_publish_key_live'];
		} else {
			$data['error_payment_publish_key_live'] = '';
		}
		
		if (isset($this->error['error_payment_client_id_test'])) {
			$data['error_payment_client_id_test'] = $this->error['error_payment_client_id_test'];
		} else {
			$data['error_payment_client_id_test'] = '';
		}
		
		if (isset($this->error['error_payment_client_id_live'])) {
			$data['error_payment_client_id_live'] = $this->error['error_payment_client_id_live'];
		} else {
			$data['error_payment_client_id_live'] = '';
		}
		
		if (isset($this->error['error_payment_total_min'])) {
			$data['error_payment_total_min'] = $this->error['error_payment_total_min'];
		} else {
			$data['error_payment_total_min'] = '';
		}
		
		if (isset($this->error['error_payment_total_max'])) {
			$data['error_payment_total_max'] = $this->error['error_payment_total_max'];
		} else {
			$data['error_payment_total_max'] = '';
		}
        ///Help code///	
			//$data['helplink'] = "https://www.purpletreesoftware.com/knowledgebase/tag/opencart-multivendor-module";
			$data['helplink'] = "https://cutt.ly/kCoCLjO";
			if (defined ('DISABLED_PTS_HELP')){if(DISABLED_PTS_HELP == 0){$data['helpcheck'] = 1;}else{$data['helpcheck'] = 0;}}else{$data['helpcheck'] = 1;}
			if ($this->request->server['HTTPS']) {
				$data['helpimage'] = HTTPS_SERVER . 'view/image/help.png';
			 } else {
				$data['helpimage'] = HTTP_SERVER . 'view/image/help.png';
			}
			/// End Help code///
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/pp_adaptive', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/pts_stripe', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		//Payment Stripe text languge
		$data['text_payment_title']=$this->language->get('text_payment_title');


		if (isset($this->request->post['payment_pts_stripe_status'])) {
			$data['payment_pts_stripe_status'] = $this->request->post['payment_pts_stripe_status'];
		} else {
			$data['payment_pts_stripe_status'] = $this->config->get('payment_pts_stripe_status');
		}
		if (isset($this->request->post['payment_pts_stripe_title'])) {
			$data['payment_pts_stripe_title'] = $this->request->post['payment_pts_stripe_title'];
		} else {
			$data['payment_pts_stripe_title'] ='Stripe';
			if($this->config->get('payment_pts_stripe_title')!=NULL){
			$data['payment_pts_stripe_title'] = $this->config->get('payment_pts_stripe_title');
			}
		}
		if (isset($this->request->post['payment_pts_stripe_payment_mode'])) {
			$data['payment_pts_stripe_payment_mode'] = $this->request->post['payment_pts_stripe_payment_mode'];
		} else {
			$data['payment_pts_stripe_payment_mode'] = $this->config->get('payment_pts_stripe_payment_mode');
		}

		if (isset($this->request->post['payment_pts_stripe_secret_key_test'])) {
			$data['payment_pts_stripe_secret_key_test'] = $this->request->post['payment_pts_stripe_secret_key_test'];
		} else {
			$data['payment_pts_stripe_secret_key_test'] = $this->config->get('payment_pts_stripe_secret_key_test');
		}

		if (isset($this->request->post['payment_pts_stripe_publish_key_test'])) {
			$data['payment_pts_stripe_publish_key_test'] = $this->request->post['payment_pts_stripe_publish_key_test'];
		} else {
			$data['payment_pts_stripe_publish_key_test'] = $this->config->get('payment_pts_stripe_publish_key_test');
		}

		if (isset($this->request->post['payment_pts_stripe_secret_key_live'])) {
			$data['payment_pts_stripe_secret_key_live'] = $this->request->post['payment_pts_stripe_secret_key_live'];
		} else {
			$data['payment_pts_stripe_secret_key_live'] = $this->config->get('payment_pts_stripe_secret_key_live');
		}

		if (isset($this->request->post['payment_pts_stripe_publish_key_live'])) {
			$data['payment_pts_stripe_publish_key_live'] = $this->request->post['payment_pts_stripe_publish_key_live'];
		} else {
			$data['payment_pts_stripe_publish_key_live'] = $this->config->get('payment_pts_stripe_publish_key_live');
		}
		
		if (isset($this->request->post['payment_pts_stripe_client_id_test'])) {
			$data['payment_pts_stripe_client_id_test'] = $this->request->post['payment_pts_stripe_client_id_test'];
		} else {
			$data['payment_pts_stripe_client_id_test'] = $this->config->get('payment_pts_stripe_client_id_test');
		}
		if (isset($this->request->post['payment_pts_stripe_client_id_live'])) {
			$data['payment_pts_stripe_client_id_live'] = $this->request->post['payment_pts_stripe_client_id_live'];
		} else {
			$data['payment_pts_stripe_client_id_live'] = $this->config->get('payment_pts_stripe_client_id_live');
		}
		
		if (isset($this->request->post['payment_pts_stripe_total_min'])) {
			$data['payment_pts_stripe_total_min'] = $this->request->post['payment_pts_stripe_total_min'];
		} else {
			$data['payment_pts_stripe_total_min'] ='0';
			if($this->config->get('payment_pts_stripe_total_min')!=NULL){
			$data['payment_pts_stripe_total_min'] = $this->config->get('payment_pts_stripe_total_min');
			}
		}
		if (isset($this->request->post['payment_pts_stripe_total_max'])) {
			$data['payment_pts_stripe_total_max'] = $this->request->post['payment_pts_stripe_total_max'];
		} else {
			$data['payment_pts_stripe_total_max'] ='99999999';
			if($this->config->get('payment_pts_stripe_total_max')!=NULL){
			$data['payment_pts_stripe_total_max'] = $this->config->get('payment_pts_stripe_total_max');
			}
		}

		if (isset($this->request->post['payment_pts_stripe_debug'])) {
			$data['payment_pts_stripe_debug'] = $this->request->post['payment_pts_stripe_debug'];
		} else {
			$data['payment_pts_stripe_debug'] = $this->config->get('payment_pts_stripe_debug');
		}

		if (isset($this->request->post['payment_pts_stripe_geo_zone_id'])) {
			$data['payment_pts_stripe_geo_zone_id'] = $this->request->post['payment_pts_stripe_geo_zone_id'];
		} else {
			$data['payment_pts_stripe_geo_zone_id'] = $this->config->get('payment_pts_stripe_geo_zone_id');
		}

		if (isset($this->request->post['payment_pts_stripe_sort_order'])) {
			$data['payment_pts_stripe_sort_order'] = $this->request->post['payment_pts_stripe_sort_order'];
		} else {
			$data['payment_pts_stripe_sort_order'] = $this->config->get('payment_pts_stripe_sort_order');
		}

		if (isset($this->request->post['payment_pts_stripe_completed_status_id'])) {
			$data['payment_pts_stripe_completed_status_id'] = $this->request->post['payment_pts_stripe_completed_status_id'];
		} else {
			$data['payment_pts_stripe_completed_status_id'] = $this->config->get('payment_pts_stripe_completed_status_id');
		}
		
		
		/* if (isset($this->request->post['payment_pts_stripe_canceled_reversal_status_id'])) {
			$data['payment_pts_stripe_canceled_reversal_status_id'] = $this->request->post['payment_pts_stripe_canceled_reversal_status_id'];
		} else {
			$data['payment_pts_stripe_canceled_reversal_status_id'] = $this->config->get('payment_pts_stripe_canceled_reversal_status_id');
		} */

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_pp_adaptive_geo_zone_id'])) {
			$data['payment_pp_adaptive_geo_zone_id'] = $this->request->post['payment_pp_adaptive_geo_zone_id'];
		} else {
			$data['payment_pp_adaptive_geo_zone_id'] = $this->config->get('payment_pp_adaptive_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_pp_stripe_status'])) {
			$data['payment_pp_stripe_status'] = $this->request->post['payment_pp_stripe_status'];
		} else {
			$data['payment_pp_stripe_status'] = $this->config->get('payment_pp_stripe_status');
		}
		
		if (isset($this->request->post['payment_pp_stripe_payment_mode'])) {
			$data['payment_pp_stripe_payment_mode'] = $this->request->post['payment_pp_stripe_payment_mode'];
		} else {
			$data['payment_pp_stripe_payment_mode'] = $this->config->get('payment_pp_stripe_payment_mode');
		}

		if (isset($this->request->post['payment_pp_adaptive_sort_order'])) {
			$data['payment_pp_adaptive_sort_order'] = $this->request->post['payment_pp_adaptive_sort_order'];
		} else {
			$data['payment_pp_adaptive_sort_order'] = $this->config->get('payment_pp_adaptive_sort_order');
		}
// echo "<pre>";
// print_r($data);
// die;
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/pts_stripe', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/pp_adaptive')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_pts_stripe_title']) {
			$this->error['error_payment_method_title'] = $this->language->get('error_payment_method_title');
		}
		if (!$this->request->post['payment_pts_stripe_payment_mode']) {
		if (!$this->request->post['payment_pts_stripe_secret_key_test']) {
			$this->error['error_payment_secret_key_test'] =  $this->language->get('error_payment_secret_key_test');
		}
		
		if (!$this->request->post['payment_pts_stripe_publish_key_test']) {
			$this->error['error_payment_publish_key_test'] =  $this->language->get('error_payment_publish_key_test');
		}
		if (!$this->request->post['payment_pts_stripe_client_id_test']) {
			$this->error['error_payment_client_id_test'] =  $this->language->get('error_payment_client_id_test');
		}
		} else {
		if (!$this->request->post['payment_pts_stripe_secret_key_live']) {
			$this->error['error_payment_secret_key_live'] =  $this->language->get('error_payment_secret_key_live');
		}
		if (!$this->request->post['payment_pts_stripe_publish_key_live']) {
			$this->error['error_payment_publish_key_live'] =  $this->language->get('error_payment_publish_key_live');
		}
		
		if (!$this->request->post['payment_pts_stripe_client_id_live']) {
			$this->error['error_payment_client_id_live'] =  $this->language->get('error_payment_client_id_live');
		}
		} 
/* 		if($this->request->post['payment_pts_stripe_total_max']===''){
			$this->request->post['payment_pts_stripe_total_max']=99999999;
		}
		
		if($this->request->post['payment_pts_stripe_total_min']===''){
			$this->request->post['payment_pts_stripe_total_min']=0;
		} */
		
		if ($this->request->post['payment_pts_stripe_total_min'] < 0 and $this->request->post['payment_pts_stripe_total_min'] !== ''){
			$this->error['error_payment_total_min'] =  $this->language->get('error_payment_total_min_less');
		}elseif($this->request->post['payment_pts_stripe_total_min'] >= 0){
				$total_max=99999999;	
			if($this->request->post['payment_pts_stripe_total_max']!==''){
				$total_max=(float)$this->request->post['payment_pts_stripe_total_max'];	
			}

			if((float)$this->request->post['payment_pts_stripe_total_min'] >= $total_max){
			$this->error['error_payment_total_min'] =  $this->language->get('error_payment_total_min_greter');
			}
		}
		
		if ($this->request->post['payment_pts_stripe_total_max'] < 0 and $this->request->post['payment_pts_stripe_total_max'] !== ''){
			$this->error['error_payment_total_max'] =  $this->language->get('error_payment_total_max_less');
		}elseif( $this->request->post['payment_pts_stripe_total_max'] >= 0){
				$total_min=0;	
			if($this->request->post['payment_pts_stripe_total_min']!==''){
				$total_min=(float)$this->request->post['payment_pts_stripe_total_min'];	
			}
				$total_max=99999999;	
			if($this->request->post['payment_pts_stripe_total_max']!==''){
				$total_max=(float)$this->request->post['payment_pts_stripe_total_max'];	
			}
			if($total_max <= $total_min){
			$this->error['error_payment_total_max'] =  $this->language->get('error_payment_total_max_greter');
			}
		}

		return !$this->error;
	}
}
?>