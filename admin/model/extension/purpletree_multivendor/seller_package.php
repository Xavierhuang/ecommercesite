<?php
class ModelExtensionPurpletreeMultivendorSellerPackage extends Model {
	public function addSellerPackage($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "seller_package SET name = '" . $this->db->escape($data['name'])."', type = '".(int)$data['type']."', recurring_id = '".(int)$data['recurring_id']."', amount = '" . $this->db->escape($data['amount']) . "', commission = '" . $this->db->escape($data['commission']) . "', date_added = NOW()");

		$customer_id = $this->db->getLastId();
		
		return $customer_id;
	}

	public function editSellerPackage($seller_package_id, $data) {		
		$this->db->query("UPDATE " . DB_PREFIX . "seller_package SET name = '" . $this->db->escape($data['name'])."', type = '".(int)$data['type']."', recurring_id = '".(int)$data['recurring_id']."', amount = '" . $this->db->escape($data['amount']) . "', commission = '" . $this->db->escape($data['commission']) . "', date_modified = NOW() WHERE seller_package_id = '" . (int)$seller_package_id . "'");
	}
	
	public function deleteSellerPackage($seller_package_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seller_package WHERE customer_id = '" . (int)$seller_package_id . "'");
	}

	public function getSellerPackage($seller_package_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "seller_package WHERE seller_package_id = '" . (int)$seller_package_id . "'");

		return $query->row;
	}

	public function getSellerPackages($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "seller_package`";

		$sort_data = array('name');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalSellerPackages () {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "seller_package`");

		return $query->row['total'];
	}
}
