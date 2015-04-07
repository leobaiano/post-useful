    jQuery(document).ready(function($) {

    	// Click in post useful vote
    	$( ".post-useful-vote" ).on( "click", function(){
    		var post_id = $( this ).data( 'id' );
    		var rate = $( this ).data( 'rate' );
    		var clicked = $( this );
			var data = {
				'action': 'send_rate',
				'post': post_id,
				'rate': rate
			};
			$.post( postUsefulAjax.ajaxurl, data, function(response) {
				if( response == 'ok' ){
					$( '.post_useful_buttons_' + post_id + ' a' ).css( 'background-position', '0 0');
					$( '.post_useful_buttons_' + post_id + ' a' ).removeClass( 'post_useful_active' );
					$( '.post_useful_success_' + post_id ).css( 'background-position', '0 0');
					$( clicked ).css( 'background-position', '0 32px' );
					$( '.post_useful_' + post_id + ' p' ).css( 'display', 'none' );
					$( '.post_useful_success_' + post_id ).css( 'display', 'block');
				}
			});
    	});
    });