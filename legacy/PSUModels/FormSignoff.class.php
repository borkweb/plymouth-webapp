<?php

require_once('FormText.class.php');

/**
 * @ingroup psumodels
 */
class FormSignoff extends FormText
{
	const BUTTON_LABEL = 'Signoff';

	/**
	 *
	 */
	public function __construct($args = array())
	{
		$args = PSU::params($args);

		$args['type'] = 'submit'; // text by default
		$args['class'] = 'signoff';
		$args['maxlength'] = 75;

		parent::__construct($args);
	}//end __construct

	/**
	 * Convert form field to an HTML string.
	 */
	public function __toString()
	{
		$v = $this->value();

		if( empty($v) )
		{
			// empty value means it's not signed off. temporarily override
			// the value so it shows up in the submit button.
			
			$this->value = 'Signoff';
			$html = parent::__toString();
			$this->value = null;
		}
		else
		{
			return $this->readonly($v);
		}

		return $html;
	}//end __toString

	/**
	 * Signoff,
	 */
	public function value()
	{
		if( func_num_args() == 1)
		{
			$v = func_get_arg(0);

			// handle dumb scripts that didn't call signoff() directly. dumb scripts aren't
			// bad, they just didn't override the default signoff behavior.
			if( $v == self::BUTTON_LABEL )
			{
				$v = true;
			}

			// we prefer signoff() to value() because it does some
			// extra things.
			return $this->signoff($v);
		}

		// user just wanted our value
		return parent::value();
	}

	/**
	 *
	 */
	public function signoff( $content = true )
	{
		if( $content === true )
		{
			$content = date('Y-m-d');
		}

		$this->title = new HTMLAttribute($content);

		// we have no use for $this->value() in this context
		parent::value($content);
		$this->disabled = true;
	}//end signoff
}//end FormSignoff
