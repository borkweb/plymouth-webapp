<?php

/**
 * 
 */
class TeacherCertTemplate extends \PSU\Template {
	function _wrap_method( $method, $args = null ) {
		// Maintain output buffering level, since an exception
		// throw in Smarty::fetch() may generate ob_start() that are
		// never ended. This will cause the failed template to show up
		// at the top of the page, above the PSUTemplate main.tpl.
		$ob_level_1 = ob_get_level();

		$callback = 'parent::' . $method;

		try {
			if( $args ) {
				return call_user_func_array( $callback, $args );
			} else {
				return call_user_func( $callback );
			}
		} catch( \PSU\ActiveRecord\NotFoundException $e ) {
			$out = "ActiveRecord could not find some data: " . $e->GetMessage();
		}

		$ob_level_2 = ob_get_level();

		for( ; $ob_level_2 > $ob_level_1; $ob_level_2-- ) {
			ob_end_clean();
		}

		return $out;
	}

	function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		$out = $this->_wrap_method( __FUNCTION__, func_get_args() );

		// Smarty::fetch() can optionally output or return content
		if( $display ) {
			echo $out;
		} else {
			return $out;
		}
	}//end fetch

	function _smarty_include() {
		$out = $this->_wrap_method( __FUNCTION__, func_get_args() );

		// _smarty_include expects to include(), which will output some
		// content. make sure we do that.
		if( $out ) {
			echo $out;
		}

		return;
	}//end fetch
}//end class TeacherCertTemplate
