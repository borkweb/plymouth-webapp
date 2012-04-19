<?php

require 'PSUModels/Model.class.php';

class AnejfApply extends Model {
	public function __construct( $f = array(), $protected = true ) {
		$this->id_ = new FormNumber('label=Sumission ID:');
		$this->regyear_ = new FormNumber;
		$this->submitted_ = new FormText('label=Submit Date:');

		$this->first_name = new FormText('maxlength=75&size=15&required=1');
		$this->last_name = new FormText('maxlength=75&size=15&required=1');
		$this->instrument = new FormSelect(array('options' => self::instruments(), 'required' => 1, 'maxlength' => 10));
		$this->address1 = new FormText('label=Address Line 1:&maxlength=75&required=1');
		$this->address2 = new FormText('label=Address Line 2:&maxlength=75');
		$this->city = new FormText('required=1&maxlength=40');
		$this->state = new FormSelect(array('options' => self::states(), 'required' => 1, 'maxlength' => 2));
		$this->zip = new FormText('maxlength=10&size=10&required=1');
		$this->high_school = new FormText('label=Name of High School:&maxlength=75&required=1');
		$this->high_school_grade = new FormSelect(array('label' => 'High School Grade:', 'options' => array(array(10, 'Sophomore'), array(11, 'Junior'), array(12, 'Senior')), 'required' => 1, 'maxlength' => 2));
		$this->high_school_enrollment = new FormNumber('label=Approximate Enrollment of High School:&size=5&maxlength=6');
		$this->band_size = new FormNumber('label=Number of Students in High School Band:&size=5&maxlength=6');

		$this->director_name = new FormText('label=Band Director\'s Name:&required=1&maxlength=75');
		$this->director_email = new FormText('required=1&maxlength=100');
		$this->director_student_rating = new FormSelect(array('label' => 'Director\'s Rating of Student:', 'options' => self::ratings(), 'required' => 1, 'maxlength' => 1));
		$this->student_past_participate = new FormSelect(array('label' => 'Has the student participated in this festival previously?', 'options' => FormSelect::yesno(), 'required' => 1, 'maxlength' => 1));
		$this->student_chair = new FormSelect(array('options' => self::chairs(4), 'required' => 1, 'maxlength' => 2));
		$this->student_section_players = new FormNumber('maxlength=3&size=3&required=1');

		$this->honorband_years = new FormNumber('label=How many years?');
		$this->honorband_recent_chair = new FormSelect(array('label' => 'Most recent chair?', 'options' => self::chairs(10), 'maxlength' => 2));

		$this->solosf_music_level = new FormSelect(array('label' => 'Most recent level of music performed:', 'options' => array(array(3, 'III'), array(4, 'IV'), array(5, 'V'), array(6, 'VI')), 'maxlength' => 1));
		$this->solosf_rating = new FormSelect(array('label' => 'Rating:', 'options' => array('A (I)', 'A-', 'B+', 'B (II)', 'B-', 'C+', 'C (III)', 'C-', 'Other'), 'maxlength' => 8));

		$this->comments = new FormTextarea('rows=10&cols=85&label=Additional Comments (optional)');

		parent::__construct( $f, $protected );

		if( null == $this->regyear_->value() ) {
			$this->regyear_->value( $GLOBALS['ANEJF']['YEAR'] );
		}
	}//end __construct

	public function save() {
		$f = $this->form();

		$rs = PSU::db('myplymouth')->Execute("SELECT * FROM anejf WHERE id_ = -1");
		$sql = PSU::db('myplymouth')->GetInsertSQL( $rs, $f );

		$result = PSU::db('myplymouth')->Execute( $sql );


		if( $result === false ) {
			return false;
		}

		$id = PSU::db('myplymouth')->Insert_ID();
		$this->id_->value($id);

		return $id;
	}//end save

	public static function chairs( $max ) {
		$chairs = array();

		for( $i = 1; $i <= $max; $i++ ) {
			$chair = $i . PSU::ordinal_suffix($i);
			$chairs[] = array($i, $chair);
		}

		$chairs[] = array(-1, 'Other');

		return $chairs;
	}//end chair

	public static function instruments() {
		return array(
			  'Alto Sax'
			, 'Tenor Sax'
			, 'Bari Sax'
			, 'Trumpet'
			, 'Trombone'
			, 'Piano'
			, 'Bass'
			, 'Drums'
			, 'Percussion'
			, 'Vibes'
			, 'Guitar'
		);
	}//end instruments

	/**
	 * Subset of states.
	 */
	public static function states() {
		return array(
			  array('CT', 'Connecticut')
			, array('ME', 'Maine')
			, array('MA', 'Massachusetts')
			, array('NH', 'New Hampshire')
			, array('RI', 'Rhode Island')
			, array('VT', 'Vermont')
		);
	}//end ratings

	public static function ratings() {
		return array(
			  array('1', 'Good (I)')
			, array('2', 'Excellent (II)')
			, array('3', 'Outstanding (III)')
		);
	}//end ratings
}//end class AnejfApply
