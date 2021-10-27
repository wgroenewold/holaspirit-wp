(function ($) {
	var cur;

	$( 'div[data-name="hs_token"] input[type="text"]' ).prop( 'disabled', true );

	$( document ).on(
		'click',
		'div[data-name="hs_generate_token"] input[type="radio"]',
		function(){
			$.ajax(
				{
					url : ajax.ajaxurl,
					type : 'post',
					data : {
						action : 'generate_token',
					},
					beforeSend: function(){
						  cur = $( 'div[data-name="hs_generate_token"] label.selected' ).html();
						  $( 'div[data-name="hs_generate_token"] label.selected' ).text( 'Even geduld asjeblieft...' );
					},
					success : function( response ) {
						  console.log( response );
						  $( 'div[data-name="hs_generate_token"] label.selected' ).html( cur );
						  $( 'div[data-name="hs_token"] input[type="text"]' ).val( response );
					}
				}
			);
		}
	);

	$( document ).on(
		'click',
		'div[data-name="hs_fetch_users"] input[type="radio"]',
		function(){
			fetch_data( 'users' );
		}
	);

	$( document ).on(
		'click',
		'div[data-name="hs_fetch_roles"] input[type="radio"]',
		function(){
			fetch_data( 'roles' );
		}
	);

	$( document ).on(
		'click',
		'div[data-name="hs_fetch_circles"] input[type="radio"]',
		function(){
			fetch_data( 'circles' );
		}
	);

	function fetch_data(name){
		$.ajax(
			{
				url : ajax.ajaxurl,
				type : 'get',
				data : {
					action : 'fetch_' + name,
				},
				beforeSend: function(){
					cur = $( 'div[data-name="hs_fetch_' + name + '"] label.selected' ).html();
					$( 'div[data-name="hs_fetch_' + name + '"] label.selected' ).text( 'Even geduld asjeblieft...' );
				},
				success : function( response ) {
					$( 'div[data-name="hs_fetch_' + name + '"] label.selected' ).text( 'Klaar!' );
					setTimeout( function(){$( 'div[data-name="hs_fetch_' + name + '"] label.selected' ).html( cur );}, 1000 );
				}
			}
		);
	}
})( jQuery );
