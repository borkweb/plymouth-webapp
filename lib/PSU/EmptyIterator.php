<?php

/**
 * EmptyIterator that correctly implements Countable.
 *
 * @sa https://bugs.php.net/bug.php?id=60577
 */

namespace PSU;

class EmptyIterator extends \EmptyIterator implements \Countable {
	/**
	 * An empty iterator count() always returns 0.
	 */
	public function count() {
		return 0;
	}//end count
}//end \PSU\EmptyIterator
