<?php

/*
 *    Single Item from GLPI Inventory
 *
 *
 */
namespace PSU\Inventory;
class Item implements \PSU\ActiveRecord{
	static $id;
	static $psu_name;
	static $serial;
	static $notes;
	static $state;
	static $model;
	static $manufacturer;
	static $type;
	static $price;
	static $condition;
	static $description;
	static $filepath;

	public function __construct( $args ){
		$this->id = $args['id'];
		$this->psu_name = $args['psu_name'];
		$this->serial = $args['serial'];
		$this->notes = $args['notes'];
		$this->state = $args['state'];
		$this->model = $args['model'];
		$this->manufacturer = $args['manufacturer'];
		$this->type = $args['type'];
		$this->price = $args['price'];
		$this->condition = $args['condition'];
		$this->description = $args['description'];
		$this->filepath = $args['filepath'];

	}//end __construct

	public static function get( $id ){
		return self::row( $id );
	}//end get

	public static function row( $id ){
	
	}//end row

	public function save( $method = NULL ){

	}//end save

	public function delete(){

	}//end delete
	
}//end class
