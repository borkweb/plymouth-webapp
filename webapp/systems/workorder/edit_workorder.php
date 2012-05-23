<?php
/**
 * edit_workorder.php
 *
 * Repair Shop Workorder System - update db records on workorder edit
 *
 * @version		1.0
 * @author		Alan Baker <a_bake@plymouth.edu>
 * @copyright 2008, Plymouth State University, ITS
 */ 
 
	if(!checkAuthorization($_SESSION['username']))
		exit("Authorization Required");
	$workorder = $_POST['wo'];
	$query = "SELECT note FROM shop_user_notes WHERE workorder_id=? ORDER BY id desc";
	$args = array(
		'workorder_id' => $workorder
	);
	$res = $GLOBALS['SYSTEMS_DB']->Execute($query,$args);
	$array = $res->FetchRow();
	$user_note = $array['note'];
	$query = "SELECT note FROM shop_tech_notes WHERE workorder_id=? ORDER BY id desc";
	$args = array(
		'workorder_id' => $workorder
	);
	$res = $GLOBALS['SYSTEMS_DB']->Execute($query, $args);
	$array = $res->FetchRow();
	$tech_note = $array['note'];
	$query = "SELECT current_status,university_owned,username, device_manufacturer, device_model, device_serial, university_owned, send_email, tech_assigned FROM shop_workorder WHERE id=?";
	$args = array(
		'id' => $workorder
	);
	$res = $GLOBALS['SYSTEMS_DB']->Execute($query,$args);
	$array = $res->FetchRow();
	$current_status = $array['current_status'];
	$psu_property = $array['university_owned'];
	$device_owner = $array['username'];
	$current_model= $array['device_model'];
	$current_manufacturer = $array['device_manufacturer'];
	$current_serial = $array['device_serial'];
	$current_prop = $array['university_owned'];
	$send_email = $array['send_email'];
	$current_tech = $array['tech_assigned'];
	
	if(strcasecmp($current_serial, $_POST['serial'])!=0 && $_POST['serial']!="")
	{
		$serial_query = "UPDATE shop_workorder SET device_serial=? WHERE id=?";
		$args = array(
			'device_serial' =>$_POST['serial'],
			'id' => $workorder
		);
		$GLOBALS['SYSTEMS_DB']->Execute($serial_query,$args) or die("DB error updating serial number");
	}
	if(strcasecmp($current_tech, $_POST['tech'])!=0 && $_POST['tech']!="")
	{
		$tech_query = "UPDATE shop_workorder SET tech_assigned=? WHERE id=?";
		$args = array(
			'tech_assigned' =>$_POST['tech'],
			'id' => $workorder
		);
		$GLOBALS['SYSTEMS_DB']->Execute($tech_query,$args) or die("DB error updating assigned tech");
	}
	if(strcasecmp($current_model, $_POST['model'])!=0 && $_POST['model']!="")
	{
		$model_query = "UPDATE shop_workorder SET device_model=? WHERE id=?";
		$args = array(
			device_model =>$_POST['model'],
			'id' => $workorder
		);
		$GLOBALS['SYSTEMS_DB']->Execute($model_query,$args) or die("DB error updating model");
	}
	if(strcasecmp($current_manufacturer, $_POST['manufacturer'])!=0 && $_POST['manufacturer']!="")
	{
		$manufacturer_query = "UPDATE shop_workorder SET device_manufacturer=? WHERE id=?";
		$args = array(
			'device_manufacturer' =>$_POST['manufacturer'],
			'id' => $workorder
		);
		$GLOBALS['SYSTEMS_DB']->Execute($manufacturer_query,$args) or die("DB error updating manufacturer");
	}
	if($current_prop != $_POST['psuproperty'] && $_POST['psuproperty']!="")
	{
		$property_query = "UPDATE shop_workorder SET university_owned=? WHERE id=?";
		$args = array(
			'university_owned'=>$_POST['psuproperty'],
			'id' => $workorder
		);
		$GLOBALS['SYSTEMS_DB']->Execute($property_query,$args) or die("DB error updating university owned field");
	}
	
	if(strcasecmp($user_note,strip_tags($_POST['comments']))!=0) // if notes to user changed, insert new record in notes
	{
		$note_query = "INSERT INTO shop_user_notes(workorder_id,note,username) VALUES( ?,?,?)";
		$args = array(
			'workorder_id' => $workorder,
			'note' => strip_tags($_POST['comments']),
			'username' => $_SESSION['username']
		);
		$GLOBALS['SYSTEMS_DB']->Execute($note_query, $args) or die("DB error updating notes to user");
	}
	
	if(strcasecmp($tech_note,strip_tags($_POST['notes']))!=0) // if tech notes changed, insert new record
	{
		$tech_query = "INSERT INTO shop_tech_notes(workorder_id,note,username) VALUES( ?,?,?)";
		$args = array(
			'workorder_id' => $workorder,
			'note' => strip_tags($_POST['notes']),
			'username' => $_SESSION['username']
		);
		$GLOBALS['SYSTEMS_DB']->Execute($tech_query, $args) or die("DB error updating tech notes");
	}
	
	$total_due = $_POST['total_due'];
	
	/************ ITEM REMOVAL CODE ****************/
	$to = $GLOBALS['ORDER_USERNAME']."@mail.plymouth.edu";
	$subject = "Repair Shop Part Order Cancellation/RMA";
	$headers = "From: ".$GLOBALS['SHOP_EMAIL']  . "\r\n" .
	"Reply-To: ".$GLOBALS['SHOP_EMAIL']  . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

	$message = "Part Cancellation for Work Order #".$_POST['wo']."\n\n";
	$items_cancel = "";
	for($i=0; $i<$_POST['totalitems']; $i++) //check for item removals
	{
		$element = "remove".$i;
		if($_POST[$element]!="")
		{
			$remove_item = "UPDATE shop_workorder_items SET removed=1,removed_username=? WHERE id=?";
			$args = array(
				'removed_username' => $_SESSION['username'],
				'id' => $_POST[$element]
			);
			$GLOBALS['SYSTEMS_DB']->Execute($remove_item, $args) or die("DB error updating current status");
			$query="SELECT * FROM shop_workorder_items WHERE id=?";
			$args = array(
				'id' => $_POST[$element]
			);
			$res = $GLOBALS['SYSTEMS_DB']->Execute($query, $args);
			$item_array = $res->FetchRow();
			if($item_array['part']==1)
			{
				if($item_array['warranty']==1)
					$items_cancel.="WARRANTY - ";
				$items_cancel.=$item_array['vendor']." ".$item_array['item']."\t".$item_array['product_num']."\t$".number_format($item_array['part_cost'],2,'.',',')."\t\n";
			}
			$total_due -= (float)$item_array['cost']; 
		}
	}
	$message .=$items_cancel;
	if($items_cancel!="")
		PSU::mail($to, $subject, $message, $headers);
	/*********** END ITEM REMOVAL CODE **************************/
	
	for($i=0; $i<$GLOBALS['NUM_ITEMS']; $i++) //check for new item additions
	{
		$element = "item".$i;
		$cost = "cost".$i;
		$hours = "labor".$i;
		if($_POST[$element]!="")
		{
			$insert_query = "INSERT INTO shop_workorder_items(workorder_id,item,billable_hours,cost,username,time_entered) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)";
			$args = array(
				'workorder_id'=> $workorder,
				'item' => strip_tags($_POST[$element]),
				'billable_hours' => $_POST[$hours],
				'cost' => $_POST[$cost],
				'username' => $_SESSION['username']
			);
			$GLOBALS['SYSTEMS_DB']->Execute($insert_query,$args) or die("DB error updating items & costs");
			$total_due += (float)$_POST[$cost];
		}
	}
	
	
	
	if(strcasecmp($current_status,$_POST['status'])!=0) // if status has changed, insert new record & update workorder entry
	{
		if(strcasecmp(substr($_POST['status'],0,5),"Close")==0)
		{
			$update_current = "UPDATE shop_workorder SET current_status=?, closed=1, time_closed=CURRENT_TIMESTAMP(),payment_method=?, amount_charged=? WHERE id=?";
				if($_POST['status']=="Close: transferred to surplus")
					$amount_charged=0.00;
				else
					$amount_charged=$_POST['total_due'];
			$args = array(
				'current_status'=> $_POST['status'],
				'payment_method'=> $_POST['payment'],
				'amount_charged'=> $amount_charged,
				'id' => $workorder
			);
			$GLOBALS['SYSTEMS_DB']->Execute($update_current, $args) or die("DB error updating current status");	
		}
		else 
		{
			$close = " ";
			$update_current = "UPDATE shop_workorder SET current_status=?, closed=0, time_closed=0 WHERE id=?";
			$args = array(
				'current_status'=> $_POST['status'],
				'id' => $workorder
			);
			$GLOBALS['SYSTEMS_DB']->Execute($update_current, $args) or die("DB error updating current status");
		}
		$insert_current = "INSERT INTO shop_status_history(workorder_id,status,username) VALUES(?,?,?)";
		$args = array(
			'workorder_id'=> $workorder,
			'status'=> $_POST['status'],
			'username'=> $_SESSION['username']
		);
		$GLOBALS['SYSTEMS_DB']->Execute($insert_current, $args) or die("DB error inserting current status update into history");
		
		/***************** Email User if Ready for Pickup ***********************/
		if(	$send_email && strcasecmp($_POST['status'], "Ready for pickup")==0 ) //ready for pickup & we should send emails
		{
			$to      = $device_owner."@mail.plymouth.edu";
			$subject = "PSU Repair Shop Work Order #".$workorder;
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    "Reply-To: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
	
			$message = "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
				<title>PSU Repair Shop Workorder Update</title>
			</head>
			<body>
				<p align=\"center\"><h3>PSU Repair Shop Work Order# ".$workorder."</h3></p>	
				<p>This work order is complete.  Your total due is $".number_format($total_due,2,'.',',')."</p>
				<p>Your device may be picked up at the PSU Repair Shop in Highland Hall room 011 between 10:00am and 4:00pm Monday through Friday.  If you are unable to pick up during these hours, please call to schedule an appointment or to make alternate arrangements.  Payments may be made by check or credit card, we do not currently accept cash.</p>
				<br />
				<p align=\"center\"><strong>Plymouth State University Repair Shop</strong><br />
				535-3499 <a href=\"mailto:".$GLOBALS['SHOP_EMAIL']."\">".$GLOBALS['SHOP_EMAIL']."</a><br />
				Open Monday - Friday 10:00am - 4:00pm</p>
			</body>
			</html>";
			PSU::mail($to, $subject, $message, $headers);
		}
		if($send_email && strcasecmp($current_status,"Submitted to LLC")==0 && strcasecmp($_POST['status'],"Submitted for processing")==0) //was at the helpdesk, now being checked into the shop
		{
			$to      = $device_owner."@mail.plymouth.edu";
			$subject = "PSU Repair Shop Work Order #".$workorder;
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    "Reply-To: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
	
			$message = "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
				<title>PSU Repair Shop Workorder Update</title>
			</head>
			<body>
				<p align=\"center\"><h3>PSU Repair Shop Work Order# ".$workorder."</h3></p>	
				<p>Your device has been transferred from the ITS Helpdesk to the Repair Shop to begin work.  This is just an informational message to update you on your device's location and status.  No action is required at this time.  Please continue to check your e-mail occasionally for further updates and notification of when your repair is finished.</p>
				<br />
				<p align=\"center\"><strong>Plymouth State University Repair Shop</strong><br />
				535-3499 <a href=\"mailto:".$GLOBALS['SHOP_EMAIL']."\">".$GLOBALS['SHOP_EMAIL']."</a><br />
				Open Monday - Friday 10:00am - 4:00pm</p>
			</body>
			</html>";
			PSU::mail($to, $subject, $message, $headers);
		}
		
		if(	$send_email && strcasecmp($_POST['status'], "Transferred to LLC")==0 ) //transferred to the helpdesk & we should send emails
		{
			$to      = $device_owner."@mail.plymouth.edu";
			$subject = "PSU Repair Shop Work Order #".$workorder;
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    "Reply-To: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
	
			$message = "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
				<title>PSU Repair Shop Workorder Update</title>
			</head>
			<body>
				<p align=\"center\"><h3>PSU Repair Shop Work Order# ".$workorder."</h3></p>	
				<p>Your device has been transferred to the ITS Helpdesk at the Lamson Learning Commons.  You may pick it up there during normal library operating hours. Those hours are listed at <a href=\"http://library.plymouth.edu/hours\">http://library.plymouth.edu/hours</a>.</p>
				<br />
				<p align=\"center\"><strong>Plymouth State University Repair Shop</strong><br />
				535-3499 <a href=\"mailto:".$GLOBALS['SHOP_EMAIL']."\">".$GLOBALS['SHOP_EMAIL']."</a><br />
				Open Monday - Friday 10:00am - 4:00pm</p>
			</body>
			</html>";
			PSU::mail($to, $subject, $message, $headers);
		}
		if(	$send_email && strcasecmp($_POST['status'], "Close: returned to user")==0 ) //returned to user & we should send emails
		{
			$to      = $device_owner."@mail.plymouth.edu";
			$subject = "PSU Repair Shop Work Order #".$workorder;
			$headers  = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$headers .= "From: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    "Reply-To: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
	
			$message = "
			<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html xmlns=\"http://www.w3.org/1999/xhtml\">
			<head>
				<title>Customer Service Evaluation</title>
			</head>
			<body>
				<p align=\"center\"><h3>PSU Repair Shop Work Order# ".$workorder."</h3></p>	
				<p>We appreciate the opportunity to have served you and we would appreciate your feedback so that we can improve our service and support!  Please, take a few minutes to fill out this anonymous survey. <a href=\"http://www.plymouth.edu/webapp/survey/fillsurvey.php?sid=163\" >http://www.plymouth.edu/webapp/survey/fillsurvey.php?sid=163</a></p>
				<br />
				<p align=\"center\"><strong>Plymouth State University Repair Shop</strong><br />
				535-3499 <a href=\"mailto:".$GLOBALS['SHOP_EMAIL']."\">".$GLOBALS['SHOP_EMAIL']."</a><br />
				Open Monday - Friday 10:00am - 4:00pm</p>
			</body>
			</html>";
			PSU::mail($to, $subject, $message, $headers);
		}
	}
	
	
	header("Location: admin.html"); //redirect back to admin main page 
?>