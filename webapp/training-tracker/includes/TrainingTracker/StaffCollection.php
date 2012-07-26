<?php

namespace TrainingTracker;


class StaffCollection extends \PSU\Collection {

	public static $child = '\TrainingTracker\Staff';

	public function __construct(){
	}

	public function get(){
		
		$client = \PSU::api('backend'); //load api
		$users = $client->get('support/users'); //get all the people that work at the help desk
		return $users;
		
	}//end get


	//mentees. selects the trainee and sta permission from callog
	public function mentees($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new Staff_MenteeFilterIterator( $it );

	}//end mentees

//valid users have callog permissions and are active
	public function valid_users($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new valid_FilterIterator( $it );

	}//end staff_filter

	//filter for admins, people with jr. shift supervisors or greater
	public function admins($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new admin_FilterIterator( $it );

	}//end staff

	//filter for trainee, sta and shift_leader callog permissions
	public function staff($it = null){
		if ( ! $it ){
			$it = $this->getIterator();
		}//end if

		return new Staff_FilterIterator( $it );

	}//end staff
	
	public function mentors($it = null){
			if ( ! $it ){
				$it = $this->getIterator();
			}//end if
			return new Staff_MentorFilterIterator( $it );
	}//end mentors

	public function merit_users($it = null){
			if ( ! $it ){
				$it = $this->getIterator();
			}//end if
			return new Staff_meritFilterIterator( $it );
	}//end mentors

	
}//end function

class admin_FilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'manager' == $staff->privileges || 'supervisor' == $staff->privileges || 'webguru' == $staff->privileges;
	}//end accept
}//end 

class Staff_meritFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges || 'manager' == $staff->privileges;
	}//end accept
}//end 

class Staff_FilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges;
	}//end accept
}//end 


class valid_FilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges || 'manager' == $staff->privileges || 'supervisor' == $staff->privileges || 'webguru' == $staff->privileges;
	}//end accept
}//end 


class Staff_MenteeFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentee = $this->current();

		return 'trainee' == $mentee->privileges || 'sta' == $mentee->privileges;
	}//end accept
}//end 


class Staff_MentorFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentor = $this->current();

		return 'shift_leader' == $mentor->privileges || 'supervisor' == $mentor->privileges;
	}//end accept
}//end class
