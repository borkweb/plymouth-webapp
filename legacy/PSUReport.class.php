<?php
ini_set('memory_limit', -1);

class PSUReport{
 	/**
	 * Max data length of each column
	 */
	public $data_length = array();

	/**
	 * array of drilldown objects and field associations
	 */
	public $drill;

	/**
	 * pre-set filters
	 */
	public $filters = array();

	/**
	 * require filters before report is run?
	 */
	public $filter_required = false;

	/**
	 * holds field specific formatting
	 */
	public $format = array();

	/**
	 * holds all fields that should be hidden
	 */
	public $hidden = array();

	/**
	 * hide the result count
	 */
	public $hide_count = false;

	/**
	 * hide the csv link
	 */
	public $hide_csv = false;

	/**
	 * Restrict field width
	 */
	public $max_field_width = 650;
	
	/**
	 * allow paging?
	 */
	public $paging = 20;
 
	/**
	 * the field used in to specifiy if a simple graph should be displayed
	 */
	public $simple_graph;

	/**
	 * the field used in generating simple graphs
	 */
	public $summary_field;

	/**
	 * id of the report
	 */
	public $id;

	/**
	 * id of the row
	 */
	public $row_id = 0;

	/**
	 * original $_GET filters
	 */
	public $original_get = array();

	/**
	 * SQL object for the report
	 */
	public $sql;

	/**
	 * timestamp of object instantiation
	 */
	public static $time;

 	public function __construct( $id, $report, $drill = array() ){
		$this->id = $id;
		$this->_chart_data = array();

		$args = PSU::params($args);

		$args['id'] = $args['id'] ? $args['id'] : $this->id;

		if($this->bind) {
			$args['bind'] = $this->bind;
		}//end if

		if($this->bind_registry) {
			$args['bind_registry'] = $this->bind_registry;
		}//end if

		if($this->params) {
			$args['params'] = $this->params;

			if( !$args['bind_registry'] ) {
				$args['bind_registry'] = array_keys( (array) $args['params'] );
			}//end if
		}//end if

		if($this->bind_registry) {
			$args['bind_registry'] = $this->bind_registry;
		}//end if

		if($this->database) {
			$args['database'] = $this->database;
		}//end if

		$this->format_exclude = array(
			'date' => array(),
			'decimal' => array(),
			'money' => array(),
			'percent' => array(),
			'pre' => array(),
			'number' => array(
				'pidm',
				'pidmcrn',
				'crn',
				'course_number',
				'course_num',
				'course_numb',
				'crse_num',
				'crse_numb',
				'crse_number',
				'fund_year',
				'id',
				'term_code',
				'term_code_eff',
				'term',
				'zip',
				'zip_code'
			),
		);

		// this will hold the fields that the generic filter has already set.
		// filters customized with the generic set take precedence over the default settings
		$filter_fields = array();

		// set up generic filters
		for($i = 1; $i < 5; $i++) {
			$field = $_GET['psufilter_'.$i]['field'];
			$value = $_GET['psufilter_'.$i]['value'];
			$operator = $_GET['psufilter_'.$i]['operator'];

			// was an operater specified in the UI?
			if( !$operator ) {
				// nope!  default to an = sign
				$operator = '=';
			}//end if

			$filter_fields[] = $field;

			$this->filters[ 'psufilter_'.$i ] = new PSUReportFilter($field, $value, $operator);
			if( $field ) {
				$this->filters[ 'psureportfilter_'.$field ] = new PSUReportFilter($field, $value, $operator);
			}//end if
		}//end for

		// load GET filters
		foreach( (array) $_GET as $key => $value ) {
			if( strpos( $key, 'psufilter_' ) === false 
					&& $key != 'page'
					&& $key != 'graph_type'
					&& $key != 'summary_field') {
				if( $this->filters[ $key ]) {
					if( in_array( $this->filters[ $key ]->field, $filter_fields ) ) {
						$this->filters[ $key ]->value = $this->filters[ 'psureportfilter_'.$key ]->value;
					} else {
						$this->filters[ $key ]->value = $value;
					}//end else
				} else {
					$this->original_get[$key] = new PSUReportFilter($key, $value);
				}//end else
			}//end if
		}//end foreach

		// merge GET and other filters
		$this->filters = array_merge( (array) $this->filters, (array) $this->original_get);

		foreach( $this->filters as $key => $filter ) {
			$args['bind_registry'][] = str_replace('psureportfilter_', '', $key);

			$values = $filter->values();
			foreach( $values as $key => $value ) {
				$args['bind_registry'][] = $key;
			}//end foreach
		}//end foreach
		$args['bind_registry'] = array_unique( (array) $args['bind_registry'] );

		if( $this->nocache ) {
			$args['nocache'] = true;
		}//end if

		// set up the sql object
		if( is_array( $report ) ) {
			$this->data = (array) $report;
			if( count( $this->data ) ) {
				$cols = array_keys(current($this->data));
				$this->cols = array();
				foreach( $cols as $col ) {
					if( 'class' == $col ) {
						$col = 'class_';
					}//end if

					$this->cols[$col] = (object) array('name' => $col);
				}//end foreach
			}//end if
		} elseif( $report instanceof PSUSQL ){
			$this->sql = $report;
		} else {
			$this->sql = new PSUSQL($report, $args);	
		}//end else

		// apply set filters to the SQL statement
		if( $this->cols && $this->filters ) {
			foreach( (array) $this->filters as $key => $filter ) {
				if( array_key_exists( $filter->field, $this->cols )) {
					$values = $filter->values();
					$value_exists = false;
					foreach( $values as $key => $value ) {
						// is there actually a value?
						if( $value !== null && $value != '' ) {
							$value_exists = true;
						}//end if

						$this->sql->_add_parameters( array( $key => $value ) );
					}//end foreach

					// only add the where if the value is not null
					if($value_exists) {
						$this->sql->addWhere($filter->where());
					}//end if
				}//end if
			}//end foreach
		}//end if

		$this->drill = $drill;

		self::$time = time();

		// set up default colors
		$this->default_colors = array(
			'rgb(120,90,59)',
			'rgb(53,115,53)',
			'rgb(178,87,56)',
			'rgb(203,143,71)',
			'rgb(55,106,155)',
			'rgb(205,197,51)',
			'rgb(209,130,139)',
			'rgb(159,153,57)',
			'rgb(206,173,136)',
			'rgb(191,132,72)',
			'rgb(151,135,169)',
			'rgb(140,48,51)',
			'rgb(59,144,187)',
			'rgb(197,190,104)',
			'rgb(109,136,79)',
			'rgb(144,100,144)',
			'rgb(181,94,94)',
			'rgb(59,144,144)',
			'rgb(204,136,92)',
			'rgb(139,167,55)',
			'rgb(205,171,66)',
			'rgb(150,184,211)'
		);

		PSU::add_filter('psusql_' . $this->id . '_parse_results', array(&$this, 'parse_row'));
	}//end constructor

	/**
	 * adds a range where clause to the where registry
	 */
	public function addRange($type = 'date', $field_1 = null, $operator = 'BETWEEN', $value_1 = null, $field_2 = null, $value_2 = null, $connector = 'AND'){
		$bind = array();

		$where = $this->addRangeWhere($type, $field_1, $operator, $value_1, $field_2, $value_2, $connector, $bind);

		$this->sql->addWhere($where, $bind);
	}//end addRange

	/**
	 * creates a range where clause
	 */
	public function addRangeWhere($type = 'date', $field_1 = null, $operator = 'BETWEEN', $value_1 = null, $field_2 = null, $value_2 = null, $connector = 'AND', &$bind = array()){
		$operator = strtolower( trim( $operator ) );

		$where = $connector . ( $operator == 'between' ? " %s BETWEEN %s AND %s" : " %s %s %s");

		// set up default swap variable values
		$swap_1 = null;
		$swap_2 = null;
		$swap_3 = null;

		// operator determines the final population of the swap variables
		if( $operator == 'between' ){
			// operator is BETWEEN.  Sweet cuppin cakes, lets populate those swap vars

			// swap_1 is super easy:
			//    IF field_1 is set, swap_1 will hold field_1
			//    OTHERWISE...
			//    IF value_1 was passed in, swap_1 will hold its bind key (key_1)
			//    OTHERWISE
			//    neither field_1 or value_1 were set, so swap_1 will be a bound timestamp
			$swap_1 = $field_1 ? $field_1 : ($value_1 !== null ? ':'.$this->createBind($type, $value_1, $bind) : ':'.$this->createBind($type, self::$time, $bind));

			// determining swap_2 and swap_3 are a bit more complex as they
			// are completely reliant on what was passed into the function	
			if( $field_1 && $value_1 !== null ){
				// both field_1 and value_1 were passed in
				
				// swap_2 will hold the bind key of value_1 (key_1)
				$swap_2 = ':'.$this->createBind($type, $value_1, $bind);

				// swap_3 is slightly more complex
				//    IF field_2 is set, swap_3 will hold field_2
				//    OTHERWISE
				//    IF value_2 was passed in, swap_3 will hold its bind key (key_2)
				//    OTHERWISE
				//    neither field_2 or value_2 were set, so swap_3 will be a bound timestamp
				$swap_3 = $field_2 ? $field_2 : ($value_2 !== null ? ':'.$this->createBind($type, $value_2, $bind) : ':'.$this->createBind($type, self::$time, $bind));
			} elseif( $field_2 && $value_2 != null ){
				// both field_2 and value_2 were passed in

				// swap_2 will hold field_2
				$swap_2 = $field_2;

				// swap_3 will hold the bind key of value_2 (key_2)
				$swap_3 = ':'.$this->createBind($type, $value_2, $bind);
			} elseif( $field_2 ){
				// field_2 was passed in but value_2 was not

				// swap_2 will hold a bound timestamp
				$swap_2 = ':'.$this->createBind($type, self::$time, $bind);

				// swap_3 will hold field_2
				$swap_3 = $field_2;
			} elseif( $value_2 != null ){
				// value_2 was passed in but field_2 was not

				// swap_2 will hold a bound timestamp
				$swap_2 = ':'.$this->createBind($type, self::$time, $bind);

				// swap_3 will hold the bind key of value_2 (key_2)
				$swap_3 = ':'.$this->createBind($type, $value_2, $bind);
			} else {
				// neither field_2 nor value_2 were passed in

				// swap_2 and swap_3 will both hold bound timestamps
				$swap_2 = ':'.$this->createBind($type, self::$time, $bind);
				$swap_3 = ':'.$this->createBind($type, self::$time, $bind);
			}//end else
		} else {
			// the operator was not BETWEEN

			// swap_1 and swap_2 are easy peasy
			$swap_1 = $field_1;
			$swap_2 = $operator;

			// swap_3 is slightly more complex
			//    IF field_2 is set, swap_3 will hold field_2
			//    OTHERWISE
			//    IF value_1 was passed in, swap_3 will hold its bind key (key_1)
			//    OTHERWISE
			//    neither field_2 or value_1 were set, so swap_3 will be a bound timestamp
			$swap_3 = $field_2 !== null ? $field_2 : ( $value_1 != null ? ':'.$this->createBind($type, $value_1, $bind) : ':'.$this->createBind($type, self::$time, $bind));
		}//end else

		$where = sprintf( $where, $swap_1, $swap_2, $swap_3 );
		return $where;
	}//end addRangeWhere

	/**
	 * authorization of access
	 */
	public function authZ(&$person = null) {
		if( !($person instanceof PSUPerson) ) {
			$person = PSUPerson::get( $person ? $person : $_SESSION['wp_id'] );
		}//end if	

		return true;
	}//end authZ

	/**
	 * creates a bind variable
	 */
	public function createBind($type, $value, &$bind){
		static $range_counter = 1;
		
		$bind_vars = array('range_bind_'.$range_counter => ($type == 'date' ? PSU::db($this->sql->database)->BindTimestamp($value) : $value));
		$bind = array_merge( $bind, $bind_vars );

		$range_counter++;

		return key($bind_vars);
	}//end createBind

	/**
	 * get width of field text
	 */
	public function getTextWidth( $text ) {
		return strlen( $text );
	}
 
	/**
	 * create graph instance
	 */
	public function graph(){
		if( $this->summary_field ) {
			$this->graph = new PSUGraph($this->id);
			$this->_graph_simple();
			return $this->graph;
		} elseif( $this->graph_line ) {
			$this->graph = new PSUGraph($this->id);
			$this->_graph_line();
			return $this->graph;
		}//end if
		return null;
	}//end graph


	/**
	 * sets the graphable field
	 */
	public function graph_line( $x, $y ) {
		$this->x = $x;
		$this->y = $y;
		$this->graph_line = true;
	}//end graph_line

	/**
	 * returns the report's name
	 */
	public function name($object_name) {
		$reflection = new ReflectionClass( $object_name );
		try{
			$name = $reflection->getStaticPropertyValue('name');
		} catch( Exception $e ) {
			$name = $this->name;
		}//end catch
		return $name;
	}//end name

	/**
	 * default handling of records to prep graphs
	 */
	public function parse_row( $row ) {
		if( $this->summary_field ) {
			$field = $row[ $this->summary_field ];
			$this->_chart_data[ $field ? $field : '[blank]' ]++;
			$this->_chart_data[ 'psuchart_total' ]++;
		}//end if	

		// KendoUI does not like the word "class" as a field name.  If there is one, rename it to class_
		if( isset( $row['class'] ) ) {
			$row['class_'] = $row['class'];
			unset( $row['class'] );
		}//end if

		// Determine max length of values in column 
		foreach( $row as $field=>$data ) {
			$field_length = $this->getTextWidth( $data ); 
			if( $field_length > $this->data_length[ $field ] ) { 
				$this->data_length[ $field ] = $field_length;
			}

		}

		// Give each row a unique id, essential for slickgrid
		$row['analytics_grid_row_id'] = $this->row_id++; 

		return $row;
	}//end parse_row
 
	/**
	 * retrieves the source of the query
	 */
	public function source() {
		$return_sql = '';
		$execute_sql = $this->sql->execute_sql;
		$return_sql = $execute_sql;

		return $return_sql;
	}//end source

	/**
	 * sets the graphable field
	 */
	public function summary( $default = null ) {
		$this->summary_field = $_GET['summary_field'] ? $_GET['summary_field'] : $default;
		$this->simple_graph = true;
	}//end summary

	/**
	 * renders an xlsx version of the report
	 */
	public function xlsx() {
		$meta = array();

		$meta['creator'] = $_SESSION['username'].' using PSU Analytics';
		$meta['last_modified_by'] = $_SESSION['username'].' using PSU Analytics';
		$meta['title'] = $this->name;
		$meta['subject'] = $this->name;
		$meta['description'] = $this->name.', generated using PSU Analytics.';
		$meta['keywords'] = $this->id.' office 2007 openxml php psuanalytics '.$_SESSION['username'];
		$meta['category'] = 'PSU Analytics';
		$meta['file_name'] = $this->id;
		$meta['types'] = array();	

		if( !isset( $this->sql ) ) {
			$meta['headings'] = array_keys($this->data[0]);
			$data = $this->data;
		}
		else {
			$meta['headings'] = array_keys($this->sql->cols);

			foreach($this->sql->cols as $key=>$field) {
				// in theory we can/should check more types, but even this simple check is WAYYYYY better than csv
				$meta['types'][$key] = ($field->type=='VARCHAR2' || $field->type == 'VARCHAR') ? 'string' : '';
			}// end foreach
			
			$data = $this->sql->data;
		} // end else

		PSU::xlsx( $data, $meta );
	}// end xlsx

	/**
	 * sort handler for simple chart data sorts
	 */
	public static function _chart_data_sort($a, $b){
		if( $a[0] == $b[0] ) {
			return 0;
		}//end if

		return $a[0] < $b[0] ? 1 : -1;
	}//end _chart_data_sort

	/**
	 * create the graph for the report
	 */
	public function _graph_line(){
		$type = 'line';

		$this->data;

		if( $title ) {
			$this->graph->options('title', $title);
		}//end if

		if( $this->x && $this->y) {
			$this->graph_data = array();
			foreach( $this->data as $row ) {
				$this->graph_data[] = array( PSUGraph::js_time($row[$this->x]), $row[$this->y] );
			}//end foreach	
		}//end if

		$series = new PSUGraphSeries($this->id, $this->graph_data, ucwords($type).'Series');

		$options = array(
			'allow_zoom' => false,
			'show_titlebar' => false
		);

		$this->graph->options($options);
		$this->graph->style(array(
			'title' => ' ',
			'height' => '300px',
			'width' => '900px'
		));

		$x_option_args = array(
			'format_string' => 'MMM&nbsp;DD,&nbsp;YYYY',
			'useUTC' => false
		);

		$x_option = new PSUGraphOption('formatter', $x_option_args, 'DateFormatter');
		$series_option = new PSUGraphOption('x_axis_formatter', $x_option_args, 'DateFormatter');

		$this->graph->options('axis_bottom', $x_option, 'object');

		$major_tick_args = array(
			'count' => 5,
			'opacity'=>100,
			'show'=>true,
			'thickness'=>1
		);

		$this->graph->options['axis_bottom']->options('major_ticks', $major_tick_args, 'object');

		$minor_tick_args = array(
			'opacity'=>20,
			'show'=>false,
			'color'=>'rgb(255,0,0)',
			'size'=>'100%',
			'thickness'=>1
		);

		$this->graph->options['axis_bottom']->options('minor_ticks', $minor_tick_args, 'object');

		$this->graph->addAxis( new PSUGraphAxis('x', 'x', ucwords(str_replace('_', ' ', $this->x))) );
		$this->graph->addAxis( new PSUGraphAxis('y', 'y', ucwords(str_replace('_', ' ', $this->y))) );

		$series->options($series_option);
		$series->options('title', $this->graph->axis['count']->caption);
		$series->options('drawPoints', true);
		$this->graph->addSeries($series);
		
		return $this->graph;
	}//end _graph_line

	/**
	 * initializes a standard field-based pie chart
	 */
	public function _graph_simple($type = 'pie'){
		$type = $_GET['graph_type'] ? $_GET['graph_type'] : $type;

		$this->data;

		if( substr( $this->summary_field, -4 ) == 'code' ) {
			$field_title = ucwords( str_replace( '_', ' ', str_replace('_code', '', $this->summary_field) ) );
		} else {
			$field_title = ucwords( str_replace( '_', ' ', $this->summary_field ) );
		}//end if

		$this->graph_data = array();
		foreach( $this->_chart_data as $key => $value ) {
			if( $key != 'psuchart_total' ) {
				if( $type == 'pie' ) {
					$this->graph_data[] = array( $value, $key );
				} else {
					$this->graph_data[] = array( $value, $key );
				}//end if
			}//end if
		}//end foreach
		usort( $this->graph_data, array( 'PSUReport', '_chart_data_sort' ) );

		$series = new PSUGraphSeries($this->id, $this->graph_data, ucwords($type).'Series');

		$options = array(
			'allow_zoom' => false,
			'show_titlebar' => false
		);

		$this->graph->options($options);
		$this->graph->style(array(
			'title' => ' ',
			'height' => '250px',
			'width' => '600px'
		));

		$series_options = array(
			'title' => $field_title,
			'defaultColors' => $this->default_colors
		);

		if( $type == 'pie' ) {
			$series_options['hint_string'] = '[series_title]<br/>[label]: [x] of [total] ([percent]%)';
		}//end if

		if( $type == 'bar' ) {
			$series_options['orientation'] = 'horizontal';
		}//end if

		$series->options( $series_options );

		if( $type == 'bar') {
			$this->graph->addAxis( new PSUGraphAxis('count', 'x', 'Count') );
			$this->graph->addAxis( new PSUGraphAxis($this->summary_field, 'y', $field_title) );
		}//end if

		$this->graph->addSeries($series);
		
		return $this->graph;
	}//end _graph_simple

	/**
	 * magic getter
	 */
	public function __get($property){
		if( $property == 'name' ) {
			return $this->name( get_class($this) );
		} elseif($property == 'source') {
			return $this->source();
		} elseif($property == 'graph') {
			return $this->graph();
		} elseif($property == 'colors') {
			return json_encode($this->default_colors);
		} elseif($property == 'cols') {
			//if we get here, $this->cols was not set. That means
			// we are relying on the column information of a SQL statement
			return $this->sql->cols;
		} elseif($property == 'data') {
			// if we get here, $this->data is not set.  That means
			// we are relying on the data generated from a PSUSQL statement
			return $this->sql->data;
		} elseif($property == 'count') {
			// if we get here, $this->count is not set.  That means
			// we are relying on the count generated from a PSUSQL statement
			return $this->sql->count;
		} elseif($property == 'graph_data'){
			// $this->graph_data was not set if we get here.  Let's generate that by accessing
			// $this->data.
			// lets force the sql statement to run
			$this->data;
			
			// now that the sql has run, is there a graph_data property set?  If not, return the sql data
			return $this->graph_data ? $this->graph_data : $this->data;
		} elseif( $property == 'pagination' ) {
			return $this->sql->pagination;
		}//end if
	}//end __get
}//end class PSUReport

class PSUReportDrill{
	/**
	 * field this drill down targetted at
	 */
	public $field;

	/**
	 * get parameters for executing the drill down
	 */
	public $params = array();

	/**
	 * url for report to drill to
	 */
	public $url;

	/**
	 * field to retrieve value from
	 */
	public $value;

	/**
	 * constructor
	 */
	public function __construct($field, $target, $value = null, $inherit_params = true) {
		$this->field = $field;

		if( $target instanceof PSUReport ) {
			$this->url = '/webapp/analytics/report/'.$target->id.'/';
		} else {
			if( strpos( $target, '/' ) === 0 || strpos( $target, 'http' ) === 0 || strpos( $target, 'mailto' ) === 0 ) {
				$this->url = $target;
			} else {
				$this->url = '/webapp/analytics/report/'.$target.'/';
			}//end else
		}//end else

		if( $inherit_params ) {
			$this->params = $_GET;
		}//end if

		if( $value === null ) {
			$value = $field;
		}//end if

		$this->value = $value;

		if( $field != $value ) {
			$this->params[ $field ] = $value;
		}//end if
	}//end constructor	
}//end class PSUReportDrill

class PSUReportFilter {
	/**
	 * field name
	 */
	public $field;

	/**
	 * field type
	 */
	public $type;

	/**
	 * field operator
	 */
	public $operator;

	/**
	 * field options
	 */
	public $options;

	/**
	 * filter value
	 */
	public $value;

	/**
	 * constructor
	 */
	public function __construct( $field, $value, $operator = '=', $type = 'text', $options = array() ) {
		$this->field = $field;
		$this->value = $value != null ? trim($value) : null;
		$this->type = $type;
		$this->operator = $operator;
		$this->options = $options;
	}//end __construct

	public function values() {
		if( $this->operator == 'any' || $this->operator == 'not_any') {
			$return_values = array();
			$values = explode(',', $this->value);
			foreach( $values as $key => $value ) {
				$return_values[ $this->field . '_' . $key ] = trim($value);
			}//end foreach

			return $return_values;
		}//end if

		return array( $this->field => $this->value );
	}//end bind

	public function where() {
		$values = $this->values();

		if( count( $values ) > 1 ) {
			$where = 'AND (';
			$field_where = '';
			foreach( $values as $key => $value ) {
				if( $key ) {
					if( $field_where ) {
						$field_where .= $this->operator == 'any' ? 'OR ' : 'AND ';
					}//end if

					$field_where .= $this->field.' '.($this->operator == 'not_any' ? '<>' : '=').' :'.$key.' ';
				}//end if
			}//end foreach
			$where .= $field_where;
			$where .= ')';
		} elseif($this->field) {
			$where = 'AND '.$this->field.' '.$this->operator.' :'.$this->field.' ';
		}//end if
		return $where;
	}//end where
}//end class PSUReportField
