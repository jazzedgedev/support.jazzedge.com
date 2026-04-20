jQuery(document).ready( function() {
  jQuery("#dialog-confirm").hide();

  jQuery('.memberium-subscription-list').submit( function(event) {
    event.preventDefault(); // unsure
    var myform = this;

    jQuery( "#dialog-confirm" ).dialog( {
      resizable: false,
      height: 'auto',
      width: 'auto',
      modal: true,
      buttons: [
		{
			text: subscriptionCancelText.confirmButton,
			click: function() {
				jQuery( this ).dialog( 'close' );
				myform.submit();
			}
		},
		{
			text: subscriptionCancelText.confirmCancel,
			click: function() {
				jQuery( this ).dialog( "close" );
			}
		}
	]
    } );
  } );
} );
