<?php

class Prompt_Admin_Text_Heartbeat {

	public static function filter_response( $response ) {

		if ( !isset( $response['wp_autosave'] ) )
			return $response;

		$context = new Prompt_Post_Rendering_Context( intval( $_POST['data']['wp_autosave']['post_id'] ) );

		$response['prompt_text_version']  = $context->get_the_text_content();

		$context->reset();

		return $response;
	}

}