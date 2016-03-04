(function($) {

	$( document ).on( 'click', 'a.edit-hm-post-repeat', function( e ) {

		e.preventDefault();

		$( '#hm-post-repeat-schedule' ).change();
		$( this ).hide();
		$( '.misc-pub-hm-post-repeat strong' ).hide();
		$( '#hm-post-repeat' ).show();
		$( '#hm-post-repeat' ).find( 'select' ).focus();

	} );

	function setRepeatablePostLabel() {
		var $schedule = $( '#hm-post-repeat-schedule option:selected' );

		var text = $schedule.text();

		if ( 'no' !== $schedule.val() ) {
			text += ', ';

			var $selectedRepetition = $( '#hm-post-repeat-end input:checked' );
			var label = $selectedRepetition.closest( 'label' ).text();

			switch ( $selectedRepetition.val() ) {
				case 'until':
					text += label + $( '#hm-post-repeat-until-month option:selected' ).attr( 'data-text' ) + ' ' + parseInt($( '#hm-post-repeat-until-day' ).val()) + ', ' + $( '#hm-post-repeat-until-year' ).val();
					break;
				case 'times':
					text += $( '#hm-post-repeat-times' ).val() + ' ';
					// Don't break, let it slip through!
				case 'forever':
					text += label;
					break;
			}
		}

		$( '.misc-pub-hm-post-repeat strong' ).text( text ).show();

	}

	$( document ).on( 'click', '#hm-post-repeat a.save-post-hm-post-repeat', function( e ) {

		e.preventDefault();

		//$( '.misc-pub-hm-post-repeat strong' ).text( $( '#hm-post-repeat' ).find( 'option:selected' ).text() ).show();

		setRepeatablePostLabel();

		$( '#hm-post-repeat' ).hide().siblings('a.edit-hm-post-repeat' ).show().focus();

	} );

	$( document ).on( 'click', '#hm-post-repeat a.cancel-post-hm-post-repeat', function( e ) {

		e.preventDefault();

		$( '#hm-post-repeat' ).hide().siblings('a.edit-hm-post-repeat' ).show().focus();

		var $schedule = $( '#hm-post-repeat-schedule' );

		$schedule.val( $( '#hidden_hm-post-repeat-schedule' ).val() ).change();
		$( '#hm-post-repeat-until-day' ).val( $( '#hidden_hm-post-repeat-until-day' ).val() );
		$( '#hm-post-repeat-until-month' ).val( $( '#hidden_hm-post-repeat-until-month' ).val() );
		$( '#hm-post-repeat-until-year' ).val( $( '#hidden_hm-post-repeat-until-year' ).val() );

		setRepeatablePostLabel();
	} );

	$( '#hm-post-repeat-schedule' ).change( function( e ) {

		e.preventDefault();

		if ( 'no' === $( this ).val() ) {
			$( '#hm-post-repeat-end' ).slideUp( 'fast' );
		} else {
			$( '#hm-post-repeat-end' ).slideDown( 'fast' );
		}

	} );

}(jQuery));