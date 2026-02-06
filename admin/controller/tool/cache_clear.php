<?php
class ControllerToolCacheClear extends Controller {
	public function index() {
		$this->load->language('tool/cache_clear');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/cache_clear', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];
		$data['clear'] = $this->url->link('tool/cache_clear/clear', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/cache_clear', $data));
	}

	public function clear() {
		$this->load->language('tool/cache_clear');

		if (!$this->user->hasPermission('modify', 'tool/cache_clear')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			// Clear all cache keys
			$cache_keys = array(
				'product',
				'category',
				'manufacturer',
				'information',
				'blog_category',
				'blog_post',
				'image',
				'seo_pro',
				'seo_url'
			);

			foreach ($cache_keys as $key) {
				$this->cache->delete($key);
			}

			// Clear file cache if exists
			$files = glob(DIR_CACHE . '*');
			
			if ($files) {
				foreach ($files as $file) {
					if (is_file($file) && basename($file) != 'index.html') {
						@unlink($file);
					}
				}
			}

			// Clear image cache
			$image_cache_files = glob(DIR_IMAGE . 'cache/*');
			
			if ($image_cache_files) {
				foreach ($image_cache_files as $file) {
					if (is_file($file)) {
						@unlink($file);
					}
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$this->response->redirect($this->url->link('tool/cache_clear', 'user_token=' . $this->session->data['user_token'], true));
	}
}
