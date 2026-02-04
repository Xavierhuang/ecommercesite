var logprcatc = {
	'dologin': function() {
		$('#logprcatcpop').modal('show');
	},
	'submitform': function() { 
		$('#logprcatcpop').find('.modal-body .alert').remove();
 		$.ajax({
			type: "POST",
			dataType: 'json',
			cache: false,
			url: 'index.php?route=extension/logprcatc/checklog',
			data: $('#logprcatc_formid').serialize(),
			success: function(resp) {
				if(resp['text_success']) {
					location.reload();
				}
				if(resp['error_warning']) {
					$('#logprcatcpop').find('.modal-body').prepend('<div class="alert alert-danger">'+ resp['error_warning'] +'</div>');
				}
			}
		});	
	},
	'setpophtml': function(json) {		
		var pophtml = '<div id="logprcatcpop" class="modal fade" role="dialog"> <div class="modal-dialog" style="width:300px"> <div class="modal-content">';
		pophtml += '<div class="modal-header"> <button type="button" class="close" data-dismiss="modal">&times;</button> <h4>'+json['langlogin']['text_login']+'</h4> </div>';
		pophtml += '<div class="modal-body">';
        pophtml += '<form action="'+json['actionlink']+'" id="logprcatc_formid" method="post" enctype="multipart/form-data">';
          pophtml += '<div class="login-wrap">';
            pophtml += '<div class="form-group">';
              pophtml += '<label class="control-label" for="input-email">'+json['langlogin']['entry_email']+'</label>';
              pophtml += '<input type="text"  name="email" id="logprcatc-email" class="form-control" />';
            pophtml += '</div>';
            pophtml += '<div class="form-group">';
              pophtml += '<label class="control-label" for="input-password">'+json['langlogin']['entry_password']+'</label>';
              pophtml += '<input type="password"  name="password" id="logprcatc-password" class="form-control" />';
            pophtml += '</div>';
			pophtml += '<div class="form-group">';
               pophtml += '<input type="button" onclick="logprcatc.submitform();" value="'+json['langlogin']['button_login']+'" class="btn btn-primary button btn-block" />';
            pophtml += '</div>';
          pophtml += '</div>';
          pophtml += '<hr/>';
          pophtml += '<a href="'+json['registerlink']+'">'+json['langlogin']['text_register']+'</a> <a href="'+json['forgottenlink']+'" class="pull-right">'+json['langlogin']['text_forgotten']+'</a>';
        pophtml += '</form>';
		pophtml += '</div>';
		pophtml += '</div>';
		$('body').append(pophtml);
	},
	'atgrid': function() {
  		var product_ids = [];
		$("[onclick*='addToCart']").each(function() {
			var target = $(this).closest('.product-wrapper');
			var product_id = $(this).attr('onclick').match(/[0-9]+/).toString(); 			
			if(target.length && target.hasClass('logprcatc_atgridcol') == false) {
				target.addClass('logprcatc_atgridcol').addClass('logprcatc_atgridcol'+product_id);
				product_ids.push(product_id);
 			}
		});
		if(product_ids.length) { 
			//console.log(product_ids.length);
 			$.ajax({
				url: 'index.php?route=extension/logprcatc/getcachedata',
				type: 'post',
				data: {product_ids:product_ids},
				cache: true,
				complete: function() { 
				},
				success: function(json) {
					if(json) {
						//console.log(json);
						$("[onclick*='addToCart']").each(function() {
 							var product_id = $(this).attr('onclick').match(/[0-9]+/).toString(); 
							var target = $(this).closest('.product-wrapper'); 						
							if(json[product_id] && target.length) {
								if(json['hideprc'] == 1) { 
									target.find('.price').remove();
								}
								$(this).attr('onclick', 'logprcatc.dologin();').html('<b class="fa fa-lock"></b> '+json['btntxt']);
							}
						});	
					}
				}
			});
		}		
  	},	
	'prodpage': function() {	
		var product_ids = [];
		var product_id = false;
		if($("#product input[name='product_id']").length) {
			product_id = $("#product input[name='product_id']").val();
			product_ids.push(product_id);
		}		
		if (product_ids.length) {
			$.ajax({
				url: 'index.php?route=extension/logprcatc/getcachedata',
				type: 'post',
				data: {product_ids:product_ids},
				cache: true,
				complete: function() { 
				},
				success: function(json) {
					var target = $('#product'); 
					if(json && json[product_id]) {						
						if(json['hideprc'] == 1) { 
							target.find('ul.price').remove();
						}
						$('#button-cart').unbind('click').html('<b class="fa fa-lock"></b> '+json['btntxt']).attr('onclick', 'logprcatc.dologin();').removeAttr('id');					
					}					
				}
			});
 		}
  	},	
	'initjson': function() {
		$.ajax({
			url: 'index.php?route=extension/logprcatc/getrsdata',
			dataType: 'json',
			cache: true,
			complete: function() {				
			},
			success: function(json) {
				if(json['btntxt']) {
 					logprcatc.setpophtml(json);
					logprcatc.atgrid();
					logprcatc.prodpage();
					$(document).ajaxStop(function(){ 
						logprcatc.atgrid(); 
 					});	
  				}
			}			
 		});
	}
}
$(document).ready(function() {
	logprcatc.initjson();
});
$(document).ajaxStop(function(){ 
 	$("[onclick*='addToCart'], #button-cart, .price, #product ul.price").attr('style','opacity:1;');
});