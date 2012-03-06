<?php

namespace PSU;

require_once 'Zend/Feed.php';

class Feed implements \Iterator {
	public $feed;
	public $encoding;

	const FIELD_HIDE        = 0; // hide this field
	const FIELD_SHOW        = 1; // show this field

	const EXPAND_DISABLED   = 0; // entry expanding is disabled
	const EXPAND_ENABLED    = 1; // entry expanding is enabled
	const EXPAND_FORCED     = 2; // entries are expanded by default

	const BODY_FULL         = 0; // show full body text
	const BODY_SUMMARY      = 1; // summarize body text

	const DEFAULT_ITEMS     = 5; // default items to show
	const DEFAULT_OPEN      = 0; // default expanded items
	const DEFAULT_READ_MORE = 'Read more'; // default "read more" text

	public $num = self::DEFAULT_ITEMS;
	public $open = self::DEFAULT_OPEN;
	public $expand = self::EXPAND_ENABLED;
	public $summary = self::BODY_FULL;
	public $title = self::FIELD_HIDE;
	public $read_more = self::DEFAULT_READ_MORE;
	public $desc = self::FIELD_HIDE;
	public $length = false;

	/**
	 * Track number of items shown.
	 */
	private $shown = 0;

	/**
	 * Track number of items expanded.
	 */
	private $expanded = 0;

	public static function import( $uri ) {
		$feed = new self;
		$zend_feed = \Zend_Feed::import( $uri );

		$feed->feed = $zend_feed;
		$feed->encoding = $feed->feed->getDOM()->ownerDocument->encoding;

		$feed->process();

		return $feed;
	}

	public function process() {
		echo $this->feed->charset;

		foreach( $this->feed as $item ) {
		}
	}

	public function content_encoded() {
		return $this->current()->{'content:encoded'};
	}

	public function description() {
		if( $this->content_encoded() ) {
			return $this->current()->{'content:encoded'};
		} else {
			return $this->current()->description();
		}
	}

	public function li_class() {
	}

	public function rewind() {
		$this->feed->rewind();

		$this->shown = 0;
		$this->expanded = 0;
	}

	public function current() {
		$item = $this->feed->current();
		return $item;
	}

	public function key() {
		$key = $this->feed->key();
		return $key;
	}

	public function next() {
		$this->feed->next();
	}

	public function url() {
		$link = $this->feed->link();
		if(is_array($link)) {
			foreach($link as $l) {
				if($l->prefix == null) {
					$link = $l->textContent;
					break;
				}
			}
		}
		return $link;
	}//end url

	public function valid() {
		$valid = $this->feed->valid();
		return $valid;
	}
}
