<?php

namespace PSU\Festivals\MusicCareerDay;

require_once PSU_LEGACY_DIR . '/PSUModels/Model.class.php';

class Model extends \Model {
	public function __construct( $f = array(), $protected = true ) {
		$this->id_ = new \FormNumber('label=Sumission ID:');
		$this->submitted_ = new \FormText('label=Submit Date:');

		$this->first_name = new \FormText('maxlength=75&size=15&required=1');
		$this->last_name = new \FormText('maxlength=75&size=15&required=1');
		$this->instrument = new \FormSelect(array('options' => self::instruments(), 'required' => 1, 'maxlength' => 10));
		$this->address1 = new \FormText('label=Address Line 1:&maxlength=75&required=1');
		$this->address2 = new \FormText('label=Address Line 2:&maxlength=75');
		$this->city = new \FormText('required=1&maxlength=40');
		$this->state = new \FormSelect(array('options' => self::states(), 'required' => 1, 'maxlength' => 2));
		$this->zip = new \FormText('maxlength=10&size=10&required=1');
		$this->high_school = new \FormText('label=Name of High School:&maxlength=75&required=1');
		$this->high_school_grade = new \FormSelect(array('label' => 'High School Grade:', 'options' => self::grades(), 'required' => 1, 'maxlength' => 2));
		$this->high_school_enrollment = new \FormNumber('label=Approximate Enrollment of High School:&size=5&maxlength=6');
		$this->band_size = new \FormNumber('label=Number of Students in High School Band:&size=5&maxlength=6');

		$this->director_name = new \FormText('label=Band Director\'s Name:&required=1&maxlength=75');
		$this->director_email = new \FormText('required=1&maxlength=100');

		$this->comments = new \FormTextarea('label=Additional Comments (optional)');

		parent::__construct( $f, $protected );
	}//end __construct

	public function save() {
		$f = $this->form();

		$rs = \PSU::db('myplymouth')->Execute("SELECT * FROM mte_career_day WHERE id_ = -1");
		$sql = \PSU::db('myplymouth')->GetInsertSQL( $rs, $f );

		$result = \PSU::db('myplymouth')->Execute( $sql );

		if( $result === false ) {
			return false;
		}

		$id = \PSU::db('myplymouth')->Insert_ID();
		$this->id_->value($id);

		return $id;
	}//end save

	public static function grades() {
		return array(
			array(10, 'Sophomore'),
			array(11, 'Junior'),
			array(12, 'Senior'),
		);
	}//end grades

	public static function instruments() {
		return array(
			'Alto Sax',
			'Tenor Sax',
			'Bari Sax',
			'Trumpet',
			'Trombone',
			'Piano',
			'Bass',
			'Drums',
			'Percussion',
			'Vibes',
			'Guitar',
			'Other',
		);
	}//end instruments

	/**
	 * Subset of states.
	 */
	public static function states() {
		return array(
			array('CT', 'Connecticut'),
			array('ME', 'Maine'),
			array('MA', 'Massachusetts'),
			array('NH', 'New Hampshire'),
			array('RI', 'Rhode Island'),
			array('VT', 'Vermont'),
		);
	}//end states
}//end class Model
