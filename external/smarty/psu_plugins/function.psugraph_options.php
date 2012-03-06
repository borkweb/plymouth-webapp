<?php
function smarty_function_psugraph_options($params, &$smarty){
	$tpl = new PSUSmarty();
	$tpl->assign('args', $params);
	return $tpl->fetch('/web/pscpages/webapp/analytics/templates/graph.options.tpl');
}//end smarty_function_psugraph_options
