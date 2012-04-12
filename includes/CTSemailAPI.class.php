<?php
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';
class CTSemailAPI{

			function emailUser($reserve){
				$categories=reserveDatabaseAPI::categories();
				$locations=reserveDatabaseAPI::locations();
			$message='
				<html>
					<body>
						<p>
							Below you will find your copy of the media equipment request which you or someone you authorized
							submitted via the on-line request form. This loan is subject to the Equipment Reservation
							Agreement (ERA).  If approved, you are to abide by the terms of the ERA.  The full text of the
							agreement can be found in myPlymouth under the Computing Resources Channel and in the Equipment 
							Reservations link.
						</p>
						<p>
							If you did not authorize this loan or you do not agree to the Equipment Reservation Agreement,
							you must e-mail itsmedia@plymouth.edu and request cancellation of this loan. 
						</p>
						<p>
							You will be contacted by a member of Classroom Technology Services ONLY if there is a need for 
							further clarification or if there is a conflict with equipment availability.
						</p>
						<p>Thank you.</p>
						';

			$message2='<ul>
					<h2>Your/Submitter Contact Information</h2>
					<li><strong>Name: </strong>'. $reserve['submit_first_name']. ' ' .$reserve['submit_last_name'].'</li>

					<h2>Event Contact Information</h2>
					<li><strong>Name: </strong>'.$reserve['first_name'].' '. $reserve['last_name'].'</li>
					<li><strong>Phone: </strong>'.$reserve['phone'].'</li>

					<h2>Event Information</h2>
					<li><strong>Course Title or Event Name: </strong>'.$reserve['title'].'</li>
					<li><strong>Location: </strong>'.$locations[$reserve['location']]. ' in room '. $reserve['room'] . '</li>
					<li><strong>Start Date and Time: </strong>'.$reserve['start_date'] .' at '. $reserve['start_time'].'</li>
					<li><strong>End Date and Time: </strong>'.$reserve['end_date'] .' at '. $reserve['end_time'].'</li>
					<li><strong>Pickup/Dropoff Method: </strong>';

						if($reserve['reserve_type'] == "equipment"){
							$message3='I will pickup/dropoff at the helpdesk.';
						}else{
							$message3='The CTS department will dropoff the equipment at the location specified.';
						}
						
			$message4='</li>
					<li><strong>Comments/Purpose: </strong>
						<p>'.$reserve['comments'].'</p>
					</li>
					<h2>Equipment Requested</h2>';

						foreach($reserve['equipment'] as $item){
							$message5 .="<li>$categories[$item]</li>";
						}
						
				$message6='</ul>';
						$headers = 'MIME-Version:Ê1.0'."\r\n".'Content-type: text/html;'."\r\n".'From: Classroom Technology Office <itsmedia@plymouth.edu>'."\r\n";
						mail( $reserve['email'],'Media Request',$message.$message2.$message3.$message4.$message5.$message6,$headers);
					
			}
	function emailCTS($reserve, $insert_id){
				$categories=reserveDatabaseAPI::categories();
				$locations=reserveDatabaseAPI::locations();
			
				$message='<ul>
					<li>Reservation ID:'. $insert_id .'</li>
					<h2>Submitter Contact Information</h2>
					<li><strong>Name: </strong>'. $reserve['submit_first_name']. ' ' . $reserve['submit_last_name'].'</li>

					<h2>Event Contact Information</h2>
					<li><strong>Name: </strong>'.$reserve['first_name'].' '. $reserve['last_name'].'</li>
					<li><strong>Phone: </strong>'.$reserve['phone'].'</li>

					<h2>Event Information</h2>
					<li><strong>Course Title or Event Name: </strong>'.$reserve['title'].'</li>
					<li><strong>Location: </strong>'.$locations[$reserve['location']]. ' in room '. $reserve['room'] . '</li>
					<li><strong>Start Date and Time: </strong>'.$reserve['start_date'] .' at '. $reserve['start_time'].'</li>
					<li><strong>End Date and Time: </strong>'.$reserve['end_date'] .' at '. $reserve['end_time'].'</li>
					<li><strong>Pickup/Dropoff Method: </strong>';

						if($reserve['reserve_type'] == "equipment"){
							$message2='I will pickup/dropoff at the helpdesk.';
						}else{
							$message2='The CTS department will dropoff the equipment at the location specified.';
						}
						
			$message3='</li>
					<li><strong>Comments/Purpose: </strong>
						<p>'.$reserve['comments'].'</p>
					</li>
					<h2>Equipment Requested</h2>';

						foreach($reserve['equipment'] as $item){
							$message4 .="<li>$categories[$item]</li>";
						}
						$message5='</ul>';
						$headers = 'MIME-Version:Ê1.0'."\r\n".'Content-type: text/html;'."\r\n".'From: Classroom Technology Office <itsmedia@plymouth.edu>'."\r\n";
						mail( 'drallen1@plymouth.edu','Media Request from ' . $reserve['first_name'] . ' ' . $reserve['last_name'],$message.$message2.$message3.$message4.$message5,$headers);


	}//end email user
			function emailUserApproved($reservation_idx){
				$reserve=reserveDatabaseAPI::by_id($reservation_idx);
				$index=$reserve[$reservation_idx]['building_idx'];
				$start_time=date("g:i A",strtotime($reserve[$reservation_idx]['start_time']));
				$end_time=date("g:i A",strtotime($reserve[$reservation_idx]['end_time']));
				$categories=reserveDatabaseAPI::categories();
				$locations=reserveDatabaseAPI::locations();
			
				$message='<ul>
					<h1>Your equipment reservation has been approved!</h1>
					<h2>Below is the information about your reservation.</h2>

					<h2>Contact Information</h2>
					<li><strong>Name: </strong>'.$reserve[$reservation_idx]['fname'].' '. $reserve[$reservation_idx]['lname'].'</li>
					<li><strong>Phone: </strong>'.$reserve[$reservation_idx]['phone'].'</li>

					<h2>Event Information</h2>
					<li><strong>Course Title or Event Name: </strong>'.$reserve[$reservation_idx]['title'].'</li>
					<li><strong>Location: </strong>'.$locations[$index]. ' in room '. $reserve[$reservation_idx]['room'] . '</li>
					<li><strong>Start Date and Time: </strong>'.$reserve[$reservation_idx]['start_date'] .' at '. $start_time.'</li>
					<li><strong>End Date and Time: </strong>'.$reserve[$reservation_idx]['end_date'] .' at '. $end_time.'</li>
					<li><strong>Pickup/Dropoff Method: </strong>';

						if($reserve[$reservation_idx]['reserve_type'] == "equipment"){
							$message2='I will pickup/dropoff the equipment at the helpdesk.';
						}else{
							$message2='The CTS department will dropoff the equipment at the location specified.';
						}
						
			$message3='</li>
					<li><strong>Comments/Purpose: </strong>
						<p>'.$reserve[$reservation_idx]['memo'].'</p>
					</li>
					<h2>Equipment Requested</h2>'.
					$reserve[$reservation_idx]['request_items'].'</ul> ';
						$headers = 'MIME-Version:Ê1.0'."\r\n".'Content-type: text/html;'."\r\n".'From: Classroom Technology Office <itsmedia@plymouth.edu>'."\r\n";
						mail( $reserve[$reservation_idx]['email'],'Media Request Approved!',$message.$message2.$message3.$message4,$headers);


	}

}


