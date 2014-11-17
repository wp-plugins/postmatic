<?php

class Prompt_Add_On_Manager {
	const URL = 'https://gopostmatic.com';

	/**
	 * Get all known available add-ons.
	 * @return array Array key is the plugin core class name, value is plugin data in get_plugin_data() format.
	 */
	public static function available_add_ons() {

		$add_on_defaults = array(
			'PluginURI' => self::URL,
			'Version' => '0.1.0',
			'Author' => 'Vernal Creative',
			'AuthorURI' =>  self::URL,
		);

		$default_add_ons = array(
			'Prompt_Response_Core' => array(
				'Name' => 'Prompt Response',
				'Description' => __( 'Send questions and threads to a refined audience based on terms that reflect their knowledge and skills.', 'Postmatic' ),
			),
			'Prompt_Conversations_Core' => array(
				'Name' => 'Prompt Conversations',
				'Description' => __( 'Allow users to form groups and share topics.', 'Postmatic' ),
			),
			'Prompt_SMS_Core' => array(
				'Name' => 'Prompt SMS',
				'Description' => __( 'Enable users to subscribe for SMS messages as well as email.', 'Postmatic' ),
			),
		);

		foreach( $default_add_ons as $class => $add_on ) {
			$default_add_ons[$class] = array_merge( $add_on_defaults, $add_on );
		}

		//TODO: check for updates?

		return $default_add_ons;
	}

	/**
	 * Get installed and active add-ons.
	 * @return array Array key is the plugin core class name, value is plugin data in get_plugin_data() format.
	 */
	public static function active_add_ons() {

		$add_ons = self::available_add_ons();
		$active_add_ons = array();

		foreach ( $add_ons as $class => $add_on ) {
			if ( class_exists( $class ) )
				$active_add_ons[$class] = $add_on;
		}

		return $active_add_ons;
	}
}