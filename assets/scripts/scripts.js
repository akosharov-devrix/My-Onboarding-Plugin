
jQuery( document ).ready( function($) {
	jQuery('[name="mop_student_active_checkbox"]').change(function () {
		var data = {
			'action': 'mop_student_active_action',
			'nonce': jQuery( this ).data( 'nonce' ),
			'mop-post-id': jQuery( this ).data( 'post-id' ),
			'mop-student-active': jQuery( this ).prop( 'checked' ) ? '1' : '0',
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( response );
		} );
	});
});
