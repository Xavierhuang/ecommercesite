<?php

/**
* @version [Supported opencart version 2.3.x.x.]
* @category Webkul
* @package Opencart Marketplace Stripe Payment
* @author [Webkul] <[<http://webkul.com/>]>
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

$_['heading_title_connect']    	 	 	= 'Stripe Connect';

// Text
//mp
$_['text_connect_title']    	 	 	= 'Stripe Connect';
$_['text_connect_description']    	 	= '<span>Using this Stripe Connect option you can connect your Stripe Account to Admin Stripe Account and can get payment for your orders using Stripe Payment method to your Account.</span>';
$_['text_connect_update_description']   = '<span>Congrats, You all already connected to Admin\'s Stripe Account. Using this your button you can add/update Bank Account at your Stripe Account details for your orders using Stripe Payment method to your Account.</span>';
$_['button_back']    	 	 			= 'Back';
$_['button_save']    	 	 			= 'Save';
$_['text_popup_btn_text']    	 	 	= 'Pay';

$_['text_account']    	 	 			= 'Account';
$_['error_connect']    	 	 			= 'Warning ! Something Wrong happen, please try again later !!';
$_['text_connect_success'] 				= 'Success - Your Stripe Account has been Successfully Connected .';

// $_['text_separator']    	 	 		= ' >> ';


//payment
$_['text_popup_btn_text']    	 	 	= 'Pay';
$_['text_popup_description']			= 'Stripe Description';
$_['text_popup_title']    	 			= 'Stripe Payment';
$_['text_title']   						= 'Stripe Payment';
$_['text_wait']     					= 'Attention - Please Wait for few moments !! ';
$_['text_stripe_id'] 					= '<b>Stripe Charge ID : </b> ';
$_['text_stripe_customer_id'] 			= '<br/><b>Stripe New Customer ID : </b> ';
$_['text_stripe_amount'] 				= '<br/><b>Stripe Amount (in smallest unit - *100) : </b> ';
$_['text_currency']						= '<br/><b>Currency : </b> ';
$_['text_description']					= '<br/><b>Description : </b> ';
$_['text_livemode']						= '<br/><b>Livemode : </b> ';
$_['text_yes'] 							= 'Yes';
$_['text_no']							= 'No';
$_['text_paid']							= '<br/><b>Paid : </b> ';
$_['text_card_data'] 					= '<br/><br/><b><i>Card Data </i></b>';
$_['text_card_id'] 						= '<br/><b>Card Id : </b>';
$_['text_brand']						= '<br/><b>Brand : </b> ';
$_['text_name']							= '<br/><b>Name : </b> ';
$_['text_last4'] 						= '<br/><b>Last4 : </b> ';
$_['text_exp_month']					= '<br/><b>Exp Month : </b>';
$_['text_exp_year']						= '<br/><b>Exp Year : </b> ';
$_['text_fingerprint']					= '<br/><b>Fingerprint : </b> ';
$_['text_country']						= '<br/><b>Country : </b> ';
$_['text_address1'] 					= '<br/><b>Address 1 : </b> ';
$_['text_address2']						= '<br/><b>Address 2 : </b>';
$_['text_city']							= '<br/><b>City : </b> ';
$_['text_state']						= '<br/><b>State : </b> ';
$_['text_zip']							= '<br/><b>Zip : </b> ';
$_['text_add_country'] 					= '<br/><b>Address Country : </b> ';
$_['text_cvc_check']					= '<br/><b>CVC Check : </b>';
$_['text_address_check']				= '<br/><b>Address Check : </b> ';
$_['text_address_zip_check']			= '<br/><b>Address Zip Check  : </b> ';
$_['text_cvc_chk_failed']				= '<b>CVC Check Failed </b> ';
$_['text_address_ckeck_failed'] 		= '<b>Address Check Failed </b> ';
$_['text_address_zip_ckeck_failed']		= '<br/><b>Address Zip Check : </b> ';
$_['text_refund']						= '<br/><b>Refund</b> ';
$_['text_transaction_details']			= '<b>Note: Below are the customer transaction details via stripe patment gateway. If refund is needed then simply click on refund link and refund will be made.</b><br/><br/>';
$_['text_transaction_details_mp']		= '<b>Note: Below are the customer transaction details via stripe patment gateway. If refund is needed then from your Stripe Account you & sellers can simply refund amount.</b><br/><br/>';

$_['text_success'] 						= 'Success - Your Order has been Successfully Generated.';
$_['text_testmode']						= 'Warning: The payment gateway is in \'Sandbox Mode\'. Your account will not be charged.';
$_['error_ssl'] = "Warning: SSL required for the live mode, For more info <a href='http://docs.opencart.com/en-gb/administration/ssl/' target='_blank'>Click here</a>";


  $_['text_payment_succeeded'] = 'A new payment (%s) is successfully received';

  $_['text_balance_available'] = 'Available Balance (%s), Pending Balance (%s)';
  $_['text_charge_refund_success'] = "A New Refund (%s) is successfully received";
  $_['text_charge_refund_faild'] = "A New Refund (%s) is Failed";
  $_['text_charge_refund_Non_seller_success'] = "A Non Seller New payment (%s) is successfully Refund";
  $_['text_refund_ammount_is_zero'] = "(%s) Refund Ammount Is Zero";
  $_['text_order_not_found'] = "(%s) Order Not Found";
  ?>
