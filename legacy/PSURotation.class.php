<?php

require_once 'PSUFiles.class.php';

class PSURotation {
	static $defaults = array(
		'alt' => null,
		'caption' => null,
		'class' => 'middle',
		'depth' => -1,
		'type' => 'image'
	);
	public $dir;
	public $extension;
	public $path;
	public $url;

	public function __construct($dir, $params = array()) {

		$params = PSU::params( $params, self::$defaults );

		foreach( $params as $key => $value ) {
			$this->$key = $value;
		}//end foreach

		$this->base_dir = '/web/pscpages';
		$this->dir = $dir;

		if( $this->set ) {
			$this->set( $this->set );
		} else {
			$this->_random();
		}//end else
	}//end __construct

	/**
	 * returns the alt text for the given file, if available
	 */
	public function alt(){
		if( $this->alt ) {
			return $this->alt;
		}//end if

		$this->alt = @file_get_contents( $this->base_dir.'/'.$this->dir . $this->filename . '.alt');

		return $this->alt;
	}//end alt

	/**
	 * returns the caption for the given file, if available
	 */
	public function caption(){
		if( $this->caption ) {
			return $this->caption;
		}//end if

		$this->caption = @file_get_contents( $this->base_dir.'/'.$this->dir . 'captions/' . $this->filename . '.txt');
		
		return $this->caption;
	}//end caption

	/**
	 * spits out the file
	 */
	public function file(){
		header('Content-Type: image/'.$this->extension);
		die( readfile( $this->path ) );
	}//end file

	/**
	 * instantiates a PSURotation object and returns it
	 */
	public static function get( $dir, $params = array() ) {
		return new self( $dir, $params );	
	}//end get

	/**
	 * instantiates a group of PSURotation objects and returns them
	 */
	public static function get_collection( $dir, $params = array() ) {
		$params = PSU::params( $params, self::$defaults );

		if( $params['type'] == 'image' ) {
			$files = PSUFiles::getImageArray($dir, 0, $params['depth']);
		} else {
			$files = PSUFiles::getImageArray($dir, 0, $params['depth'], array('txt', 'html'));
		}//end if

		foreach( (array) $files as $key => $file ) {
			if( strpos( $file, '/thumbs/' ) !== false ) {
				unset( $files[$key] );
			}//end if
		}//end foreach

		sort($files);

		$num_files = count($files);

		$collection = array();

		$collection_count = 0;
		while( $collection_count < $num_files && $collection_count < 4 ) {
			$random_index = rand( 0, count( $files ) - 1 );

			$params['set'] = $files[ $random_index ];

			$collection[] = new self( $dir, $params );
			unset( $files[ $random_index ] );
			sort($files);

			$collection_count = count( $collection );
		}//end while

		return $collection;
	}//end get_collection

	/**
	 * returns the link for the given file, if available
	 */
	public function link(){
		if( $this->link ) {
			return $this->link;
		}//end if

		$this->link = @file_get_contents( $this->base_dir.'/'.$this->dir . $this->filename . '.link');
		return $this->link;
	}//end link

	/**
	 * sets the current image to a specific image
	 */
	public function set( $path ) {
		$this->path = $path;

		$path_info = pathinfo( $this->path );

		$this->file = $path_info['basename'];
		$this->extension = $path_info['extension'] == 'jpg' ? 'jpeg' : $path_info['extension'];
		$this->filename = $path_info['filename'];

		$this->server = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];

		$this->url = $this->server.str_replace( $this->base_dir, '', $this->path );
	}//end set

	/**
	 * returns the thumb url for the given file, if available
	 */
	public function thumb(){
		if( $this->thumb ) {
			return $this->thumb;
		}//end if

		return $this->thumb = $this->server.'/'.$this->dir.'thumbs/'.preg_replace( '/\.([^\.]+)$/', '.thumb.\1', $this->file );
	}//end link


	/**
	 * selects a random file from a given directory
	 */
	public function _random() {
		if( $this->type == 'image' ) {
			$files = PSUFiles::getImageArray($this->base_dir.$this->dir, 0, $this->depth);
		} else {
			$files = PSUFiles::getImageArray($this->base_dir.$this->dir, 0, $this->depth, array('txt', 'html'));
		}//end if

		$this->set( PSUFiles::chooseRandomElement($files) );
	}//end random
}//end class PSURotation
