<?php

class Prompt_Comment_Email_Footer_Sidebar {

	const SIDEBAR_ID = 'prompt-comment-email-footer-area';

	public static function register() {
		register_sidebar( array(
			'name' => 'Postmatic Comments Footer',
			'id' => self::SIDEBAR_ID,
			'description' => __(
				'These widgets will be included below the comments in Postmatic comment notifications. Need inspiration? Try our widgets directory at http://gopostmatic.com/widgets.',
				'Postmatic'
			),
			'before_widget' => "<td valign='top'>",
			'after_widget' => '</td>',
			'before_title' => "<h4>",
			'after_title' => '</h4>'
		) );
	}

	public static function render() {
		if ( is_active_sidebar( self::SIDEBAR_ID ) )
			dynamic_sidebar( self::SIDEBAR_ID );
	}
}


