<?php

require_once('FormText.class.php');

/**
 * @ingroup psumodels
 */
class FormNumber extends FormText
{
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->adodb_type = 'N';
	}

	public function value()
	{
		if(func_num_args() == 1)
		{
			$v = func_get_arg(0);

			if( $v === '' || $v === null ) {
				// these values shouldn't be typecast, they'll turn into zeroes
				$v = null;
			} else {
				// any substantial value should be cast
				$v = (int)$v;
			}

			parent::value($v);
		}
		else
		{
			return parent::value();
		}
	}

	/**
	 * Avoid any empty() check, just use the raw value
	 */
	public function serialize() {
		return $this->value();
	}
}
