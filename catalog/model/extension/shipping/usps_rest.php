<?php
class ModelExtensionShippingUspsRest extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/usps_rest');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_usps_rest_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_usps_rest_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!empty($address['iso_code_2']) && $address['iso_code_2'] !== 'US') {
			$status = false;
		}

		$method_data = array();

		if (!$status || !$this->config->get('shipping_usps_rest_status')) {
			return $method_data;
		}

		$key = $this->config->get('shipping_usps_rest_consumer_key');
		$secret = $this->config->get('shipping_usps_rest_consumer_secret');
		$origin = $this->config->get('shipping_usps_rest_origin_zip');
		if ($key === '' || $key === null || $secret === '' || $secret === null || $origin === '' || $origin === null) {
			return $method_data;
		}

		$weight_class_id = $this->config->get('shipping_usps_rest_weight_class_id');
		if (!$weight_class_id) {
			$weight_class_id = $this->config->get('config_weight_class_id');
		}

		$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $weight_class_id);

		if ($weight > 70) {
			return $method_data;
		}

		$weight = ($weight < 0.1 ? 0.1 : $weight);

		$postcode = str_replace(' ', '', $address['postcode']);

		require_once DIR_SYSTEM . 'library/usps_rest.php';
		$usps = new UspsRest($this->registry);

		$options = array(
			'client_id'                       => $key,
			'client_secret'                   => $secret,
			'use_tem'                         => $this->config->get('shipping_usps_rest_use_tem'),
			'origin_zip'                      => $origin,
			'dest_zip'                        => $postcode,
			'weight'                          => $weight,
			'length'                          => (float)$this->config->get('shipping_usps_rest_length'),
			'width'                           => (float)$this->config->get('shipping_usps_rest_width'),
			'height'                          => (float)$this->config->get('shipping_usps_rest_height'),
			'mail_class'                      => $this->config->get('shipping_usps_rest_mail_class') ?: 'USPS_GROUND_ADVANTAGE',
			'processing_category'             => 'MACHINABLE',
			'rate_indicator'                  => 'SP',
			'destination_entry_facility_type' => 'NONE',
			'price_type'                      => 'RETAIL',
			'debug'                           => $this->config->get('shipping_usps_rest_debug'),
		);

		$result = $usps->getDomesticBaseRate($options);

		if (!$result['ok'] || $result['cost'] < 0) {
			if ($this->config->get('shipping_usps_rest_debug')) {
				$this->log->write('USPS REST quote failed: ' . $result['error']);
			}
			return $method_data;
		}

		$cost_usd = (float)$result['cost'];
		$cost = $this->currency->convert($cost_usd, 'USD', $this->config->get('config_currency'));

		$title = $this->language->get('text_title');
		if ($result['title'] !== '') {
			$title .= ' - ' . $result['title'];
		}
		if ($this->config->get('shipping_usps_rest_display_weight')) {
			$title .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $weight_class_id) . ')';
		}

		$quote_data = array();

		$quote_data['usps_rest'] = array(
			'code'         => 'usps_rest.usps_rest',
			'title'        => $title,
			'cost'         => $cost,
			'tax_class_id' => $this->config->get('shipping_usps_rest_tax_class_id'),
			'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_usps_rest_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
		);

		$method_data = array(
			'code'       => 'usps_rest',
			'title'      => $this->language->get('text_title'),
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('shipping_usps_rest_sort_order'),
			'error'      => false
		);

		return $method_data;
	}
}
