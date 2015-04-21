<?php

class Prompt_Admin_Text_Heartbeat {

	public static function filter_response( $response ) {

		if ( !isset( $response['wp_autosave'] ) )
			return $response;

		Prompt_Post_Mailing::setup_postdata( get_post( intval( $_POST['data']['wp_autosave']['post_id'] ) ) );

		$response['prompt_text_version']  = Prompt_Post_Mailing::get_the_text_content();

		Prompt_Post_Mailing::reset_postdata();

		return $response;
	}

}