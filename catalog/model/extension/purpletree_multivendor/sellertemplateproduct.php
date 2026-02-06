<?php
class ModelExtensionPurpletreeMultivendorSellertemplateproduct extends Model{
		
		public function getSellerProducts($data=array()){
			$sql = "SELECT ptc.category_id,cd.name,pvt.id,pvt.product_id,pvtps.id As seller_template_id,pvtps.seller_id,p.product_id,p.model,pvs.store_name AS store_name,pd.name AS product_name,p.image,pvtps.status,pvtps.quantity,pvtps.price,pvtps.stock_status_id,pvtps.template_id,ss.name as stock_status FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id) LEFT JOIN " . DB_PREFIX . "product_to_category ptc ON (ptc.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "category_description cd ON (cd.category_id = ptc.category_id)";	
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql.="AND pvt.product_id = p.product_id AND pvt.status !=0";		
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND pvtps.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}
			
			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND pvtps.quantity = '" . (int)$data['filter_quantity'] . "'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND pvtps.status = '" . (int)$data['filter_status'] . "'";
			}
			
			$sql .= " GROUP BY pvt.id";
			$sql .= " ORDER BY cd.name DESC";
			
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
				
				if ($data['limit'] < 1) {
					$data['limit'] = 5;
				}
				
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
			$query = $this->db->query($sql);
			return $query->rows;
		}
		
		public function getTotalSellerProducts($data = array()) {
			
			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total  FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";			
			$sql.="AND pvt.status = 1";	
			
			
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}
			
			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
			
			if (isset($data['status']) && !is_null($data['status'])) {
				$sql .= " AND p.status = '" . (int)$data['status'] . "'";
			}
			
			$query = $this->db->query($sql);
			
			return $query->row['total'];
		}	
		public function getProducts($data = array()) {
			$sql = "SELECT pvt.id,pvt.product_id,pvtps.id As seller_template_id,pvtps.seller_id,p.product_id,p.model,pvs.store_name AS store_name,pd.name AS product_name,p.image,pvtps.status,pvtps.quantity,pvtps.price,pvtps.stock_status_id,pvtps.template_id,ss.name as stock_status FROM " . DB_PREFIX . "purpletree_vendor_template pvt LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template_products pvtps ON (pvt.id = pvtps.template_id AND pvtps.seller_id= '".$this->customer->getId()."') LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "stock_status ss ON (ss.stock_status_id = pvtps.stock_status_id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id = pvtps.seller_id)";	
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			$sql.="AND pvt.product_id = p.product_id AND pvt.status !=0";		
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
			}
			
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
			}
			
			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
			}
			
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}		
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
				} else {
				$sql .= " ORDER BY pd.name";
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
		public function addProductTemplate($template_id, $data) {
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_vendor_template_products SET  template_id = '" . (int)$template_id . "',seller_id = '" . (int)$data['seller_id'] . "',  quantity = '" . (int)$data['quantity'] . "', subtract = '" . (int)$data['subtract'] . "', comment = '" . $this->db->escape($data['comment']) . "', t_description = '" . $this->db->escape($data['description']) . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', price = '" . (float)$data['price'] . "',status = '" . (int)$data['status'] . "'");		
		}
		public function editProductTemplate($id, $data) {
			$this->db->query("UPDATE " . DB_PREFIX . "purpletree_vendor_template_products SET    quantity = '" . (int)$data['quantity'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', price = '" . (float)$data['price'] . "', t_description = '" . $this->db->escape($data['description']) . "', comment = '" . $this->db->escape($data['comment']) . "',status = '" . (int)$data['status'] . "' WHERE id = '" . (int)$id . "'");
			
			// product  template option 
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option WHERE temp_product_id = '" . (int)$id . "'");
		   $this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_temp_product_option_value WHERE temp_product_id = '" . (int)$id . "'");
		
			if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$id . "', option_id = '" . (int)$product_option['option_id'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "purpletree_temp_product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$data['product_id'] . "', temp_product_id = '" . (int)$id . "', seller_id = '" . (int)$data['seller_id'] . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "',subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "'");
						}
					}
				}
			}
		}
		}	
		public function getMinPrice($product_id) {
			$sql = $this->db->query("SELECT MIN(price) AS min_price,pvtp.quantity,pvtp.stock_status_id,pvtp.subtract,pvtp.status AS subtract_status,pvt.product_id FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvtp.template_id = pvt.id) WHERE pvt.product_id = '" . (int)$product_id . "'AND pvtp.status = 1 AND quantity >=1 ");
			
			return $sql->row;
		}
		public function getProduct($id, $seller_id = NULL) {
			$sql = "SELECT pvtps.*,p.image,pd.name,pd.description FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtps  LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.id = pvtps.template_id) LEFT JOIN " . DB_PREFIX . "product p ON (pvt.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)WHERE pvtps.id = '" . (int)$id . "'AND pvtps.seller_id = '" . (int)$seller_id . "'";
			
			$query = $this->db->query($sql);
			return $query->row;
		}
		public function deleteProduct($template_id) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE id = '" . (int)$template_id . "'");		
		}
		public function getTemplatePrice($template_id) {
			
			$template_price = array();
			$query = $this->db->query("SELECT pvtp.*,pvs.store_name,pvs.store_logo,p.minimum FROM " . DB_PREFIX . "purpletree_vendor_template_products pvtp LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvtp.template_id = pvt.id) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_stores pvs ON (pvs.seller_id  = pvtp.seller_id) LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id  = pvt.product_id ) WHERE pvtp.template_id ='". (int)$template_id ."' AND pvs.store_status = 1");
			if($query->num_rows) {
				$template_price = $query->rows;
			}		
			return $template_price;
			
		}
		public function getStoreRating($seller_id){
			$query = $this->db->query("SELECT AVG(rating) as rating FROM " . DB_PREFIX . "purpletree_vendor_reviews where seller_id='".(int)$seller_id."' AND status=1");	
			return $query->row['rating'];
		}	
		public function updatePrice($minprice) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (int)$minprice['min_price'] . "',stock_status_id = '" . (int)$minprice['stock_status_id'] . "', subtract = '" . (int)$minprice['subtract'] . "', quantity = '" . (int)$minprice['quantity'] . "' WHERE product_id = '" . (int)$minprice['product_id'] . "'");
			
		}
		
		public function getTemplateInfo($tem_id) {		
			$template_info= array();
			$query = $this->db->query("SELECT p.image,pd.name,pd.description FROM ". DB_PREFIX . "product p  LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id  = p.product_id ) LEFT JOIN " . DB_PREFIX . "purpletree_vendor_template pvt ON (pvt.product_id  = p.product_id ) WHERE pvt.id ='". (int)$tem_id ."'");
			if($query->num_rows) {
				$template_info = $query->row;
			}
			return $template_info;
			
		}
		public function updateZero($temp_product_id) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET price = 0, quantity = 0 WHERE product_id = '" . (int)$temp_product_id . "'");
		}
		public function getTemplateProductId($temp_id) {
			$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE id =". (int)$temp_id);
			if($query->num_rows>0){
				$template_id = $query->row['product_id'];
				return $template_id;
			}
		}
		public function checkTempstatus($id) {
			$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template WHERE product_id ='".(int)$id."'");
			
			
			 if($query->num_rows){
			     $temp_id = $query->row['id'];
				 }else{
			     $temp_id = 0;
				 }
			 
			 $query1 = $this->db->query("SELECT id FROM " . DB_PREFIX . "purpletree_vendor_template_products WHERE template_id ='".(int)$temp_id."'AND status = 1");
			 if($query1->num_rows) {
				return 1;
				} else {
				return 0;
				}
		}
		
		
	public function getTempProductOptions($temp_product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "purpletree_temp_product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.temp_product_id = '" . (int)$temp_product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_temp_product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                   => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix']
				);
			}
			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value']
			);
		}

		return $product_option_data;
	}
public function getTemplateProductStatus($product_id,$option_id) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$option_id . "'");
			if($sql->num_rows){
			return $sql->row;
			}
			return null;
		}
		
	public function getTemplateProductOption($data) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$data['product_option_id'] . "' AND product_id = '" . (int)$data['product_id'] . "' AND option_id = '" . (int)$data['option_id'] . "' AND option_value_id = '" . (int)$data['option_value_id'] . "'");
			if($sql->num_rows){
				return true;
			}
			return false; 	 	
		}
	 public function getOptionValuesByProductOptId($option_id,$product_id) {
			$option_value_data = array();

			$option_value_query = $this->db->query("SELECT pov.option_value_id,ovd.name,ov.image,ov.sort_order FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.option_id = '" . (int)$option_id . "' AND pov.product_id = '" . (int)$product_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order, ovd.name");


			foreach ($option_value_query->rows as $option_value) {
				$option_value_data[] = array(
					'option_value_id' => $option_value['option_value_id'],
					'name'            => $option_value['name'],
					'image'           => $option_value['image'],
					'sort_order'      => $option_value['sort_order']
				);
			}
			return $option_value_data;
		}
		public function getProductId($id) {
			$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "purpletree_vendor_template WHERE id = '" . (int)$id . "'");
			if($sql->num_rows){
				return $sql->row['product_id'];
			}
			return null; 	 	
		}
}
?>