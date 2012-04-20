<?php

class RSSAPI {

	/** 
	 * recent wordpress feeds have <atom:link> and <link>, so $rss->link() returns an
	 * array of DOMElements. if that's the case, dig down to the vanilla <link> element
	 * and use its content.
	 * NOTE: I have found that this functionality is also needed for the description attribute
	 * in the NY Times RSS, and have genericized this as necessary...
	 */
	public function deatomize( $text ) {
		if( is_array($text) ) {
			foreach($text as $t) {
				if($t->prefix == null) {
					return $t->textContent;
				}
				return $t->textContent;
			}
		}
		return $text;
	}//end deatomize

	/**
	 * This is converting to UTF-8 for display in the portal.
	 * This causes the portal to properly handle odd characters, 
	 * but causes the improper characters to be visible in the direct
	 * render of the channel as a webapp.
	 */
	function translate( $output ) {
		$encoding = mb_detect_encoding( $output );
		return iconv( $encoding, 'UTF-8', $output );
	}//end translate

}//end RSSAPI
