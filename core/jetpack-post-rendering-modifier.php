<?php

class Prompt_Jetpack_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {
		$this->remove_shortcode( 'gallery', 'gallery_shortcode' );
		$this->add_shortcode( 'gallery', array( $this, 'suppress_jetpack_tiled_gallery' ) );
		$this->add_filter( 'jetpack_photon_override_image_downsize', '__return_true', 10, 1 );
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public function suppress_jetpack_tiled_gallery( $atts ) {

		// Jetpack adds the type attribute
		if ( isset( $atts['type'] ) )
			return '';

		return gallery_shortcode( $atts );
	}

}

