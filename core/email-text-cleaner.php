<?php

class Prompt_Email_Text_Cleaner {

	public function strip( $text ) {
		$strip_patterns = array(
			'/\n?[^\r\n]*' . date( 'Y' ) . '[^\r\n]*:[\s\n\r]+.*/s',          // google-style quoted mail intro
			'/<a href="https:\/\/overview.mail.yahoo.com[^>]*>.*?<\/a>/',   // yahoo mobile "sent from"
			'/[\r\n]-+[\r\n].*/s',                                          // dash signature divider
			'/[\r\n]?Links:[\r\n]*\s*1\..*/s',                               // Fastmail links list
			'/[\r\n]?>\s*$/s',                                              // Trailing bracket quotes
			'/\r\n\r\n  \[image: photo\]\r\n.*/s',                             // Wisestamp
			'/\n\s*Subject: [^\r\n]*$/s',                                      // Hotmail subject footer
		);

		foreach ( $strip_patterns as $pattern ) {
			$text = preg_replace( $pattern, '', $text );
		}

		// Remove single linebreaks except after punctuation
		$text = preg_replace( '/([^\n\.\?\!\"])\r?\n([^\r\n])/', '$1 $2', $text );

		return $text;
	}

}