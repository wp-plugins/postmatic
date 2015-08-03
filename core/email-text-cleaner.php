<?php

class Prompt_Email_Text_Cleaner {

	public function strip( $text ) {
		$from_address = preg_quote( Prompt_Email::default_from_email() );
		$strip_patterns = array(
			'/\n?[^\r\n]*' . date( 'Y' ) . '[^\r\n]*[\r\n]{0,2}[^\r\n]*:[\s\n\r]+.*/s', // google-style quoted mail intro
			'/\n?[^\r\n]*' . date( 'Y' ) . '[^\r\n]*[\r\n]{0,2}[^\r\n]*: *$/s',         // partially stripped google quote intro
			'/\n?[^\r\n]*\/' . date( 'y' ) . '[^\r\n]*[\r\n]{0,2}[^\r\n]*:[\s\n\r]+.*/s', // short year google-style quoted mail intro
			'/<a href="https:\/\/overview.mail.yahoo.com[^>]*>.*?<\/a>/',           // yahoo mobile "sent from"
			'/[\r\n][-\*]+\s*[\r\n].*/s',                                           // dash/asterisk signature divider
			'/[\r\n][_\* ]{8,}.*/s',                                                // underscore/asterisk signature divider
			'/[\r\n]?Links:[\r\n]*\s*1\..*/s',                                      // Fastmail links list
			'/[\r\n]?>\s*$/s',                                                      // Trailing bracket quotes
			'/[\r\n][\r\n]+\s+\[image: (photo|logo)\][\r\n].*/s',                   // Wisestamp-style footer
			'/\n\s*Subject: [^\r\n]*([\r\n]*To: [^\r\n]*)?$/s',                     // Hotmail subject footer
			'/[\r\n]Sent from Mailbird \[http:\/\/www\.getmailbird.*/s',            // Mailbird signature
			"/[\r\n]?\\s*From: [^\r\n]*$from_address.*/s",                          // Text email inclusion
			'/[\r\n]Enviado desde mi iP\w*\s*$/s',                                  // Spanish signature
		);

		foreach ( $strip_patterns as $pattern ) {
			$text = preg_replace( $pattern, '', $text );
		}

		// Remove single linebreaks except after punctuation
		$text = preg_replace( '/([^\n\.\?\!\"])\r?\n([^\r\n])/', '$1 $2', $text );

		return $text;
	}

}