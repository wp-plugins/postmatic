<?php

class Prompt_Subscribing {
	const SUBSCRIBE_ACTION = 'prompt_subscribe';

	/** @var array Subscribable types - could be extended into a registration system  */
	protected static $subscribables = array(
		'Prompt_Site' => null,
		'Prompt_Post' => 'WP_Post',
		'Prompt_User' => 'WP_User',
	);

	/**
	 * Instantiate a subscribable object.
	 *
	 * @param null|object $object Optional object to pass to the constructor.
	 * @return Prompt_Interface_Subscribable
	 */
	public static function make_subscribable( $object = null ) {
		if (
			is_a( $object, 'WP_Post' ) and
			in_array( $object->post_type, Prompt_Core::$options->get( 'site_subscription_post_types' ) )
		) {
			return new Prompt_Post( $object );
		}

		$subscribables = array_diff_key( self::$subscribables, array( 'Prompt_Post' => true ) );
		foreach ( $subscribables as $subscribable_type => $init_object_type ) {
			if ( is_a( $object, $init_object_type ) )
				return new $subscribable_type( $object );
		}

		// If no subscribables are found default to the site
		return new Prompt_Site;
	}

	/**
	 * Get registered subscribable types in fixed order.
	 * @return array
	 */
	public static function get_subscribable_classes() {
		return array_keys( self::$subscribables );
	}

}