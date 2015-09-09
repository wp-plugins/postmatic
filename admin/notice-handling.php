<?php

class Prompt_Admin_Notice_Handling {
	protected static $dismiss_query_param = 'postmatic_dismiss_notice';
	protected static $jetpack_conflict_notice = 'jetpack_conflict';
	protected static $upgrade_notice = 'upgrade';

	/**
	 * Not using for jetpack conflicts, but might as well leave the bones.
	 */
	public static function dismiss() {

		$dismissed = filter_input( INPUT_GET, self::$dismiss_query_param );

		if ( ! $dismissed )
			return;

		if ( ! in_array( $dismissed, self::valid_notices() ) )
			return;

		$dismissed_notices = array_unique(
			array_merge( Prompt_Core::$options->get( 'skip_notices' ), array( $dismissed ) )
		);

		Prompt_Core::$options->set( 'skip_notices', $dismissed_notices );
	}

	public static function display() {
		self::maybe_display_jetpack_conflict();
		self::maybe_display_upgrade();
	}

	protected static function valid_notices() {
		return array( self::$jetpack_conflict_notice, self::$upgrade_notice );
	}

	protected static function maybe_display_upgrade() {

		if ( ! current_user_can( 'update_plugins' ) )
			return;

		if ( ! Prompt_Core::$options->get( 'upgrade_required' ) )
			return;

		if ( self::is_dismissed( self::$upgrade_notice ) )
			return;

		$message = sprintf(
			__(
				'Please <a href="%s">update Postmatic</a> now to resume service. The current version is no longer supported. Thanks!',
				'Postmatic'
			),
			admin_url( 'plugins.php?plugin_status=upgrade' )
		);

		echo scb_admin_notice( $message, 'error' );
	}

	protected static function maybe_display_jetpack_conflict() {

		if ( !class_exists( 'Jetpack' ) or !current_user_can( 'manage_options' ) )
			return;

		if ( self::is_dismissed( self::$jetpack_conflict_notice ) )
			return;

		$check_modules = array( 'subscriptions', 'comments' );

		$conflicting_modules = array_filter( $check_modules, array( 'Jetpack', 'is_module_active' ) );

		if ( ! $conflicting_modules )
			return;

		$message = sprintf(
			__(
				'Heads up: We noticed there is an active Jetpack module which is not compatible with Postmatic. You\'ll need to fix that. <a href="%s" target="_blank">Learn how to do so here</a>.',
				'Postmatic'
			),
			Prompt_Enum_Urls::JETPACK_HOWTO
		);

		$message .= html( 'a',
			array(
				'href' => esc_url( add_query_arg( self::$dismiss_query_param, self::$jetpack_conflict_notice ) ),
				'class' => 'button postmatic-dismiss'
			),
			__( 'Dismiss' )
		);

		echo scb_admin_notice( $message, 'error' );
	}

	protected static function is_dismissed( $notice ) {
		return in_array( $notice, Prompt_Core::$options->get( 'skip_notices' ) );
	}
}