<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		$store_url = rtrim($this->config->get('config_url'), '/');
		$store_name = $this->config->get('config_name');
		$data['json_ld_website'] = json_encode(array(
			'@context' => 'https://schema.org',
			'@type' => 'WebSite',
			'name' => $store_name,
			'url' => $store_url,
			'potentialAction' => array(
				'@type' => 'SearchAction',
				'target' => array('@type' => 'EntryPoint', 'urlTemplate' => $store_url . '/index.php?route=product/search&search={search_term_string}'),
				'query-input' => 'required name=search_term_string'
			)
		), JSON_UNESCAPED_SLASHES);
		$data['json_ld_organization'] = json_encode(array(
			'@context' => 'https://schema.org',
			'@type' => 'Organization',
			'name' => $store_name,
			'url' => $store_url
		), JSON_UNESCAPED_SLASHES);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
