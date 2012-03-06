<?php
function smarty_function_psureport_link($params, &$smarty){
	if( !isset( $params['wrap'] ) ) {

		if( $params['include'] ) {
			$params['wrap'] = '<li>%s %s</li>';
		} else {
			$params['wrap'] = '<li>%s</li>';
		}//end else
	}//end if

	$link = '';

	if( $params['report'] ) {
		$report = 'PSUReport_'.str_replace('-','_',$params['report']);
		@include_once $GLOBALS['BASE_DIR'] . '/includes/reports/'.$report.'.class.php';	

		if( ! class_exists( $report ) ) {
			return '';
		}//end if

		$reflection = new ReflectionClass( $report );
		try{
			$name = $reflection->getStaticPropertyValue( 'name' );
			if( $reflection->hasMethod( 'authZ' ) ) {	
				$authz = call_user_func( array($report, 'authZ') );
			} else {
				$authz = call_user_func( 'PSUReport', 'authZ' );
			}//end else
		} catch( Exception $err ) {
			$report = new $report('reportlink');
			$name = $report->name;
			$authz = $report->authZ();
		}//end catch

		if( $authz ) {
			$link = '<a href="'.$GLOBALS['BASE_URL'].'/report/'.str_replace('_','-',$params['report']).'/';
			if( $params['query_string'] ) {
				$link .= '?'.$params['query_string'];
			}//end if	
			$link .= '">'.( $params['name'] ? $params['name'] : $name).'</a>';
			if( $params['include'] ) {
				$link = sprintf($params['wrap'], $link, $params['include']);
			} else {
				$link = sprintf($params['wrap'], $link);
			}//end else
		}//end if
	}//end if

	unset( $report );

	return $link;
}//end smarty_function_psugraph_options
