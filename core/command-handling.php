<?php

class Prompt_Command_Handling {

	static protected $class_map = array(
		1 => 'Prompt_Comment_Command',
		2 => 'Prompt_Register_Subscribe_Command',
		3 => 'Prompt_New_Post_Comment_Command',
	);

	/**
	 * Create a command from message data.
	 * @param object $update Message data in prompt format.
	 * @return Prompt_Interface_Command
	 */
	public static function make_command( $update ) {

		$metadata = $update->metadata;

		if ( !isset( $metadata->ids ) )
			return null;

		$data = $metadata->ids;

		$class_id = array_shift( $data );

		if ( !isset( self::$class_map[$class_id] ) ) {
			Prompt_Logging::add_error(
				'invalid_command_id',
				__( 'Received a reply with invalid command data.', 'Postmatic' ),
				$update
			);
			return null;
		}

		/** @var Prompt_Interface_Command $command */
		$command = new self::$class_map[$class_id];
		$command->set_keys( $data );
		$command->set_message( $update );

		return $command;
	}

	public static function add_command_metadata( Prompt_Interface_Command $command, Prompt_Email $email ) {

		$class = get_class( $command );

		$class_to_ids = array_flip( self::$class_map );

		if ( !isset( $class_to_ids[$class] ) ) {
			Prompt_Logging::add_error(
				'invalid_command_id',
				__( 'Tried to create an email with an unrecognized reply command.', 'Postmatic' ),
				compact( 'command', 'email' )
			);
			return '';
		}

		$data = $command->get_keys();

		array_unshift( $data, $class_to_ids[$class] );

		$metadata = new stdClass();
		$metadata->ids = $data;

		$email->set_metadata( $metadata );
	}

}