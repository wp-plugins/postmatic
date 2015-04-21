(function( $ ) {

	$( function() {

		$( '#prompt-tabs' ).tabs();

		$( 'input.last-submit' ).keypress( function( e ) {
			var $form = $( this ).parents( 'form' );
			if ( ( e.keyCode && e.keyCode === 13 ) || ( e.which && e.which === 13 ) ) {
				e.preventDefault();
				$form.find( 'input[type="submit"]' ).get( -1 ).click();
			}
		} );

		init_email_tab();
		init_invite_tab();
		init_import_tab();

	} );

	function init_email_tab() {
		var prompt_media_frame;

		$( 'input[name="email_header_image_button"]' ).on( 'click',  open_media_frame );

		var $email_header_types = $( 'input[name="email_header_type"]' ).on( 'change', show_email_header_type );
		show_email_header_type();

		var $email_footer_types = $( 'input[name="email_footer_type"]' ).on( 'change', show_email_footer_type );
		show_email_footer_type();

		function show_email_header_type() {
			var $radio_button = $email_header_types.filter(':checked' ),
				$image_row = $( 'tr.email-header-image' );

			if ( 'image' === $radio_button.val() ) {
				$image_row.show();
			} else {
				$image_row.hide();
			}
		}

		function show_email_footer_type() {
			var $radio_button = $email_footer_types.filter(':checked' ),
				$widgets_row = $( 'tr.email-footer-widgets' ),
				$text_row = $( 'tr.email-footer-text' );

			if ( 'widgets' === $radio_button.val() ) {
				$widgets_row.show();
				$text_row.hide();
			} else {
				$widgets_row.hide();
				$text_row.show();
			}
		}

		function init_media_frame() {
			return wp.media.frames.prompt_media_frame = wp.media( {
				title: 'Choose an email header image',
				multiple: false,
				library: { type: 'image' }
			} ).on( 'select', set_email_header_image );
		}

		function open_media_frame( e ) {
			e.preventDefault();

			if ( !prompt_media_frame )
				prompt_media_frame = init_media_frame();

			prompt_media_frame.open();
		}

		function set_email_header_image() {
			var attachment = prompt_media_frame.state().get( 'selection' ).first().toJSON();
			$( 'input[name="email_header_image"]' ).val( attachment.id );
			$( 'tr.email-header-image img' ).attr( {
				src: attachment.url,
				height: attachment.height / 2,
				width: attachment.width / 2
			} );
		}
	}

	function init_invite_tab() {
		var cached_commenters = null;
		var cached_users = null;

		var $form = $( '#prompt-settings-invite form' )
			.on( 'submit', enable_recipients );

		var $loading_indicator = $form.find( '.loading-indicator' );
		var $recipient_display = $form.find( 'textarea[name="recipients"]' );
		var $recipient_count = $form.find( 'span.recipient-count' );
		var $limit_warning = $form.find( '.invite-limit-warning' );
		var limit = $limit_warning.data( 'limit' );

		var manual_addresses_timer;
		var $manual_addresses_input = $form.find( 'textarea[name="manual_addresses"]' )
			.on( 'keyup', handle_manual_key );


		var $activity_months_select = $form.find( 'select[name="activity_months"]' )
			.on( 'change', change_recent_months );

		var $minimum_count_select = $form.find( 'select[name="minimum_count"]' )
			.on( 'change', change_minimum_count );

		var $user_role_select = $form.find( 'select[name="user_role"]' )
			.on( 'change', change_user_role );

		var $invite_recipient_types = $form.find( 'input[name="recipient_type"]' )
			.on( 'change', show_invite_recipient_type );
		show_invite_recipient_type();

		function enable_recipients( e ) {
			if ( $manual_addresses_input.is( ':visible' ) ) {
				set_manual_recipients();
			}
			$recipient_display.prop( 'disabled', false );
		}

		function show_invite_recipient_type() {
			var $radio_button = $invite_recipient_types.filter( ':checked' ),
				$manual_row = $( 'tr.invite-manual' ).hide(),
				$recent_row = $( 'tr.invite-recent' ).hide(),
				$count_row = $( 'tr.invite-count' ).hide(),
				$users_row = $( 'tr.invite-users' ).hide();

			switch ( $radio_button.val() ) {

				case 'recent':
					$recent_row.show();
					load_commenters( select_recent );
					break;

				case 'count':
					$count_row.show();
					load_commenters( select_active );
					break;

				case 'all':
					load_commenters( select_commenters );
					break;

				case 'users':
					$users_row.show();
					load_users( select_users );
					break;

				case 'post_subscribers':
					load_users( select_post_subscribers );
					break;

				default:
				case 'manual':
					$manual_row.show();
					set_manual_recipients();
					break;

			}
		}

		function set_recipients( recipients ) {
			var invite_type = $invite_recipient_types.filter( ':checked' ).val();

			if ( recipients.length > limit && 'manual' === invite_type ) {
				$limit_warning.show();
				recipients = recipients.slice( 0, limit );
			} else {
				$limit_warning.hide();
			}
			$loading_indicator.hide();
			$recipient_count.show();
			$recipient_display.empty().show();
			list_text = '';
			$.each( recipients, function( i, recipient ) {
				list_text += recipient + "\n";
			} );
			$recipient_display.text( list_text );
			$recipient_count.text( recipients.length );
		}

		function select_users( users, filter ) {
			var recipients = [];

			cached_users = users;

			if ( typeof filter != 'function' ) {
				filter = false;
			}

			$.each( users, function( i, user ) {

				if ( filter && !filter( user ) )
					return;

				if ( user.name ) {
					recipients.push( user.name + ' <' + user.address + '>' );
				} else {
					recipients.push( user.address );
				}
			} );

			set_recipients( recipients );
		}

		function select_commenters( commenters, filter ) {
			var recipients = [];

			cached_commenters = commenters;

			if ( typeof filter != 'function' ) {
				filter = false;
			}

			$.each( commenters, function( i, commenter ) {

				if ( filter && !filter( commenter ) )
					return;

				if ( commenter.name ) {
					recipients.push( commenter.name + ' <' + commenter.address + '>' );
				} else {
					recipients.push( commenter.address );
				}
			} );
			set_recipients( recipients );
		}

		function change_recent_months( e ) {
			select_recent( cached_commenters );
		}

		function select_recent( commenters ) {
			select_commenters( commenters, is_recent );
		}

		function is_recent( commenter ) {
			var months = parseInt( $activity_months_select.val() ),
				today = new Date(),
				min_date = new Date( today.getFullYear(), today.getMonth() - months, today.getDate() ),
				commenter_date = new Date( commenter.date );

			return commenter_date.getTime() >= min_date.getTime();
		}

		function change_minimum_count( e ) {
			select_active( cached_commenters );
		}

		function select_active( commenters ) {
			select_commenters( commenters, is_active );
		}

		function is_active( commenter ) {
			var minimum_count = parseInt( $minimum_count_select.val() );

			return commenter.count >= minimum_count;
		}

		function set_manual_recipients() {
			var input_text = $manual_addresses_input.val(),
				recipients = input_text.split( /\s*[,\n\r]\s*/ );

			if ( !input_text ) {
				set_recipients( [] );
				return;
			}

			set_recipients( recipients );
		}

		function change_user_role( e ) {
			select_role( cached_users );
		}

		function select_role( users ) {
			select_users( users, has_role );
		}

		function has_role( user ) {
			var role = $user_role_select.val();

			if ( role == 'all' )
				return true;

			return user.roles.indexOf( role ) >= 0;
		}

		function select_post_subscribers( users ) {
			select_users( users, is_post_subscriber );
		}

		function is_post_subscriber( user ) {
			return user.is_post_subscriber;
		}

		function handle_manual_key( e ) {
			clearTimeout( manual_addresses_timer );
			manual_addresses_timer = setTimeout( set_manual_recipients(), 1000 );
		}

		function load_commenters( callback ) {

			if ( cached_commenters ) {
				callback( cached_commenters );
				return;
			}


			$loading_indicator.show();
			$recipient_count.hide();
			$recipient_display.hide();

			$.ajax( {
				url: ajaxurl,
				data: { action: 'prompt_get_commenters' },
				success: callback
			} );

		}

		function load_users( callback ) {

			if ( cached_users ) {
				callback( cached_users );
				return;
			}

			$loading_indicator.show();
			$recipient_count.hide();
			$recipient_display.hide();

			$.ajax( {
				url: ajaxurl,
				data: { action: 'prompt_get_invite_users' },
				success: callback
			} );

		}

	}

	function init_import_tab() {
		var $rejected_addresses_input = $( 'input[name="rejected_addresses"]');

		$rejected_addresses_input.click( invite_rejected_addresses );

		function invite_rejected_addresses( e ) {
			e.preventDefault();

			$( 'textarea[name="manual_addresses"]' )
				.val( $rejected_addresses_input.data( 'addresses' ) )
				.trigger( 'keyup' );

			$( 'a[href="#prompt-settings-invite"]' ).click();
		}
	}

	// load mailchimp lists
	$(document).on('click', '#mail_chimp_load_lists', function( e ){

		e.preventDefault();

		$('#mailchimp_api_key').trigger('loadlists');

	})

	$(document).on('loadlists', '#mailchimp_api_key', function( e ){

		e.preventDefault();

		var $submit 		= $('#import_mailchimp_submit'),
			$container		= $('#mailchimp_lists'),
			$spinner		= $('#mail_chimp_spinner');

		data = {
			action	: 'prompt_mailchimp_get_lists',
			api_key	: this.value
		};
		
		// hide button
		$submit.hide();
		// clear container
		$container.empty();
		// show spinner
		$spinner.css('display', 'inline-block');

		$.ajax( {
			url: ajaxurl,
			method: 'POST',
			data: data,
			dataType: 'json',
			complete: function(){
				$spinner.hide();
			},
			success: function( data ){
				if( false === data.success ){
					// nope
					$container.html( '<div class="error"><p>' + data.data.error + '.</p></div>' );
				} else {
					$container.html( data.data );
					$submit.show();
				}
			}
		} );

	});

}( jQuery ));
