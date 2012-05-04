<?php	 

include_once(FUNCTIONS_DIR.'/call_log_graph_functions.php');
include_once 'jpgraph/jpgraph.php';
include_once 'jpgraph/jpgraph_bar.php';

// store the data to be drawn
$call_result = Array(); 

function makeGraph($x_data, $y_data, $num_results, $title="Statistics", $graph_type="bar", $graph_scale="textint")
{
	// default graph info
	$width=600; 
	$height=500;

	$top = 60;
	$bottom = 30;
	$left = 80;
	$right = 30;

	if($graph_type != 'csv' && $num_results == 0)
	{
		header('Content-type: image/png');
		readfile( $GLOBALS['BASE_DIR'] . '/images/no-calls.png');
		exit;
	}

	// Set the basic parameters of the graph 
	switch($graph_type){
		case "line":
			//do line graph here
			break;
		// not really a graph, returns comma seperated values
		case "csv":
			header("content-type: text/csv");
			header('Content-Disposition: attachment; filename="statistics.csv"');
			$columns = implode(',', $x_data);
			$rows = implode(',',$y_data);
			echo $columns."\n".$rows;
			break;
		case "bar":
		default: // bar is default
			$graph = new Graph($width,90 + 10*$num_results,'auto');
			$graph->SetScale($graph_scale);
			// Nice shadow
			$graph->SetShadow();
			$graph->Set90AndMargin($left,$right,$top,$bottom);

			// Setup labels
			$graph->xaxis->SetTickLabels($x_data);
			// Label align for X-axis
			$graph->xaxis->SetLabelAlign('right','center','right');
			// Label align for Y-axis
			$graph->yaxis->SetLabelAlign('center','bottom');

			// Create a bar pot
			$bplot = new BarPlot($y_data);
			$bplot->SetFillColor("#708090");
			$bplot->SetWidth(0.5);
			$bplot->SetYMin(0);
			//$bplot->SetYMin(1990);
			$graph->title->Set($title);

			$graph->Add($bplot);
			$graph->Stroke();
	}
}//end makeGraph


$statistic = $_GET['statistic'];
$time_delimit = $_GET['time_delimit'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$graph_type = $_GET['graph_type'];

// if no parameters are passed, use default
if($statistic=='')
{
	$statistic = 'top_call_loggers';
	$time_delimit = 'today';
}

if($statistic=='top_call_loggers')
{
	// attempt to draw default (daily) statistics
	$call_result = ($time_delimit == 'date_range') ? returnTopCallLoggers($time_delimit, $start_date, $end_date) : returnTopCallLoggers($time_delimit);
	$num_results = sizeOf($call_result);

	if(!empty($call_result))
	{
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['last_name'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));
	}
} // end if(statistic=='top_call_loggers')
else if($statistic=='top_callers')
{
	$call_result = ($time_delimit == 'date_range') ? returnTopCallers($time_delimit, $start_date, $end_date) : returnTopCallers($time_delimit);
	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['caller_username'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
} 
else if($statistic=='calls_by_date')
{
	$call_result = ($time_delimit == 'date_range') ? returnCallsByDate($time_delimit, $start_date, $end_date) : returnCallsByDate($time_delimit);
	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['call_date'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}
else if($statistic=='calls_by_type')
{
	$call_result = ($time_delimit == 'date_range') ? returnCallsByType($time_delimit, $start_date, $end_date) : returnCallsByType($time_delimit);

	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['call_type'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}
else if($statistic=='calls_by_category')
{
	$call_result = ($time_delimit == 'date_range') ? returnCallsByCategory($time_delimit, $start_date, $end_date) : returnCallsByCategory($time_delimit);

	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['call_category'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}
else if($statistic=='tests_by_instructor')
{
	$call_result = ($time_delimit == 'date_range') ? returnTestsByInstructor($time_delimit, $start_date, $end_date) : returnTestsByInstructor($time_delimit);

	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['instructor'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}
else if($statistic=='tests_by_date')
{
	$call_result = ($time_delimit == 'date_range') ? returnTestsByDate($time_delimit, $start_date, $end_date) : returnTestsByDate($time_delimit);

	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['date'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}
else if($statistic=='evaluations_by_dept')
{
	$call_result = ($time_delimit == 'date_range') ? returnEvaluationsByDept($time_delimit, $start_date, $end_date) : returnEvaluationsByDept($time_delimit);

	$num_results = sizeOf($call_result);

	if(!empty($call_result)){
		$x_data = Array();
		$y_data = Array();
		for($i=0; $i<$num_results; $i++){
			$x_data[] = $call_result[$i]['dept'];
			$y_data[] = $call_result[$i]['callcount'];
		}// end for
		$title = ucwords(str_replace("_"," ",$statistic.' for '.($time_delimit == 'date_range' ? $start_date.' through '. $end_date : $time_delimit) ));

	}// end if (!empty($call_results))
}

if(strcmp($graph_type, "csv")==0)
{
	makeGraph($x_data, $y_data, $num_results, $title, "csv");
}
else
{
	makeGraph($x_data, $y_data, $num_results, $title, "bar");
}
