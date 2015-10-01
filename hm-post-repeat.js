(function($) {

	$( document ).on( 'click', '.edit-hm-post-repeat', function( e ) {

		e.preventDefault();

		$( this ).hide();
		$( '.misc-pub-hm-post-repeat strong' ).hide();
		$( '#hm-post-repeat' ).show();
		$( '#hm-post-repeat' ).find( 'select' ).focus();

	} );

	$( document ).on( 'click', '#hm-post-repeat a', function() {

		$( '.misc-pub-hm-post-repeat strong' ).text( $( '#hm-post-repeat' ).find( 'option:selected' ).text() ).show();
		$( '.edit-hm-post-repeat' ).show();
		$( '#hm-post-repeat' ).hide();

	} );

}(jQuery));