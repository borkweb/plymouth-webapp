<?php
class CTSTemplate extends PSUTemplate{

	public function init_vars(){
		$hours=array();
		//generate numbers 1 through 12 for the hours
		for($i = 1; $i <=12; $i++){

			$hours[$i]=$i;
		}
		$minutes=array();
		//generate numbers 0 through 55 every 5 numbers (0,5,10,15 etc.)
		for($x = 0;$x <=55; $x+= 5){
			$minutes[$x]=$x;

		}
	
		$this->assign( 'hours', $hours );
		$this->assign( 'minutes', $minutes );	
		$this->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));

		//assign vars that are used throughout the whole system
		$this->assign('date_format','%m-%d-%Y');
		$this->assign('time_format','%l:%M %p');
		$this->assign('locations',ReserveDatabaseAPI::locations(false)); 
		$this->assign('user_level',ReserveDatabaseAPI::user_level());//this assigns the user to a manager (0) cts staff (1) or helpdesk (2)
		$status=array(
			"approved"=>"approved",
			"pending"=>"pending",
			"pending-pick-up"=>"pending pick-up",
			"ready-for-pick-up" => "ready for pick-up",
			"pending-delivery" => "pending delivery",
			"delivered" =>"delivered",
			"closed" => "closed",
			"missing-equipment" => "missing equipment",
			"loaned out"=>"loaned out",
			"outstanding" => "outstanding",
			"returned"=> "returned", 
			"cancelled"=>"cancelled",
		);
		$this->assign('status', $status);
		$this->assign('priority', array("normal", "high"));
		$this->assign( 'subitemlist', ReserveDatabaseAPI::get_subitems());


	}//end function init_vars

	public function init_technicians(){

		$query=new \PSU\Population\Query\IDMAttribute('cts','permission');
		$factory = new \PSU_Population_UserFactory_PSUPerson;
		$population= new \PSU_Population( $query, $factory );
		$population->query();
		foreach ($population as $person){
			$cts_technicians[$person->wp_id]=$person->formatName("f l");
		}
		$cts_technicians=array(NULL=>'Select a Technician') + $cts_technicians;
		$this->assign('cts_technicians', $cts_technicians);	
	}//init_technicians
	
	public function init_all_reservation_info($reservation_idx){
		$this->init_technicians();	
		$this->assign( 'subitems', ReserveDatabaseAPI::get_reserve_subitems($reservation_idx));

		$this->assign( 'subitemlist', ReserveDatabaseAPI::get_subitems());
		$this->assign( 'messages', ReserveDatabaseAPI::get_messages($reservation_idx));
		$this->assign( 'equipment', ReserveDatabaseAPI::get_equipment($reservation_idx));
		$equipment=ReserveDatabaseAPI::get_equipment($reservation_idx);
		$this->assign( 'equipment', $equipment);
		$equipment_info=ReserveDatabaseAPI::get_equipment_info($equipment);
		$this->assign('equipment_info',$equipment_info);

		$this->assign( 'reservation_idx', $reservation_idx);
		$this->assign( 'reservation' , ReserveDatabaseAPI::by_id($reservation_idx));


	}//get_all_reservation_info


}//end class initialize
