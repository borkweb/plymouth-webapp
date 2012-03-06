<?php

namespace PSU\Robot;

/**
 * 
 */
class Factory {
	static $instance = null;

	/**
	 * Create a robot, using Config settings for host/port,
	 * providing a PSU::is() conditional.
	 *
	 * @param $config
	 * @param $is Conditional for PSU::is()
	 * @return Object that impelements RobotInterface
	 */
	public static function conditional_robot_from_config( \PSU\Config $config, $is ) {
		if( \PSU::is( $is ) ) {
			return self::robot_from_config( $config );
		}

		return new Dummy( null, null );
	}//end conditional_robot_from_config

	/**
	 * Create a robot, using Config settings for host/port.
	 *
	 * @param $config
	 * @return Object that impelements RobotInterface
	 */
	public static function robot_from_config( \PSU\Config $config ) {
		if( $host = $config->get( 'robot', 'host' ) ) {
			return new \PSU\Robot( $host, $config->get( 'robot', 'port', $config->get( 'robot', 'port', 8888 ) ) );
		}

		return new Dummy( null, null );
	}//end robot_from_config

	/**
	 * Set the Robot singleton to a custom object that
	 * implements PSU\Robot\RobotInterface.
	 */
	public static function set( RobotInterface $instance ) {
		self::$instance = $instance;
	}//end set

	/**
	 * Return the Robot singleton.
	 */
	public static function get() {
		if( null === self::$instance ) {
			self::$instance = new Dummy( null, null );
		}

		return self::$instance;
	}//end get
}//end class Factory
