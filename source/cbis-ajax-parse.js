jQuery(document).ready(function ($) {

console.log('init, remove me');

    $('#cbis-ajax').click(function(e) {
    e.preventDefault();

    console.log('Clicked');

	var i = 0;
	function parseCBIS() {

		if( (typeof cbis_ajax_vars.cbis_keys[i] == 'undefined') ) {
			console.log('undefined key -> return');
			return;
		}

		$.ajax({
			url: eventmanager.ajaxurl,
			type: 'post',
			data: {
				action: 'cbis_ajax_parse',
				api_keys: cbis_ajax_vars.cbis_keys[i]
			},
			beforeSend: function() {

			},
			success: function(response) {
				console.log( response );
				i++;
				parseCBIS();
			}
		})

	}

	parseCBIS();

	});

});
