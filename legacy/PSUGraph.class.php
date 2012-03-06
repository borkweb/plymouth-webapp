<?php
/**
 * Base class for graph objects that contains properties and methods
 * used by all Graph objects
 */
class PSUGraphObject
{
	/**
	 * id of object
	 */
	public $id;

	/**
	 * id of object
	 */
	public $passed = array();

	/**
	 * constructor
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}//end __construct

	public function handleParams($property, $key = null, $value = null, $defaults = array())
	{
		if($defaults)
		{
			$this->handleParams($property, $defaults);
		}//end if

		if( is_object( $key ) && $key instanceof PSUGraphObject)
		{
			$this->{$property}[ $key->id ] = $key;
		}//end if
		elseif( is_array( $key ) || strpos($key, '=') !== false )
		{
			$values = PSU::params($key, $defaults);
			
			foreach($values as $k => $v)
			{
				if($property == 'options')
				{
					$this->{$property}[ $k ] = new PSUGraphOption($k, $v);
				}//end if
				else
				{
					$this->{$property}[ $k ] = $v;
				}//end else
			}//end foreach

			return true;
		}//end if
		elseif( $key && $value )
		{
			$this->{$property}[ $key ] = $value instanceof PSUGraphOption ? $value : new PSUGraphOption($k, $v);
			return true;
		}//end if
		elseif( $key )
		{
			return $this->{$property}[ $key ];
		}//end elseif
		else
		{
			return $this->{$property};
		}//end else
	}//end handleParams

	/**
	 * sets the style for an individual series and build the style_string
	 */
	public function options($key = null, $value = null, $type = 'value', $defaults = array())
	{
		if(!($key instanceof PSUGraphOption) && $value)
		{
			$key = new PSUGraphOption($key, $value, $type);
			$value = null;
		}//end if

		$this->handleParams('options', $key, $value, $defaults);
	}//end options
}//end PSUGraphObject

/**
 * Base graph object that contains series, axes, and graph styling
 */
class PSUGraph extends PSUGraphObject
{
	/**
	 * Array of PSUGraphAxis in the graph
	 */
	public $axis;

	/**
	 * Array of PSUGraphSeries
	 */
	public $series;

	/**
	 * Array of PSUGraphOptions
	 */
	public $options;

	/**
	 * Array of styles for the graph div
	 */
	public $style;

	/**
	 * Constructor.
	 * @param $id \b id of the graph
	 * @param $style \b array of styles for graph
	 */
	public function __construct($id, $meta = array(), $style = array())
	{
		parent::__construct($id);
		$this->series = array();
		$this->axis = array();

		$this->style($style);

		$this->options($meta);
	}//end __construct

	/**
	 * adds an axis to the graph
	 */
	public function addAxis(PSUGraphAxis $axis)
	{
		$this->axis[$axis->id] = $axis;

		if( $axis->caption)
		{
			if(!$this->options[$axis->axis])
			{
				$this->options[$axis->axis] = new PSUGraphOption($axis->axis, null, 'object');
			}//end if

			$this->options[$axis->axis]->options('caption', $axis->caption);
		}//end if
	}//end addSeries

	/**
	 * adds a series to the graph
	 */
	public function addSeries(PSUGraphSeries $series)
	{
		$this->series[$series->id] = $series;
	}//end addSeries

	/**
	 * js_timestamp format
	 */
	public static function js_time($timestamp)
	{
		return str_pad($timestamp, 13, '0', STR_PAD_RIGHT);
	}//end js_time

	/**
	 * sets the graph options
	 */
	public function options($key = null, $value = null, $type = 'value')
	{
		$defaults = array(
			'show_legend' => false,
			'title' => 'PSU Graph'
		);

		if($this->passed['options'])
		{
			parent::options($key, $value, $type);
		}//end if
		else
		{
			parent::options($key, $value, $type, $defaults);
			$this->passed['options'] = true;
		}//end else
	}//end options

	/**
	 * sets the style for a graph and build the style_string
	 */
	public function style($key = null, $value = null)
	{
		$defaults = array(
			'width' => '648px',
			'height' => '400px',
			'display' => 'block'
		);

		if($this->passed['style'])
		{
			$this->handleParams('style', $key, $value);
		}//end if
		else
		{
			$this->handleParams('style', $key, $value, $defaults);
			$this->passed['style'] = true;
		}//end else
	}//end style

	/**
	 * magic getter
	 */
	public function __get($property)
	{
		if( $property == 'style_string' )
		{
			return http_build_query( (array) $this->style , null, ';');
		}//end if
	}//end __get
}//end class PSUGraph

/**
 * Graph axis object
 */
class PSUGraphAxis extends PSUGraphObject
{
	/**
	 * type of the axis
	 */
	public $type;

	/**
	 * axis caption
	 */
	public $caption;

	/**
	 * axis location
	 */
	public $axis;

	/**
	 * value of the axis
	 */
	public $val;

	/**
	 * axis options
	 */
	public $options;

	/**
	 * constructor
	 */
	public function __construct($id, $axis, $caption, $type = 'LinearAxis')
	{
		parent::__construct($id);
		$this->type = $type;

		$this->caption = $caption;
		$this->options('caption', $this->caption);

		switch (strtolower($axis))
		{
			case 'x':
				$this->axis = 'axis_bottom';
				$this->val = 'X';
				break;
			case 'y':
				$this->axis = 'axis_left';
				$this->val = 'Y';
				break;
			case 'x2':
				$this->axis = 'axis_right';
				$this->val = 'X2';
				break;
			case 'y2':
				$this->axis = 'axis_top';
				$this->val = 'Y2';
				break;
		}
	}//end __construct

	/**
	 * sets the axis options
	 */
	public function options($key = null, $value = null, $type = 'value')
	{
		$defaults = array(
			'color' => '#000'
		);

		if($this->passed['options'])
		{
			parent::options($key, $value, $type);
		}//end if
		else
		{
			parent::options($key, $value, $type, $defaults);
			$this->passed['options'] = true;
		}//end else
	}//end options

	/**
	 * magic getter
	 */
	public function __get($property)
	{
		if($property == 'data')
		{
			return json_encode($this->raw_data);
		}//end if
		elseif( $property == 'option_string' )
		{
			return json_encode($this->options);
		}//end else
	}//end __get

}//end class PSUGraphAxis

/**
 * Graph series object
 */
class PSUGraphSeries extends PSUGraphObject
{
	/**
	 * raw series data
	 */
	public $raw_data;

	/**
	 * type of series
	 */
	public $type;

	/**
	 * series data handler
	 */
	public $handler;

	/**
	 * options
	 */
	public $options;

	/**
	 * constructor
	 */
	public function __construct($id, $data, $type = 'LineSeries', $handler = 'ArrayDataHandler')
	{
		parent::__construct($id);
		$this->raw_data = $data;
		$this->type = $type;
		$this->handler = $handler;
	}//end __construct

	/**
	 * sets the style for an individual series and build the style_string
	 */
	public function options($key = null, $value = null, $type = 'value')
	{
		parent::options($key, $value, $type);
	}//end options

	/**
	 * magic getter
	 */
	public function __get($property)
	{
		if($property == 'data')
		{
			return json_encode($this->raw_data);
		}//end if
		elseif($property == 'option_string')
		{
			return http_build_query( (array) $this->options);
		}//end else
	}//end __get
}//end class PSUGraphSeries

/**
 * Graph formatter object
 */
class PSUGraphOption extends PSUGraphObject
{
	/**
	 * type of series
	 */
	public $type;

	/**
	 * formatter options
	 */
	public $options;

	/**
	 * constructor
	 */
	public function __construct($id, $options = array(), $type = 'value')
	{
		parent::__construct($id);
		$this->type = $type;

		if($this->type == 'value')
		{
			$this->options = $options;
		} else {
			$this->options($options);
		}//end else
	}//end __construct
}//end class PSUGraphOption
