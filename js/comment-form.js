
var prompt_comment_form_env;

jQuery( function( $ ) {

	var $panel = $( '.prompt-unsubscribe' ),
		$loading_indicator = $panel.find( '.loading-indicator' ).detach().show(),
		$unsubscribe_button = $panel.find( 'input[name=' + prompt_comment_form_env.action + ']' );

	$unsubscribe_button.on( 'click', unsubscribe );

	function unsubscribe( e ) {
		e.preventDefault();

		$panel.empty().append( $loading_indicator );

		$.post(
			prompt_comment_form_env.url,
			prompt_comment_form_env,
			render_result
		)
	}

	function render_result( content ) {
		$panel.html( content );
	}

} );