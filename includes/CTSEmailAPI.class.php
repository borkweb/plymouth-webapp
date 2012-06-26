<?php
class CTSEmailAPI{
	//class that is used to email users
	public static function headers(){
		return 'MIME-Version: 1.0'."\r\n".'Content-type: text/html;'."\r\n".'From: Classroom Technology Reservation <itsmedia@plymouth.edu>'."\r\n";
	}
	public static function admin_headers(){
		return 'MIME-Version: 1.0'."\r\n".'Content-type: text/html;'."\r\n".'From: CTS RESERVATION SYSTEM'."\r\n";
	}

	public function email_user($reserve){
				//grabs the neccessary information to display properly
				$categories=ReserveDatabaseAPI::categories();
				$locations=ReserveDatabaseAPI::locations();

				$email=new \PSUSmarty;
				//creates a new template
				$email->assign('categories', $categories);
				$email->assign('locations', $locations);
				$email->assign('reserve', $reserve);
				//fetches the contect from the template
				$contents=$email->fetch( $GLOBALS['TEMPLATES'] . '/email.user.tpl' );
				//adds the headers
				//semds the mail to the user
				return PSU::mail( $reserve['email'],'Media Request',$contents,self::headers());
					
			}
	function email_CTS($reserve, $insert_id){
				//grabs the neccessary information to display properly
				$categories=ReserveDatabaseAPI::categories();
				$locations=ReserveDatabaseAPI::locations();
				$email=new \PSUSmarty;
				//creates a new template
				$email->assign('insert_id', $insert_id);
				//grabs the insert id (this is the reservation id)
				$email->assign('categories', $categories);
				$email->assign('locations', $locations);
				$email->assign('reserve', $reserve);
				$title="Media Request from " . $reserve['first_name'] . " " . $reserve['last_name'];
				//fetches the contents of the template
				$contents=$email->fetch( $GLOBALS['TEMPLATES'] . '/email.admin.tpl' );
				return PSU::mail( "itsmedia@plymouth.edu",$title,$contents,self::admin_headers());
		

			}
	function email_user_cancelled($reservation_idx){
		//grabs the reservation information to display it to the user
				$reserve=ReserveDatabaseAPI::by_id($reservation_idx);
				$reservation=$reserve[$reservation_idx];
				$index=$reserve[$reservation_idx]['building_idx'];
				$categories=ReserveDatabaseAPI::categories();
				$locations=ReserveDatabaseAPI::locations();
		
				$email=new \PSUSmarty;
				$email->assign('categories', $categories);
				$email->assign('locations', $locations);
				$email->assign('reserve', $reservation);
				$contents=$email->fetch( $GLOBALS['TEMPLATES'] . '/email.user.cancel.tpl' );
				return PSU::mail( $reserve[$reservation_idx]['email'],'Media Request Cancelled!',$contents,self::headers());
			}

	function email_user_approved($reservation_idx){
		//emails the user when thier loan has been approved
				$reserve=ReserveDatabaseAPI::by_id($reservation_idx);
				$reservation=$reserve[$reservation_idx];
				$index=$reserve[$reservation_idx]['building_idx'];
				$categories=ReserveDatabaseAPI::categories();
				$locations=ReserveDatabaseAPI::locations();
		
				$email=new \PSUSmarty;
				$email->assign('categories', $categories);
				$email->assign('locations', $locations);
				$email->assign('reserve', $reservation);
				$contents=$email->fetch( $GLOBALS['TEMPLATES'] . '/email.user.approve.tpl' );
				return PSU::mail( $reserve[$reservation_idx]['email'],'Media Request Approved!',$contents,self::headers());
	}
}
