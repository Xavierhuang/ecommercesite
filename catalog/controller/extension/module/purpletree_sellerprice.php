<?php
class ControllerExtensionModulePurpletreeSellerprice extends Controller {
		public function index($data=array()) {
			if($this->config->get('module_purpletree_multivendor_seller_product_template')){
                $template = $this->config->get('config_template');
                if (strpos($template ?? '', 'journal2') === 0) {
                    $data['journal2'] = 1;
                }
				/*if (strpos($this->config->get('config_template'), 'journal2') === 0){
					$data['journal2'] = 1;
				}*/	

				if(isset($this->request->get['product_id']) || isset($data['prod_id']))
				{
					if(isset($data['prod_id'])){
					$this->request->get['product_id']=$data['prod_id'];
					}
					$data['price_list_display']=true;
					$data['option_name']='';
					$this->load->language('extension/module/purpletree_sellerprice');
					$this->load->model('extension/module/purpletree_sellerprice');
					$this->load->model('tool/image');
					$data['template_prices'] = array(); 
					$seller_prices = $this->model_extension_module_purpletree_sellerprice->getTemplatePrice($this->request->get['product_id']);	
					if(isset($data['prod_id']))
					{
						if($data['product_option_id']!='' && $data['product_option_value_id']==''){
							$data['price_list_display']=false;
							$option_id = $this->model_extension_module_purpletree_sellerprice->getOptionByProduct($data['product_option_id'],$data['prod_id']);
							$data['option_name']=sprintf($this->language->get('text_price_msg'),$this->model_extension_module_purpletree_sellerprice->getOption($option_id));						
						}

						$SellersTemplatePrice=$seller_prices;
						$tempSellerPrices = $this->model_extension_module_purpletree_sellerprice->getOptionData($data);
						$seller_prices=array();
						if(!empty($SellersTemplatePrice)){
							foreach($SellersTemplatePrice as $SellerTemplatePrice){
								$temp_var=1;
								if(!empty($tempSellerPrices)){
									foreach($tempSellerPrices as $tempSellerPrice){
										if($SellerTemplatePrice['seller_id']==$tempSellerPrice['seller_id']){
											$seller_prices[]=$tempSellerPrice;
											$temp_var=0;
										}
									}	
								}
								if($temp_var){
									$option_id = $this->model_extension_module_purpletree_sellerprice->getOptionByProduct($data['product_option_id'],$data['prod_id']);
								$checkData=array(
									'product_id'=>$this->request->get['product_id'],
									'seller_temp_prod_id'=>$SellerTemplatePrice['id'],
									'seller_id'=>$SellerTemplatePrice['seller_id'],
									'option_id'=>$option_id
								);
								$chkOptValue = $this->model_extension_module_purpletree_sellerprice->checkMultipleProductOptionValue($checkData);

									if(!$chkOptValue){
										$seller_prices[]=$SellerTemplatePrice;	
									}
								}
							}
						}
					}
					if(isset($this->request->get['product_id'])){
						$sellerOptions = $this->model_extension_module_purpletree_sellerprice->getTemlateProductCheckWTOption($this->request->get['product_id']);
						if(!$sellerOptions){
							$data['price_list_display']=true;
							$seller_prices = $this->model_extension_module_purpletree_sellerprice->getTemplatePrice($this->request->get['product_id']);		
						}
					}					

					$data['text_seller_price'] = $this->language->get('text_seller_price');
					$data['button_add_cart'] = $this->language->get('button_add_cart');
					$data['module_purpletree_multivendor_seller_product_template'] = $this->config->get('module_purpletree_multivendor_seller_product_template');
					$templateProductData= $this->filterDataWithHyperlocal($seller_prices);

					if(!empty($templateProductData)) {         	
						foreach($templateProductData as $seller_price){
							if ($seller_price['store_logo']) {
								$image = $this->model_tool_image->resize($seller_price['store_logo'], '200' , '200');
								}else {
								$image = $this->model_tool_image->resize('placeholder.png', '200' , '200');
							}
							if(isset($seller_price['price_prefix'])){
							if ($seller_price['price_prefix'] == '+') {
									$seller_price['price'] += $seller_price['option_price'];
									
								} elseif ($seller_price['price_prefix'] == '-') {
									$seller_price['price'] -= $seller_price['option_price'];;
								}
							}
								
							if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
								$price = $this->currency->format($this->tax->calculate($seller_price['price'] , $seller_price['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
								$price = false;
							}
							if($seller_price['vacation'] !=1){
								$rating = $this->model_extension_module_purpletree_sellerprice->getStoreRating($seller_price['seller_id']);
								
								$data['template_prices'][] = array(
								'product_id'  => $this->request->get['product_id'],
								'href'        => $this->url->link('extension/account/purpletree_multivendor/sellerstore/storeview', 'seller_store_id=' . $seller_price['storeidd'],true),
								'template_id'  => $seller_price['template_id'],
								'store_logo'       => $image,						
								'store_name'        => $seller_price['store_name'],
								'seller_id'        => $seller_price['seller_id'],						
								'price'       => $price,
								'status'      =>$seller_price['status'],
								'rating'      =>$rating,
								'minimum'      =>$seller_price['minimum']					 
								);
							}
							
						}
						}
						if(isset($this->request->get['product_id'])){
							$tempData = $this->model_extension_module_purpletree_sellerprice->getTemplatePrice($this->request->get['product_id']);
						} else if(isset($data['prod_id'])){
							$tempData = $this->model_extension_module_purpletree_sellerprice->getTemplatePrice($data['prod_id']);
						} else {
							$tempData=array();
						}

						if(!empty($tempData)){
							$data['currenttheme'] = $this->config->get('theme_default_directory');
							$direction = $this->language->get('direction');
		 if ($direction=='rtl'){
			$this->document->addStyle('catalog/view/javascript/purpletree/bootstrap/css/bootstrap.min-a.css');
			$this->document->addStyle('catalog/view/theme/default/stylesheet/purpletree/custom-a.css'); 
			} else {
			$this->document->addStyle('catalog/view/javascript/purpletree/bootstrap/css/bootstrap.min.css'); 
			$this->document->addStyle('catalog/view/theme/default/stylesheet/purpletree/custom.css'); 
			}
			$this->document->addStyle('catalog/view/javascript/purpletree/css/stylesheet/commonstylesheet.css');
							return $this->load->view('extension/module/purpletree_sellerprice', $data);
						}
				}
			}
		}
		public function filterDataWithHyperlocal($seller_prices){
				$templateProductData=array();					
				if(!empty($seller_prices)) {      
						foreach($seller_prices as $seller_data){
							$sellerStoreArea = $this->model_extension_module_purpletree_sellerprice->getSellerStoreArea($seller_data['seller_id']);	
							$seller_area=0;
							if(isset($this->session->data['seller_area'])){
								$seller_area=(int)$this->session->data['seller_area'];
							}
							if($seller_area && !empty($sellerStoreArea)){
							if(in_array($seller_area,$sellerStoreArea)){
								$templateProductData[] = $seller_data;
							}
							} else {
								$templateProductData[] = $seller_data;
							}
						}
					}
			return $templateProductData;
		}
}