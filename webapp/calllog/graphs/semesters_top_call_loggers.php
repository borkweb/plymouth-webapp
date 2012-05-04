<?php


include_once FUNCTIONS_DIR.'/call_log_graph_functions.php';
include_once 'jpgraph/jpgraph.php';
include_once 'jpgraph/jpgraph_bar.php';

$call_results = Array();
$call_results = returnSemestersTopCallLoggers();
$num_results = sizeOf($call_results);


$x_data = Array();
$y_data = Array();
for($i=0; $i<$num_results; $i++){
	$x_data[] = $call_results[$i]['last_name'];
	$y_data[] = $call_results[$i]['callcount'];
}// end for


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

$graph->SetShadow();
$graph->xaxis->SetTickLabels($x_data);
$graph->xaxis->SetLabelAlign('right','center','right');
$graph->yaxis->SetLabelAlign('center','bottom');
$graph->title->Set('Semester\'s Top Call Loggers');

$bplot = new BarPlot($y_data);
$bplot->SetFillColor("#708090");
$bplot->SetWidth(0.5);
$bplot->SetYMin(0);

$graph->Add($bplot);

$graph->Stroke();

?>