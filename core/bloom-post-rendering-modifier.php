<?php

class Prompt_Bloom_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {

		if ( ! class_exists( 'ET_Bloom' ) )
			return;

		$bloom = ET_Bloom::get_this();

		// For Bloom < 1.0.5
		$this->remove_filter( 'the_content', array( $bloom, 'display_below_post' ), 10, 1 );
		// For Bloom 1.0.5 and hopefully later
		$this->remove_filter( 'the_content', array( $bloom, 'display_below_post' ), 9999, 1 );
		$this->remove_filter( 'the_content', array( $bloom, 'trigger_bottom_mark' ), 9999, 1 );
	}

}
