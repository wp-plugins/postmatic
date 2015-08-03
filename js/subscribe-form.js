var prompt_subscribe_form_env;

jQuery(
	function ( $ ) {
		var $widgets = $( '.prompt-subscribe-widget-content' );
		var widget_promises = [];

		$widgets.each(
			function ( i, widget ) {
				widget_promises.push( init( $( widget ) ) );
			}
		);

		$.when.apply( undefined, widget_promises ).then( maybe_optins );

		function init( $widget ) {
			var $form,
				$message,
				$inputs,
				$expand_list,
				$subscriber_list,
				$loading_indicator,
				$nonce_input,
				$prompts,
				$submit_input,
				$mode_input;

			return $.ajax(
				{
					url: prompt_subscribe_form_env.ajaxurl,
					method: 'GET',
					data: {
						action: 'prompt_subscribe_widget_content',
						widget_id: $widget.data( 'widgetId' ),
						template: $widget.data( 'template' ),
						collect_name: $widget.data( 'collectName' ),
						object_type: prompt_subscribe_form_env.object_type,
						object_id: prompt_subscribe_form_env.object_id
					},
					success: load_form
				}
			);

			function load_form( content ) {

				$widget.html( content );
				$form = $widget.find( 'form.prompt-subscribe' );
				$message = $form.find( '.message' );
				$inputs = $form.find( '.inputs' );
				$expand_list = $form.find( '.expand-list' );
				$subscriber_list = $form.find( '.subscriber-list' );
				$loading_indicator = $form.find( '.loading-indicator' );
				$nonce_input = $form.find( 'input[name=subscribe_nonce]' );
				$prompts = $form.find( '.prompt' ).hide();
				$submit_input = $form.find( 'input[name=subscribe_submit]' );
				$mode_input = $form.find( 'input[name=mode]' );

				$nonce_input.val( prompt_subscribe_form_env.nonce );

				$prompts.filter( '.primary.subscribe' ).text( $widget.data( 'subscribePrompt' ) );
				$prompts.filter( '.' + $mode_input.val() ).show();

				enable_placeholders();

				$expand_list.click(
					function () {
						$subscriber_list.slideToggle();
					}
				);

				$form.submit( submit_form );
			}

			function enable_placeholders() {

				$( '[placeholder]' ).focus(
					function () {
						var $input = $( this );
						if ( $input.val() == $input.attr( 'placeholder' ) ) {
							$input.val( '' ).removeClass( 'placeholder' );
						}
					}
				).blur(
					function () {
						var $input = $( this );
						if ( $input.val() == '' || $input.val() === $input.attr( 'placeholder' ) ) {
							$input.addClass( 'placeholder' ).val( $input.attr( 'placeholder' ) );
						}
					}
				).blur().parents( 'form' ).submit(
					function () {
						$( this ).find( '[placeholder]' ).each(
							function () {
								var input = $( this );
								if ( input.val() == input.attr( 'placeholder' ) ) {
									input.val( '' );
								}
							}
						)
					}
				);
			}

			function submit_form( event ) {
				var $submitted_form = $( event.currentTarget );

				$loading_indicator.show();
				$inputs.hide();
				$message.hide();
				$prompts.hide();

				$.post(
					prompt_subscribe_form_env.ajaxurl, $submitted_form.serialize(), function ( message ) {

						$message.html( message ).show();
						$loading_indicator.hide();

					}
				).error(
					function () {

						$message.html( prompt_subscribe_form_env.ajax_error_message ).show();
						$inputs.show();
						$prompts.show();
						$loading_indicator.hide();

					}
				);
				return false;
			}


		}

		function maybe_optins() {

			if ( 'object' == typeof postmatic_optin_options ) {

				$.each(
					postmatic_optin_options, function ( i, optin ) {
						var options = {};
						var bottom_id;
						var height;
						var width;

						if ( 'bottom' == optin.type ) {
							bottom_id = '#postmatic-bottom-optin-widget';
							height = $( bottom_id ).outerHeight() + 250;
							width = $( bottom_id ).outerWidth() + 150;
							options = {
								modal: 'postmatic-widget-bottom',
								content: bottom_id,
								autoload: false,
								height: height,
								width: "auto",
								sticky: "bottom right",
								title: optin.title,
								"focus": true
							};

							if ( 'bottom' == optin.trigger ) {
								var bottom_bottom_triggered = false;
								$( window ).scroll(
									function () {
										if ( false == popup_bottom_triggered ) {
											if ( near_bottom() ) {
												bottom_bottom_triggered = true;
												$( '<div>' ).calderaModal( options );
											}
										}
									}
								);
							} else if ( 'comment' == optin.trigger ) {
								$( '#commentform' ).submit(
									function () {
										$( '<div>' ).calderaModal( options );
									}
								);
							} else {
								setTimeout(
									function () {
										$( '<div>' ).calderaModal( options );
									}, optin.trigger
								);

							}
						} else if ( 'popup' == optin.type ) {
							var popup;

							if ( has_seen( optin ) && ! optin.admin_test ) {
								return true;
							}

							bottom_id = '#postmatic-popup-optin-widget';
							height = $( bottom_id ).height() + 0;
							width = $( bottom_id ).outerWidth() + 150;
							options = {
								height: height,
								width: width,
								modal: 'postmatic-widget-popup',
								content: '#postmatic-popup-optin-widget',
								autoload: false,
								"focus": true
							};


							if ( 'bottom' == optin.trigger ) {
								var popup_bottom_triggered = false;
								$( window ).scroll(
									function () {
										if ( false == popup_bottom_triggered ) {
											if ( near_bottom() ) {
												will_see( optin );
												popup_bottom_triggered = true;
												popup = $( '<div>' ).calderaModal( options );
												shake_popup( popup );
											}
										}
									}
								);
							} else if ( 'comment' == optin.trigger ) {
								$( '#commentform' ).submit(
									function () {
										will_see( optin );
										popup = $( '<div>' ).calderaModal( options );
										shake_popup( popup );
									}
								);
							} else {
								setTimeout(
									function () {
										will_see( optin );
										popup = $( '<div>' ).calderaModal( options );
										shake_popup( popup );

									}, optin.trigger
								);

							}


						}
					}
				);
			}

			function has_seen( optin ) {
				var pattern = new RegExp( 'prompt_optin_' + optin.type + '=[^;]*' );
				return document.cookie.match( pattern );
			}

			function will_see( optin ) {
				document.cookie = 'prompt_optin_' + optin.type + '=1; path=/';
			}

			function near_bottom() {
				var window_height = $( window ).height();
				var near_height = window_height * 0.15;
				var bottom_trigger = $( document ).height() - window_height - near_height;
				return $( window ).scrollTop() > bottom_trigger;
			}
		}

		function shake_popup( popup ) {

			var el = popup.modal;

			var interval = 100;
			var distance = 10;
			var times = 2;

			$( el ).css( 'position', 'relative' );

			for ( var iter = 0; iter < (times + 1); iter++ ) {
				$( el ).animate(
					{
						left: ((iter % 2 == 0 ? distance : distance * -1))
					}, interval
				);
			}//for

			$( el ).animate( {left: 0}, interval );

		}

	}
);
