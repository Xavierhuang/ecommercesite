<?php
class ModelExtensionlogprcatc extends Model {
	public function getcachedata($product_ids) {
		$json = array();
		$json['hideprc'] = $this->config->get('config_logprcatc_hideprc');
		
		$btntxt = $this->config->get('config_logprcatc_btntxt');
		$json['btntxt'] = html_entity_decode($btntxt[(int)$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
 		
		$product_ids = array_unique($product_ids);
		if($product_ids) { 
			$this->load->model('catalog/product');
			foreach ($product_ids as $pid) {
				if($this->checkpid($pid)) {
					$json[$pid] = $pid;
				}
			}
		}
		
		return $json;
	}
	
	public function checkpid($pid) {
		$rs['buyproduct'] = $this->config->get('config_logprcatc_incprd');
		$rs['buycategory'] = $this->config->get('config_logprcatc_inccat');
		$rs['buymanufacturer'] = $this->config->get('config_logprcatc_incman');
		
		$rs['outproduct'] = $this->config->get('config_logprcatc_exlprd');
		$rs['outcategory'] = $this->config->get('config_logprcatc_exlcat');
		$rs['outmanufacturer'] = $this->config->get('config_logprcatc_exlman');
		
 		$flag = false;
		
		// buy
		if(empty($rs['buyproduct']) && empty($rs['buycategory']) && empty($rs['buymanufacturer'])) {
			$flag = true;
		} else {
			if(! empty($rs['buyproduct'])) {
 				if(in_array($pid, $rs['buyproduct'])) {
					$flag = true;
				}
			}
			if(! empty($rs['buycategory']) && $rs['buycategory']) {
				foreach($rs['buycategory'] as $category_id) { 
					$catrow = $this->checkincat($category_id, $pid);
					if($catrow) {
						$flag = true; break;
					}
				}
			}
			if(! empty($rs['buymanufacturer']) && $rs['buymanufacturer']) {
				foreach($rs['buymanufacturer'] as $manuf_id) { 
					$manrow = $this->checkinman($manuf_id, $pid);
					if($manrow) {
						$flag = true; break;
					}
				}
			}
		}
		
		// lets check product exclude condition
		if(empty($rs['outproduct']) && empty($rs['outcategory']) && empty($rs['outmanufacturer'])) {
			// Do nothing						 
		} else {
			if(! empty($rs['outproduct'])) {
				if(in_array($pid, $rs['outproduct'])) {
					$flag = false;
				}
			}
			if(! empty($rs['outcategory']) && $rs['outcategory']) {
				foreach($rs['outcategory'] as $category_id) { 
					$catrow = $this->checkincat($category_id, $pid);
					if($catrow) {
						$flag = false; break;
					}
				}
			}
			if(! empty($rs['outmanufacturer']) && $rs['outmanufacturer']) {
				foreach($rs['outmanufacturer'] as $manuf_id) { 
					$manrow = $this->checkinman($manuf_id, $pid);
					if($manrow) {
						$flag = false; break;
					}
				}
			}
		}
		
		return $flag;
 	}
	public function checkincat($catid, $pid) {
		$subq = $this->db->query("SELECT GROUP_CONCAT(category_id) as subcatsid FROM " . DB_PREFIX . "category_path WHERE path_id = '".(int)$catid."' ");
		if(!empty($subq->row['subcatsid'])) {
			$catq = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE category_id in (".$subq->row['subcatsid'].") AND product_id = ".(int)$pid);
			if($catq->num_rows) {
				return $catq->rows;				
			}
		}
		return false;		
	}
	public function checkinman($manid, $pid) {
		$manq = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE manufacturer_id = '".(int)$manid."' AND product_id = ".(int)$pid);
		if($manq->num_rows) {
			return $manq->rows;				
		}
		return false;		
	}
	public function loadfooterjs() {
		if(!$this->customer->isLogged() && $this->config->get('config_logprcatc')) { 
			$this->document->addStyle('catalog/view/javascript/logprcatc/'.$this->config->get('config_logprcatc_themenm').'/common.css');
 			$this->document->addScript('catalog/view/javascript/logprcatc/'.$this->config->get('config_logprcatc_themenm').'/common.js');
		}
	}
}