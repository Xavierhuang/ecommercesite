var logprcatc = {
	'geturlparam': function(name) {
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    return results[1] || 0;
	},
	'loadajaxprocatmanu': function(inptname, typeset) {
		user_token = logprcatc.geturlparam('user_token');
		
		$('input[name=\''+inptname+'\']').autocomplete({
			source: function(request, response) {
				$.ajax({
					url: 'index.php?route=catalog/'+typeset+'/autocomplete&user_token='+user_token+'&filter_name=' +  encodeURIComponent(request),
					dataType: 'json',
					success: function(json) {
						response($.map(json, function(item) {
							return {
								label: item['name'],
								value: item[''+typeset+'_id']
							}
						}));
					}
				});
			},
			select: function(item) {
				$('input[name=\''+inptname+'\']').val('');
				
				$('#'+inptname+ item['value']).remove();
				
				$('#'+inptname).append('<div id="'+inptname+'-' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="'+inptname+'[]" value="' + item['value'] + '" /></div>');	
			}
		});
			
		$('#'+inptname).delegate('.fa-minus-circle', 'click', function() {
			$(this).parent().remove();
		}); 
	}
}

$(document).ready(function() {  	
	logprcatc.loadajaxprocatmanu("config_logprcatc_incprd", 'product');
	logprcatc.loadajaxprocatmanu("config_logprcatc_inccat", 'category');
	logprcatc.loadajaxprocatmanu("config_logprcatc_incman", 'manufacturer');
	logprcatc.loadajaxprocatmanu("config_logprcatc_exlprd", 'product');
	logprcatc.loadajaxprocatmanu("config_logprcatc_exlcat", 'category');
	logprcatc.loadajaxprocatmanu("config_logprcatc_exlman", 'manufacturer');
});