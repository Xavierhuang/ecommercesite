<?php
/**
 * Admin: List and manage reseller/tax exemption certificates.
 * Approve or reject pending certificates; once approved, sales tax is removed for that customer.
 */
class ControllerCustomerTaxExemption extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('customer/tax_exemption');
		$this->document->setTitle($this->language->get('heading_title'));
		if (!$this->user->hasPermission('access', 'customer/tax_exemption')) {
			$this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}
		$this->getList();
	}

	public function approve() {
		$this->load->language('customer/tax_exemption');
		if (!$this->user->hasPermission('modify', 'customer/tax_exemption')) {
			$this->session->data['error_warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}
		$exemption_id = isset($this->request->get['exemption_id']) ? (int)$this->request->get['exemption_id'] : 0;
		if ($exemption_id < 1) {
			$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}
		if (!class_exists('TaxExemptionHelper')) {
			require_once(DIR_SYSTEM . 'library/tax_exemption_helper.php');
		}
		$helper = new TaxExemptionHelper($this->db, $this->config);
		$helper->approveExemption($exemption_id, $this->user->getId());
		$this->session->data['success'] = $this->language->get('text_approve_success');
		$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function reject() {
		$this->load->language('customer/tax_exemption');
		if (!$this->user->hasPermission('modify', 'customer/tax_exemption')) {
			$this->session->data['error_warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}
		$exemption_id = isset($this->request->post['exemption_id']) ? (int)$this->request->post['exemption_id'] : (isset($this->request->get['exemption_id']) ? (int)$this->request->get['exemption_id'] : 0);
		$reason = isset($this->request->post['rejection_reason']) ? $this->request->post['rejection_reason'] : '';
		if ($exemption_id < 1) {
			$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}
		if (!class_exists('TaxExemptionHelper')) {
			require_once(DIR_SYSTEM . 'library/tax_exemption_helper.php');
		}
		$helper = new TaxExemptionHelper($this->db, $this->config);
		$helper->rejectExemption($exemption_id, $this->user->getId(), $reason);
		$this->session->data['success'] = $this->language->get('text_reject_success');
		$this->response->redirect($this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true));
	}

	protected function getList() {
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}
		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];
		$data['exemptions'] = array();

		try {
			$sql = "SELECT e.*, CONCAT(c.firstname, ' ', c.lastname) AS customer_name, c.email
				FROM " . DB_PREFIX . "customer_tax_exemption e
				LEFT JOIN " . DB_PREFIX . "customer c ON (e.customer_id = c.customer_id)
				WHERE 1=1";
			if ($filter_status !== '') {
				$sql .= " AND e.status = '" . $this->db->escape($filter_status) . "'";
			}
			$sql .= " ORDER BY e.date_added DESC";
			$query = $this->db->query($sql);
			$data['exemptions'] = $query->rows;
		} catch (\Throwable $e) {
			$data['error_table'] = $this->language->get('error_table_missing');
			$data['exemptions'] = array();
		}

		$data['filter_status'] = $filter_status;
		$data['text_pending'] = $this->language->get('text_status_pending');
		$data['text_approved'] = $this->language->get('text_status_approved');
		$data['text_rejected'] = $this->language->get('text_status_rejected');
		$data['catalog_url'] = (defined('HTTP_CATALOG') ? HTTP_CATALOG : rtrim($this->config->get('config_url'), '/'));
		$data['action_filter'] = $this->url->link('customer/tax_exemption', 'user_token=' . $this->session->data['user_token'], true);
		$data['approve_url'] = $this->url->link('customer/tax_exemption/approve', 'user_token=' . $this->session->data['user_token'] . '&exemption_id=', true);
		$data['reject_url'] = $this->url->link('customer/tax_exemption/reject', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}
		if (isset($data['error_table'])) {
			$data['error_warning'] = $data['error_table'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('customer/tax_exemption', $data));
	}
}
