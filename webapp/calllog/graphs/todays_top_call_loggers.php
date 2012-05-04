<?php

include_once FUNCTIONS_DIR.'/call_log_graph_functions.php';
include_once 'jpgraph/jpgraph.php';
include_once 'jpgraph/jpgraph_bar.php';

print_r(get_included_files());

$call_results = Array();
$call_results = returnTodaysTopCallLoggers();
$num_results = sizeOf($call_results);

if(!empty($call_results)){
	$x_data = Array();
	$y_data = Array();
	for($i=0; $i<$num_results; $i++){
		$x_data[] = $call_results[$i]['last_name'];
		$y_data[] = $call_results[$i]['callcount'];
	}// end for

	// Size of graph
	$width=600; 
	$height=500;

	// Set the basic parameters of the graph 
	$graph = new Graph($width,$height,'auto');
	$graph->SetScale("textint");


	$top = 60;
	$bottom = 30;
	$left = 80;
	$right = 30;
	$graph->Set90AndMargin($left,$right,$top,$bottom);


	// Nice shadow
	$graph->SetShadow();

	// Setup labels
	
	$graph->xaxis->SetTickLabels($x_data);

	// Label align for X-axis
	$graph->xaxis->SetLabelAlign('right','center','right');

	// Label align for Y-axis
	$graph->yaxis->SetLabelAlign('center','bottom');

	// Titles
	$graph->title->Set('Today\'s Top Call Loggers');

	// Create a bar pot
	$bplot = new BarPlot($y_data);
	$bplot->SetFillColor("#708090");
	$bplot->SetWidth(0.5);
	$bplot->SetYMin(0);
	//$bplot->SetYMin(1990);

	$graph->Add($bplot);
	$graph->Stroke();

}// end if
else{
	echo '
		  <html><head><title>Today\'s Top Call Loggers</title></head>
		  <body onload="setTimeout(window.close, 3000)">
		  <h2>No Calls Have Been Logged Yet Today.</h2>
		  <h3>This window will close automatically.</h3>
		  </body>
		  </html>';
}


?>
