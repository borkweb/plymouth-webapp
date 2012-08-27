<?php

namespace TrainingTracker;

include 'FilterIterators/AdminFilterIterator.php';
include 'FilterIterators/MeritFilterIterator.php';
include 'FilterIterators/PromotionFilterIterator.php';
include 'FilterIterators/StaffFilterIterator.php';
include 'FilterIterators/ValidUserFilterIterator.php';
include 'FilterIterators/MenteeFilterIterator.php';
include 'FilterIterators/MentorFilterIterator.php';


class StaffCollection extends \PSU\Collection {

	public static $child = '\TrainingTracker\Staff';

	public function __construct(){
	}

	public function get(){
		// Load API
		$client = \PSU::api('backend');
		// Get all of the people that work at the helpdesk.
		$users = $client->get('support/users');
		return $users;
	}//end get

	// mentees. selects the trainee and sta permission from callog
	public function mentees($person = null){
		if ( ! $person ){
			$person = $this->getIterator();
		}//end if
		return new MenteeFilterIterator( $person );
	}//end mentees

	// valid users have callog permissions and are active
	public function valid_users($person = null){
		if ( ! $person ){
			$person = $this->getIterator();
		}//end if
		return new ValidUserFilterIterator( $person );
	}//end staff_filter

	// filter for admins, people with jr. shift supervisors or greater
	public function admins($person = null){
		if ( ! $person ){
			$person = $this->getIterator();
		}//end if
		return new AdminFilterIterator( $person );
	}//end staff

	// filter for trainee, sta and shift_leader callog permissions
	public function staff($person = null){
		if ( ! $person ){
			$person = $this->getIterator();
		}//end if
		return new StaffFilterIterator( $person );
	}//end staff
	
	public function mentors($person = null){
			if ( ! $person ){
				$person = $this->getIterator();
			}//end if
			return new MentorFilterIterator( $person );
	}//end mentors

	public function merit_users($person = null){
			if ( ! $person ){
				$person = $this->getIterator();
			}//end if
			return new MeritFilterIterator( $person );
	}//end mentors

	public function promotion_users($person = null){
			if ( ! $person ){
				$person = $this->getIterator();
			}//end if
			return new PromotionFilterIterator( $person );
	}//end mentors

}// end StaffCollection 

