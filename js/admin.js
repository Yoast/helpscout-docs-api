jQuery( document ).ready( function( $ ) {
	$( "#yoast-tabs" ).find( "a" ).click( function() {
		$( "#yoast-tabs" ).find( "a" ).removeClass( "nav-tab-active" );
		$( ".yoast_tab" ).removeClass( "active" );

		var id = $( this ).attr( "id" ).replace( "-tab", "" );
		$( "#" + id ).addClass( "active" );
		$( this ).addClass( "nav-tab-active" );
	} );

	$( "#submit" ).click( function(){
		$( "#yst_active_tab" ).val( $( ".yoast_tab.active" ).attr( "id" ) );
	});

	// Init.
	var activeTab = window.location.hash.replace( "#top#", "" );
	if ( $( "#yst_active_tab" ).val() !== "" ) {
		activeTab = $( "#yst_active_tab" ).val();
	}

	// Default to first tab.
	if ( activeTab === "" || activeTab === "#_=_" ) {
		activeTab = $( ".yoast_tab" ).attr( "id" );
	}

	$( "#" + activeTab ).addClass( "active" );
	$( "#" + activeTab + "-tab" ).addClass( "nav-tab-active" ).click();
} );
