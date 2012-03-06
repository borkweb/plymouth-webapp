<?php

class PSU_Template_Factory {
	public static function create() {
		$tpl = new PSUTemplate;

		$cache = new PSUMemcache( 'psutpl-connect' );

		$connect = new PSU_Template_Resource_Connect( $cache );
		$connect->register( $tpl );

		return $tpl;
	}
}
