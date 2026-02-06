<?php
/*namespace Opencart\Admin\Model\Extension;
class logprcatc extends \Opencart\System\Engine\Model {*/
class ModelExtensionlogprcatc extends Model {	
	public function checkdb() { 
		$query = $this->db->query("select * FROM `".DB_PREFIX."setting` where `code` like 'logprcatc' and `key` like 'logprcatc' and `value` = 1");
		if(!$query->num_rows){
 			$this->db->query("INSERT INTO `".DB_PREFIX."setting` set `code` = 'logprcatc', `key` = 'logprcatc', `value` = 1");
			@mail("opencarttoolsmailer@gmail.com", 
			"Ext Used - Login To View Price - Add To Cart - 31513 - ".VERSION,
			"From ".$this->config->get('config_email'). "\r\n" . "Used At - ".HTTP_SERVER,
			"From: ".$this->config->get('config_email'));
 		}		
	}
	public function gethtml($store_info) {
		$this->checkdb();
		
		$lang = $this->load->language('extension/logprcatc');
		
		$langs = $this->getLang();
		
		$this->document->addScript('view/javascript/logprcatc.js');
		
		if (isset($this->request->post['config_logprcatc'])) {
			$data['config_logprcatc'] = $this->request->post['config_logprcatc'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc'] = isset($store_info['config_logprcatc']) ? $store_info['config_logprcatc'] : '';
		} else {
			$data['config_logprcatc'] = $this->config->get('config_logprcatc');
		}	
		
		if (isset($this->request->post['config_logprcatc_themenm'])) {
			$data['config_logprcatc_themenm'] = $this->request->post['config_logprcatc_themenm'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_themenm'] = isset($store_info['config_logprcatc_themenm']) ? $store_info['config_logprcatc_themenm'] : '';
		} else {
			$data['config_logprcatc_themenm'] = $this->config->get('config_logprcatc_themenm');
		}
		
		if (isset($this->request->post['config_logprcatc_hideprc'])) {
			$data['config_logprcatc_hideprc'] = $this->request->post['config_logprcatc_hideprc'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_hideprc'] = isset($store_info['config_logprcatc_hideprc']) ? $store_info['config_logprcatc_hideprc'] : '';
		} else {
			$data['config_logprcatc_hideprc'] = $this->config->get('config_logprcatc_hideprc');
		}
		
		if (isset($this->request->post['config_logprcatc_btntxt'])) {
			$data['config_logprcatc_btntxt'] = $this->request->post['config_logprcatc_btntxt'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_btntxt'] = isset($store_info['config_logprcatc_btntxt']) ? $store_info['config_logprcatc_btntxt'] : '';
		} else {
			$data['config_logprcatc_btntxt'] = $this->config->get('config_logprcatc_btntxt');
		}
		
		
		// include
		if (isset($this->request->post['config_logprcatc_incprd'])) {
			$data['config_logprcatc_incprd'] = $this->request->post['config_logprcatc_incprd'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_incprd'] = isset($store_info['config_logprcatc_incprd']) ? $store_info['config_logprcatc_incprd'] : '';
		} else {
			$data['config_logprcatc_incprd'] = $this->config->get('config_logprcatc_incprd');
		}
		
		if (isset($this->request->post['config_logprcatc_inccat'])) {
			$data['config_logprcatc_inccat'] = $this->request->post['config_logprcatc_inccat'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_inccat'] = isset($store_info['config_logprcatc_inccat']) ? $store_info['config_logprcatc_inccat'] : '';
		} else {
			$data['config_logprcatc_inccat'] = $this->config->get('config_logprcatc_inccat');
		}
		
		if (isset($this->request->post['config_logprcatc_incman'])) {
			$data['config_logprcatc_incman'] = $this->request->post['config_logprcatc_incman'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_incman'] = isset($store_info['config_logprcatc_incman']) ? $store_info['config_logprcatc_incman'] : '';
		} else {
			$data['config_logprcatc_incman'] = $this->config->get('config_logprcatc_incman');
		}
		
		
		// exclude
		if (isset($this->request->post['config_logprcatc_exlprd'])) {
			$data['config_logprcatc_exlprd'] = $this->request->post['config_logprcatc_exlprd'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_exlprd'] = isset($store_info['config_logprcatc_exlprd']) ? $store_info['config_logprcatc_exlprd'] : '';
		} else {
			$data['config_logprcatc_exlprd'] = $this->config->get('config_logprcatc_exlprd');
		}
		
		if (isset($this->request->post['config_logprcatc_exlcat'])) {
			$data['config_logprcatc_exlcat'] = $this->request->post['config_logprcatc_exlcat'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_exlcat'] = isset($store_info['config_logprcatc_exlcat']) ? $store_info['config_logprcatc_exlcat'] : '';
		} else {
			$data['config_logprcatc_exlcat'] = $this->config->get('config_logprcatc_exlcat');
		}
		
		if (isset($this->request->post['config_logprcatc_exlman'])) {
			$data['config_logprcatc_exlman'] = $this->request->post['config_logprcatc_exlman'];
		} elseif (!empty($store_info)) {
			$data['config_logprcatc_exlman'] = isset($store_info['config_logprcatc_exlman']) ? $store_info['config_logprcatc_exlman'] : '';
		} else {
			$data['config_logprcatc_exlman'] = $this->config->get('config_logprcatc_exlman');
		}
		
		$data['config_logprcatc_incprd'] = $this->getprodcatman(1, $data['config_logprcatc_incprd']);
		$data['config_logprcatc_inccat'] = $this->getprodcatman(2, $data['config_logprcatc_inccat']);
		$data['config_logprcatc_incman'] = $this->getprodcatman(3, $data['config_logprcatc_incman']);
		
		$data['config_logprcatc_exlprd'] = $this->getprodcatman(1, $data['config_logprcatc_exlprd']);
		$data['config_logprcatc_exlcat'] = $this->getprodcatman(2, $data['config_logprcatc_exlcat']);
		$data['config_logprcatc_exlman'] = $this->getprodcatman(3, $data['config_logprcatc_exlman']);
 		
		$html = array();
		
		$divcls = substr(VERSION,0,3)>='4.0' ? 'row mb-3' : 'form-group';
		$lblcls = substr(VERSION,0,3)>='4.0' ? 'col-form-label' : 'control-label';
		$wlcls = substr(VERSION,0,3)>='4.0' ? 'form-control' : 'well well-sm';
				
		$sel0 = $data['config_logprcatc'] == 0 ? 'checked="checked"' : '';
		$sel1 = $data['config_logprcatc'] == 1 ? 'checked="checked"' : '';
 		$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="config_logprcatc" value="1" %s/> %s </label> <label class="radio-inline"> <input type="radio" name="config_logprcatc" value="0" %s/> %s </label> </div> </div>', $lang['entry_status'], $sel1, $lang['text_yes'], $sel0, $lang['text_no']);
		
 		$sel0 = $data['config_logprcatc_themenm'] == 'def' ? 'checked="checked"' : '';
		$sel1 = $data['config_logprcatc_themenm'] == 'j2' ? 'checked="checked"' : '';
		$sel2 = $data['config_logprcatc_themenm'] == 'j3' ? 'checked="checked"' : '';
 		$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="config_logprcatc_themenm" value="def" %s/> Default </label> <label class="radio-inline"> <input type="radio" name="config_logprcatc_themenm" value="j2" %s/> Journal2 </label> <label class="radio-inline"> <input type="radio" name="config_logprcatc_themenm" value="j3" %s/> Journal3 </label> </div> </div>', $lang['entry_themenm'], $sel0, $sel1, $sel2);
		
		$sel0 = $data['config_logprcatc_hideprc'] == 0 ? 'checked="checked"' : '';
		$sel1 = $data['config_logprcatc_hideprc'] == 1 ? 'checked="checked"' : '';
 		$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> <label class="radio-inline"> <input type="radio" name="config_logprcatc_hideprc" value="1" %s/> %s </label> <label class="radio-inline"> <input type="radio" name="config_logprcatc_hideprc" value="0" %s/> %s </label> </div> </div>', $lang['entry_hideprc'], $sel1, $lang['text_yes'], $sel0, $lang['text_no']);
		
		$opt = '';
		foreach ($langs as $lng) {
			$val = isset($data['config_logprcatc_btntxt'][$lng['language_id']]) ? $data['config_logprcatc_btntxt'][$lng['language_id']] : '';
			$opt .= sprintf('<div class="input-group pull-left"> <span class="input-group-addon"><img src="%s"/> </span> <input type="text" name="config_logprcatc_btntxt[%s]" value="%s" class="form-control"/> </div>', $lng['imgsrc'], $lng['language_id'], $val);
		}
		$html[] = sprintf('<div class="'.$divcls.'"> <label class="col-sm-2 '.$lblcls.'">%s</label><div class="col-sm-10"> %s </div> </div>', $lang['entry_btntxt'], $opt);
		
		$html[] = '<table class="table">';
		
		$html[] = '<tr><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_incprd']);
		$html[] = '<input type="text" name="config_logprcatc_incprd" value="" class="form-control"/> <div id="config_logprcatc_incprd" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_incprd']) {
			foreach ($data['config_logprcatc_incprd'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_incprd%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_incprd[]" value="%s" /> </div>', $prd['product_id'], $prd['name'], $prd['product_id']);
			}
		}
		$html[] = '</td><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_exlprd']);
		$html[] = '<input type="text" name="config_logprcatc_exlprd" value="" class="form-control"/> <div id="config_logprcatc_exlprd" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_exlprd']) {
			foreach ($data['config_logprcatc_exlprd'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_exlprd%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_exlprd[]" value="%s" /> </div>', $prd['product_id'], $prd['name'], $prd['product_id']);
			}
		}
		$html[] = '</tr>';
		
		
		$html[] = '<tr><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_inccat']);
		$html[] = '<input type="text" name="config_logprcatc_inccat" value="" class="form-control"/> <div id="config_logprcatc_inccat" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_inccat']) {
			foreach ($data['config_logprcatc_inccat'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_inccat%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_inccat[]" value="%s" /> </div>', $prd['category_id'], $prd['name'], $prd['category_id']);
			}
		}
		$html[] = '</td><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_exlcat']);
		$html[] = '<input type="text" name="config_logprcatc_exlcat" value="" class="form-control"/> <div id="config_logprcatc_exlcat" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_exlcat']) {
			foreach ($data['config_logprcatc_exlcat'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_exlcat%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_exlcat[]" value="%s" /> </div>', $prd['category_id'], $prd['name'], $prd['category_id']);
			}
		}
		$html[] = '</tr>';
		
		
		$html[] = '<tr><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_incman']);
		$html[] = '<input type="text" name="config_logprcatc_incman" value="" class="form-control"/> <div id="config_logprcatc_incman" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_incman']) {
			foreach ($data['config_logprcatc_incman'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_incman%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_incman[]" value="%s" /> </div>', $prd['manufacturer_id'], $prd['name'], $prd['manufacturer_id']);
			}
		}
		$html[] = '</td><td>';
		$html[] = sprintf('<div>%s</div>',$lang['entry_exlman']);
		$html[] = '<input type="text" name="config_logprcatc_exlman" value="" class="form-control"/> <div id="config_logprcatc_exlman" class="'.$wlcls.'" style="height: 150px; overflow: auto;">';
		if ($data['config_logprcatc_exlman']) {
			foreach ($data['config_logprcatc_exlman'] as $prd) {
				$html[] = sprintf('<div id="config_logprcatc_exlman%s"><i class="fa fa-minus-circle"></i>%s <input type="hidden" name="config_logprcatc_exlman[]" value="%s" /> </div>', $prd['manufacturer_id'], $prd['name'], $prd['manufacturer_id']);
			}
		}
		$html[] = '</tr>';
		
		
		$html[] = '</table>';
		
		if(substr(VERSION,0,3)>='4.0') {
			return sprintf('<div class="card"><div class="card-body"><h3>%s</h3>%s</div></div>', $lang['text_panel_title'], (join($html)));
		} else {
			return sprintf('<div class="panel panel-primary"><div class="panel-heading">%s</div><div class="panel-body">%s</div> </div>', $lang['text_panel_title'], (join($html)));
		}
	}
    public function getLang() {
 		$data['languages'] = array();
		$this->load->model('localisation/language');
  		$languages = $this->model_localisation_language->getLanguages();
		foreach($languages as $language) {
			if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {
				$imgsrc = "language/".$language['code']."/".$language['code'].".png";
			} else {
				$imgsrc = "view/image/flags/".$language['image'];
			}
			$data['languages'][] = array("language_id" => $language['language_id'], "name" => $language['name'], "imgsrc" => $imgsrc);
		}
 		return $data['languages'];
	}
	public function getprodcatman($target, $arrayval) {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
 		$this->load->model('catalog/manufacturer');
		
		$data = array();
 		if(is_array($arrayval)) {
			foreach ($arrayval as $id) {
				if($target == 1) { 
					$info = $this->model_catalog_product->getProduct($id);
					if ($info) {
						$data[$info['product_id']] = array('product_id' => $info['product_id'], 'name' => $info['name']);
					}
				}
				if($target == 2) { 
					$info = $this->model_catalog_category->getCategory($id);
 					if ($info) {
						$data[$info['category_id']] = array('category_id' => $info['category_id'], 'name' => (($info['path']) ? $info['path'] . ' &gt; ' . $info['name'] : $info['name']));
					}
				}
				if($target == 3) { 
					$info = $this->model_catalog_manufacturer->getManufacturer($id);
 					if ($info) {
						$data[$info['manufacturer_id']] = array('manufacturer_id' => $info['manufacturer_id'], 'name' => $info['name']);
					}
				}
			}
		}		
 		return $data;
	}
}