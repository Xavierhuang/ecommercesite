<?php
class ControllerExtensionShippingUspsRest extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/usps_rest');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_usps_rest', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['consumer_key'])) {
			$data['error_consumer_key'] = $this->error['consumer_key'];
		} else {
			$data['error_consumer_key'] = '';
		}

		if (isset($this->error['consumer_secret'])) {
			$data['error_consumer_secret'] = $this->error['consumer_secret'];
		} else {
			$data['error_consumer_secret'] = '';
		}

		if (isset($this->error['origin_zip'])) {
			$data['error_origin_zip'] = $this->error['origin_zip'];
		} else {
			$data['error_origin_zip'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/usps_rest', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/usps_rest', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		$fields = array(
			'shipping_usps_rest_consumer_key',
			'shipping_usps_rest_consumer_secret',
			'shipping_usps_rest_use_tem',
			'shipping_usps_rest_origin_zip',
			'shipping_usps_rest_length',
			'shipping_usps_rest_width',
			'shipping_usps_rest_height',
			'shipping_usps_rest_mail_class',
			'shipping_usps_rest_weight_class_id',
			'shipping_usps_rest_tax_class_id',
			'shipping_usps_rest_geo_zone_id',
			'shipping_usps_rest_status',
			'shipping_usps_rest_sort_order',
			'shipping_usps_rest_debug',
			'shipping_usps_rest_display_weight',
		);

		foreach ($fields as $f) {
			if (isset($this->request->post[$f])) {
				$data[$f] = $this->request->post[$f];
			} else {
				$data[$f] = $this->config->get($f);
			}
		}

		if ($data['shipping_usps_rest_length'] === '' || $data['shipping_usps_rest_length'] === null) {
			$data['shipping_usps_rest_length'] = '6';
		}
		if ($data['shipping_usps_rest_width'] === '' || $data['shipping_usps_rest_width'] === null) {
			$data['shipping_usps_rest_width'] = '4';
		}
		if ($data['shipping_usps_rest_height'] === '' || $data['shipping_usps_rest_height'] === null) {
			$data['shipping_usps_rest_height'] = '3';
		}
		if ($data['shipping_usps_rest_mail_class'] === '' || $data['shipping_usps_rest_mail_class'] === null) {
			$data['shipping_usps_rest_mail_class'] = 'USPS_GROUND_ADVANTAGE';
		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/weight_class');
		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/usps_rest', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/usps_rest')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->request->post['shipping_usps_rest_status']) {
			if (!utf8_strlen($this->request->post['shipping_usps_rest_consumer_key'])) {
				$this->error['consumer_key'] = $this->language->get('error_consumer_key');
			}
			if (!utf8_strlen($this->request->post['shipping_usps_rest_consumer_secret'])) {
				$this->error['consumer_secret'] = $this->language->get('error_consumer_secret');
			}
			if (!utf8_strlen($this->request->post['shipping_usps_rest_origin_zip'])) {
				$this->error['origin_zip'] = $this->language->get('error_origin_zip');
			}
		}

		return !$this->error;
	}
}
