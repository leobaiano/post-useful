    jQuery(document).ready(function($) {

    	// Click in post useful vote
    	$( ".post-useful-vote" ).on( "click", function(){

			var data = {
				'action': 'send_rate',
				'post': 1234
			};
			$.post( postUsefulAjax.ajaxurl, data, function(response) {
				console.log( response );
			});
    	});
    });