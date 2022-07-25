jQuery( function( $ ) {
    $( 'input#_gift_card' ).change( function() {
        var is_gift_card = $( 'input#_gift_card:checked' ).size();

        $( '.show_if_gift_card' ).hide();
        $( '.hide_if_gift_card' ).hide();

        if ( is_gift_card ) {
            $( '.hide_if_gift_card' ).hide();
        }
        if ( is_gift_card ) {
            $( '.show_if_gift_card' ).show();
        }
    });
    $( 'input#_gift_card' ).trigger( 'change' );
});