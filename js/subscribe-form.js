var prompt_subscribe_form_env;

jQuery( function( $ ) {
	var $widget = $( '.widget_prompt_subscribe_widget' ),
		$form,
		$message,
		$inputs,
		$expand_list,
		$subscriber_list,
		$loading_indicator,
		$nonce_input,
		$prompts,
		$show_unsubscribe_link,
		$submit_input,
		$cancel_link,
		$confirm_unsubscribe_input = $( '<input name="confirm_unsubscribe" type="hidden" value="1" />' );

	$.ajax( {
		url: prompt_subscribe_form_env.ajaxurl,
		method: 'GET',
		data: {
			action: 'prompt_subscribe_widget_content',
			widget_id: $widget.attr( 'id' ),
			collect_name: $widget.find( '.dynamic-content' ).data( 'collectName' ),
			object_type: prompt_subscribe_form_env.object_type,
			object_id: prompt_subscribe_form_env.object_id
		},
		success: load_form
	} );

	function load_form( content ) {

		$widget.find( '.dynamic-content' ).html( content );
		$form = $widget.find( 'form.prompt-subscribe' );
		$message = $form.find( '.message' );
		$inputs = $form.find( '.inputs' );
		$expand_list = $form.find( '.expand-list' );
		$subscriber_list = $form.find( '.subscriber-list' );
		$loading_indicator = $form.find( '.loading-indicator' );
		$nonce_input = $form.find( 'input[name=subscribe_nonce]' );
		$prompts = $form.find( '.prompt' ).hide();
		$show_unsubscribe_link = $form.find( 'a.show-unsubscribe' );
		$submit_input = $form.find( 'input[name=subscribe_submit]' );
		$cancel_link = $form.find( 'a.cancel' );

		$nonce_input.val( prompt_subscribe_form_env.nonce );

		$cancel_link.hide();
		$prompts.filter( '.' + $submit_input.val() ).show();

		$show_unsubscribe_link.click( switch_to_unsubscribe );

		$cancel_link.click( switch_to_subscribe );

		enable_placeholders();

		$expand_list.click( function() {
			$subscriber_list.slideToggle();
		} );

		$form.submit( submit_form );

	}

	function switch_to_unsubscribe( e ) {
		e.preventDefault();

		$form.append( $confirm_unsubscribe_input );
		$form.find( 'input[name=subscribe_name]' ).hide();
		$prompts.filter( '.subscribe' ).hide();
		$prompts.filter( '.unsubscribe' ).show();
		$submit_input.val( prompt_subscribe_form_env.unsubscribe_action );
		$cancel_link.show();
	}

	function switch_to_subscribe( e ) {
		e.preventDefault();

		$confirm_unsubscribe_input.detach();
		$form.find( 'input[name=subscribe_name]' ).show();
		$prompts.filter( '.subscribe' ).show();
		$prompts.filter( '.unsubscribe' ).hide();
		$submit_input.val( prompt_subscribe_form_env.subscribe_action );
		$cancel_link.hide();
	}

	function enable_placeholders() {

		$('[placeholder]' ).focus( function() {
			var $input = $( this );
			if ( $input.val() == $input.attr( 'placeholder' ) ) {
				$input.val( '' ).removeClass( 'placeholder' );
			}
		} ).blur( function() {
			var $input = $( this );
			if ( $input.val() == '' || $input.val() === $input.attr( 'placeholder' ) ) {
				$input.addClass( 'placeholder' ).val( $input.attr( 'placeholder' ) );
			}
		} ).blur().parents('form').submit(function() {
			$(this).find('[placeholder]').each(function() {
				var input = $(this);
				if (input.val() == input.attr('placeholder')) {
					input.val('');
				}
			})
		});
	}

	function submit_form() {
		$loading_indicator.show();
		$inputs.hide();
		$message.hide();

		$.post( prompt_subscribe_form_env.ajaxurl, $form.serialize(), function( message ) {

			$message.html( message ).show();
			$loading_indicator.hide();

		} ).error( function() {

			$message.html( prompt_subscribe_form_env.ajax_error_message ).show();
			$inputs.show();
			$loading_indicator.hide();

		} );
		return false;
	}
} );
