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
/*	public function mentees(){
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->mentees_filter() as $i ){
			$mentees_array[$ct]['username'] = $i->username;
			$mentees_array[$ct]['privileges'] = $i->privileges;
			$mentees_array[$ct]['wpid'] = $i->wpid;
			$mentees_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentees_array;

}//end mentees_filter */

/*	public function mentors(){
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->mentors_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentors_array;

}//end mentors */

/*	public function admins(){
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->admins_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach

		return $mentors_array;

}//end mentees */

/*
	public function valid_users(){ //All the people that work at the help desk
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->valid_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach
		return $mentors_array;
	}
 */

/*	public function staff(){ //everybody minus jr. shift supervisors.
		$users = new StaffCollection();
		$ct = 0;

		foreach ( $users->staff_filter() as $i ){
			$mentors_array[$ct]['username'] = $i->username;
			$mentors_array[$ct]['privileges'] = $i->privileges;
			$mentors_array[$ct]['wpid'] = $i->wpid;
			$mentors_array[$ct]['name'] = $i->name;
			$ct++;
		}//end foreach
		
		foreach($mentors_array as &$staff){
			
			$stats = get_stats($staff['wpid']);
			$staff['progress'] = $stats['progress'];
		}

		//print_r($mentors_array);
		//die();
		return $mentors_array;

}//end staff */

	
}//end function

class admin_FilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'manager' == $staff->privileges || 'supervisor' == $staff->privileges || 'webguru' == $staff->privileges;
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
