<?php

/**
* @version [Supported opencart version 3.x.x.x.]
* @category Webkul
* @package Opencart Marketplace Stripe Payment
* @author [Webkul] <[<http://webkul.com/>]>
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

// Heading
$_['heading_title']       				= 'Stripe Payment Method';
$_['text_extension']              = 'Extension';

$_['button_close'] 		  				= 'Close';
$_['button_save']         				= 'Save';

$_['tab_general'] 		  				= 'General Management';
$_['tab_stripe']          				= 'Stripe Management';
$_['tab_checkout']        				= 'Checkout Management';
$_['tab_status']          				= 'Status Management';
$_['tab_connect']         				= 'Stripe Connect Management';
$_['tab_webhook'] = 'Webhook';
$_['tab_wallet'] = 'Wallets';

// Text
$_['text_refund']	      				= 'Success - Amount has been refunded successfully.';
$_['text_payment']	      				= 'Payment';
$_['text_authorization']  				= 'Authorization';
$_['text_all_zones']  	  				= 'All Zones';
$_['text_all_customer']   				= 'Not Logged In';
$_['text_enabled']        				= 'Enable';
$_['text_disabled']      				= 'Disable';
$_['text_yes']         	  				= 'Yes';
$_['text_no']      		  				= 'No';
$_['text_success']        				= 'Success: You have modified Stripe Payment Method details!';
$_['text_sale']           				= 'Sale';
$_['text_live']    		  				= 'Live / Production';
$_['text_test']    		  				= 'Test';
$_['text_clear']    	  				= 'Clear';
$_['text_browse']    	  				= 'Browse';
$_['text_image_manager']  				= 'Image Manager';
$_['text_info']                         = "For the live payments SSL is required, as per the Stripe terms and condition, for more info <a href='https://stripe.com/docs/security#tls' target='_blank'>Click here</a>";

// Entry

//for stripe connect tab
$_['entry_connect_info'] 		     	= 'Here You can manage your Stripe Connect related options, for this you have to add Application at Stripe then you can use that\'s parameters <a href="https://connect.stripe.com/account/applications/settings" target="_blank">Here</a>.';
$_['entry_connect_title'] 		     	= 'Enter Stripe Connect Title : ';
$_['entry_connect_title_info'] 			= 'Add your Stripe Connect page title which will display to sellers.';
$_['entry_connect_description']    	 	= 'Enter Stripe Description : ';
$_['entry_connect_description_info'] 	= 'Add your Stripe Connect page description which will display to sellers, you can explain benefits of this for sellers.';
$_['entry_connect_test_client_id']   	= 'Enter Stripe App Test Client Id : ';
$_['entry_connect_test_client_id_info'] = 'Add your App\'s Test Client Id which you can get from given uper URL.';
$_['entry_connect_live_client_id']   	= 'Enter Stripe App Live Client Id : ';
$_['entry_connect_live_client_id_info'] = 'Add your App\'s Production/Live Client Id which you can get from given uper URL.';
$_['entry_connect_redirect']   			= 'Redirects URL for Stripe App : ';
$_['entry_connect_redirect_info'] 		= 'Add this URL to your App as Redirects URLs.';
$_['entry_connect_landing'] 		    = 'Stripe Connect Landing Page :';
$_['entry_connect_landing_info'] 		= 'Using this you can set Stripe Connect landing page for.Your can see details <a href="https://stripe.com/docs/connect/reference#get-authorize" target="_blank">Here</a>';
$_['entry_connect_landing_register']  	= 'Register Page';
$_['entry_connect_landing_login'] 		= 'Login Page';
$_['entry_connect_type']   			 	= 'Stripe Connect Type :';
$_['entry_connect_type_info']  	   		= 'Remember for Live / Production type your store must have secure URLS - HTPPS.';
$_['entry_stripe_work']   	        	= 'Stripe Method Work :';
$_['entry_stripe_work_info']  	   		= 'Select Stripe Payment method work if all sellers reated to order did not connected to Stripe Connect.';
$_['entry_not_visible']   	        	= 'Don\'t Visible';
$_['entry_visible_with_admin']   	    = 'Transfer Seller\'s amount to Admin\'s Account';
$_['entry_stripe_transfer']   	        = 'Stripe Method Transfer Type :';
$_['entry_stripe_transfer_info']   	    = 'Select Stripe Payment Type - Transfer all money to Admin stripe account or Transfer to Sellers and Admin stripe account (For Marketplace).';
$_['entry_stripe_transfer_admin']   	= 'Normal Stripe Method';
$_['entry_stripe_transfer_all']   	    = 'Stripe Method with Stripe Connect';


//general tab
$_['entry_general_info']    			= 'Enter/Select options which will work for this payment method for customer.';
$_['entry_geo_zone']    			 	= 'Allowed Geo Zone(s) :';
$_['entry_geo_zoneinfo']   			 	= 'Select zone(s) where you want to deliver order or if customer will not under selected zone(s) then method will not display to customer.';
$_['entry_status']      			 	= 'Payment Method Status :';
$_['entry_sort_order']  			 	= 'Sort Order :';
$_['entry_total']       			 	= 'Transaction Amount Restriction:';
$_['entry_total_info'] = 'Enter min and max value if you want that stripe will work only in between of some range, otherwise leave it blank';
$_['entry_min']     			 	 	= 'Min';
$_['entry_max']     			     	= 'Max';
$_['entry_title']    	 			 	= 'Payment Method Title :';
$_['entry_title_info']    	 	     	= 'Enter the title for payment method that will be displayed to the customer while choosing payment method at checkout.';
$_['entry_btn_text']    	 		 	= 'Confirmation Button Text :';
$_['entry_btn_info']    	 	     	= 'Enter the text for order confirmation button that will be displayed to the customer while order confirmation at checkout.';
$_['entry_groups']     				 	= 'Allowed Customer Group(s) :';
$_['entry_groupsinfo']				 	= 'Select CustomerGroup(s) which can use this payment method or method will not display to customer.';


//stripe management tab
$_['entry_keysinfo'] 				 	= 'You can get all your API keys at Stripe admin panel under Your Account > Account Settings > API Keys <a href="https://connect.stripe.com/account/apikeys" target="_blank">Here</a>';
$_['entry_test_key']   				 	= 'Secret Key for testing :';
$_['entry_test_publish_key']  	     	= 'Publish Key for testing :';
$_['entry_live_key']   			     	= 'Secret Key for live :';
$_['entry_live_publish_key'] 		 	= 'Publish Key for live :';
$_['entry_stripe_mode']    			 	= 'Payment Mode :';
$_['entry_stripe_mode_info']    			= 'Use Test to test payments via Stripe payment gateway. Use \'Live\' when you\'re ready to accept payments.';
$_['entry_currecny_mapping']    	 	= 'Currency Alteration :';
$_['entry_currecny_mappinginfo']     	= 'Select currency that will be charged against selected currency by customer at the time of payment. If it is disabled and customer is using disabled currency at front-end then stripe payment method will no longer available for payment.';
$_['entry_send_customer']   		 	= 'Customer Data :';
$_['entry_send_customerinfo']  		 	= 'Sending customer data will create a customer profile at Stripe using the email address which is provided while placing order. The credit card which is used for payment, will be attached to this customer, allowing you to charge them again in the future in Stripe.';
$_['entry_stripe_description']    	 	= 'Transaction Description :';
$_['entry_stripe_descriptioninfo'] 	 	= 'If you want simple text sent as transaction description then enter text, otherwise can be given like "XYZ [comment] ABC", where comment will be index of order information array, could be given anything from index';
$_['entry_stripe_method']    	 	= 'Stripe Charge  ';
$_['entry_stripe_through_platform']    	 	= 'Charge Through Platform ';
$_['entry_stripe_through_connected']    	 	= ' Charge Through Connected Account ';

//checkout management tab
$_['entry_stripe_settingsinfo']    	 	= 'Stripe Checkout uses Stripe\'s design and their own validation regarding card number, cvc etc.';
$_['entry_stripe_button']    		 	= 'Stripe Button';
$_['entry_remember_me']    			 	= 'Enable Remember Me :';
$_['entry_remember_meinfo'] 		 	= 'This will allow customers to remember their details.';
$_['entry_shipping']    			 	= 'Enable Shipping Address :';
$_['entry_shippinginfo']   			 	= 'Set Yes if you want address from customer otherwise No.';
$_['entry_stripe_logo']    	 		 	= 'Logo at Pop-up Box :';
$_['entry_stripe_logoinfo']	 		 	= 'Select the image that will be used as your company logo in the pop-up Box.';
$_['entry_stripe_pop_title']    	 	= 'Pop-up Title :';
$_['entry_stripe_pop_titleinfo']   	 	= 'If you want simple text title on stripe popup then enter text, otherwise can be given like "XYZ | [order_id]", where order_id will be index of order information array, could be given anything from index.';
$_['entry_stripe_pop_description']	 	= 'Pop-up Description :';
$_['entry_stripe_pop_desinfo']  	 	= 'If you want simple text description on stripe popup then enter text, otherwise can be given like "XYZ | [order_id]", where order_id will be index of order information array, could be given anything from index.';
$_['entry_stripe_pop_text']    	 	 	= 'Pop-up Button Text :';
$_['entry_stripe_pop_textinfo']	 	 	= 'If you want simple text on stripe popup then enter text, otherwise can be given like "XYZ | [amount]", where amount will be index of order information array, could be given anything from index.';

// webhook tab
$_['entry_webhook_status'] = 'Status';
$_['entry_webhook_url'] = 'Webhook URL';
$_['entry_webhook_secret'] = 'Webhook secret key';

//order status tab
$_['entry_orderinfo']    		     	= 'Choose status that will be set to order when any of below event will occur when customer place order.';
$_['entry_successpayment']    		 	= 'Successfully Paid Status :';
$_['entry_streetchk']    	         	= 'Address(street) Failure Status :';
$_['entry_zipchk']    				 	= 'Zip Code Failure Status :';
$_['entry_cvcchk']    				 	= 'CVC Code Failure Status :';
$_['entry_refund']    				 	= 'Refund Payment Status :';

//placeholders
$_['entry_popup_discription_placeholder']=	'Your order id: | [order_id]';
$_['entry_popup_title_placeholder']		=	'Amount to be paid: | [total]';
$_['entry_popup_button_placeholder']	=	'Pay: | [total]';
$_['entry_tran_description_placeholder']=	'Description For Order - | [order_id]';

// Error
$_['error_payment_wk_stripe_webhook_secret'] = "Please enter webhook secret key";
$_['error_permission']   				 = 'Warning: You do not have permission to modify payment Stripe Payment !';
$_['error_title']                        = "Please enter the Stripe Payment Title";
$_['error_payment_wk_stripe_live_key']   = "Please enter the Stripe live key";
$_['error_payment_wk_stripe_live_publish_key'] = "Please enter the Stripe live publish key";
$_['error_payment_wk_stripe_test_key'] = "Please enter the Stripe test key";
$_['error_payment_wk_stripe_test_publish_key'] = "Please enter the Stripe test publish key";
$_['error_payment_wk_stripe_min']         = "Please add the correct amount in the max and min value, Min value can not bigger than the max value";

$_['text_copy'] = "Copy URL";
$_['text_webhook_info'] = 'For setting webhook, login to your account, go to developers and add the below url as endpoint by enabling the following events.<br/><br/>1) <b>payment_intent.succeeded</b> - which is triggered when a payment is successful <br />2) <b>balance.available</b> - which is triggered when account balance is updated<br/><br/><a href="%s" target="_blank">Click here</a> to see stripe logs';
$_['text_wallet_info'] = 'For enabling Google pay and Apple pay on stripe checkout page, login to stripe account and enable both payment methods from <a href="https://dashboard.stripe.com/settings/checkout" target="_blank">here</a><br/><br/>Troubleshooting steps <br/><br/><b>1)</b> Google Pay or Apple Pay is enabled for Checkout in your Stripe Dashboard.<br/><b>2)</b> The customer is using Google Chrome or Safari. The customer’s device is running macOS 10.14.1+ or iOS 12.1+ for Apple Pay.<br/><b>3)</b> The customer has a valid card registered with Google Pay or Apple Pay.';
$_['text_stripe_log'] = "Logs";
$_['text_stripe'] = "Webkul stripe";
$_['text_stripe_config'] = "Configuration";
$_['text_stripe_webhook_secret'] = "For finding key, open the endpoind added on stripe dashboard and find secret key";
$_['text_copied'] = "Copied!";


?>
