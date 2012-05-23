<?php
/**
 * process_workorder.php
 *
 * Repair Shop Workorder System - insert workorder into database
 *
 * @version		1.0
 * @author		Alan Baker <a_bake@plymouth.edu>
 * @copyright 2008, Plymouth State University, ITS
 */ 
	$fields[0] = "sn";
	$fields[1] = "givenName";
	$name=$GLOBALS['AD']->user_info($_POST['username'],$fields);
	 $last_name = $name[0]['sn'][0];
	$first_name = $name[0]['givenname'][0];
	$name = addslashes($last_name.", ".$first_name);

	if($_POST['housing']=="on-campus")
		$housing = 1;
	else
		$housing = 0;
	$customer_query = "INSERT INTO shop_patrons(username,name,student_housing,phone_primary,phone_other) VALUES('".$_POST['username']."','".$name."',".$housing.",'".$_POST['phone_primary']."','".$_POST['phone_other']."') ON DUPLICATE KEY UPDATE student_housing=VALUES(student_housing), name=VALUES(name), phone_primary=VALUES(phone_primary), phone_other=VALUES(phone_other)";
	
	$GLOBALS['SYSTEMS_DB']->Execute($customer_query) or die("DB error entering user information");
	
	if($_POST['keyboard']==1)
		$keyboard =1;
	else
		$keyboard =0;
	if($_POST['monitor']==1)
		$monitor =1;
	else
		$monitor =0;
	if($_POST['mouse']==1)
		$mouse =1;
	else
		$mouse =0;
	if($_POST['ac_adapter']==1)
		$ac_adapter =1;
	else
		$ac_adapter =0;
	if($_POST['printer']==1)
		$printer =1;
	else
		$printer =0;
	if($_POST['cable']==1)
		$cable =1;
	else
		$cable =0;
	if($_POST['scanner']==1)
		$scanner =1;
	else
		$scanner =0;
	if($_POST['software']==1)
		$software =1;
	else
		$software =0;
	if($_POST['property_type']=="university")
		$uni_owned =1;
	else
		$uni_owned =0;
	$now = date("n/j/Y g:i:s a");
	
	$status = "Submitted for processing";
	if($_SESSION['LOCATION']==2)
		$status = "Submitted to LLC";
	
	$workorder_query = "INSERT INTO shop_workorder(username, device_type, device_manufacturer, device_model, device_serial, periph_monitor, periph_keyboard, periph_mouse, periph_ac_adapter, periph_printer, periph_printer_cable, periph_scanner, software, periph_other, pw_system, pw_windows, pw_screensaver, problem, university_owned, current_status,policy_agreed,send_email) VALUES('".$_POST['username']."','".$_POST['type']."','".strip_tags($_POST['manufacturer'])."','".strip_tags($_POST['model'])."','".strip_tags($_POST['serial'])."',".$monitor.",".$keyboard.",".$mouse.",".$ac_adapter.",".$printer.",".$cable.",".$scanner.",".$software.",'".strip_tags($_POST['other'])."','".base64_encode(strip_tags($_POST['pw_system']))."','".base64_encode(strip_tags($_POST['pw_windows']))."','".base64_encode(strip_tags($_POST['pw_screensaver']))."','".strip_tags($_POST['problem'])."',".$uni_owned.",'".$status."',".$_POST['policy_accepted'].",".$_POST['send_email'].")";

	$GLOBALS['SYSTEMS_DB']->Execute($workorder_query) or die("DB error entering new workorder");
	
	$query = "SELECT id from shop_workorder where username='".$_POST['username']."' ORDER BY id DESC";
	$res = $GLOBALS['SYSTEMS_DB']->Execute($query) or die("Error retrieving workorder number");
	$res_array = $res->FetchRow();
	$workorder_num = $res_array['id'];
	
	$status_query = "Insert into shop_status_history(workorder_id,status,username) values(".$workorder_num.",'".$status."','".$_SESSION['username']."')";
	$GLOBALS['SYSTEMS_DB']->Execute($status_query) or die("DB error updating status history");

if($_POST['send_email'])
{
	/*********Send Confirmation Email *************/	
	$to      = $_POST['username']."@mail.plymouth.edu";
	$subject = "PSU Repair Shop Work Order #".$workorder_num;
	$headers  = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$headers .= "From: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
    "Reply-To: ".$GLOBALS['SHOP_EMAIL'] . "\r\n" .
    "X-Mailer: PHP/" . phpversion();
	if($_SESSION['LOCATION']==1)
		$loc = "PSU Repair Shop, Highland Hall Room 011";
	else
		$loc = "ITS Helpdesk, Lamson Learning Commons";
	$message = "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head>
			<title>PSU Repair Shop Workorder Confirmation</title>
		</head>
		<body>
			<p align=\"center\"><h3>PSU Repair Shop Work Order# ".$workorder_num."</h3></p>
			<br />
			<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">
				<tr><td colspan=\"3\"><h4>Customer Information</h4></td></tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Name:</strong></td>
					<td>".$_POST['name']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Username:</strong></td>
					<td>".$_SESSION['username']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Primary Phone:</strong></td>
					<td>".$_POST['phone_primary']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Other Phone:</strong></td>
					<td>".$_POST['phone_other']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Work Order Initiated At:</strong></td>
					<td>".$loc."</td>
				</tr>
			</table>
			<br />
			<br />
			<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">
				<tr><td colspan=\"3\"><h4>Device Information</h4></td></tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Manufacturer:</strong></td>
					<td>".$_POST['manufacturer']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Model:</strong></td>
					<td>".$_POST['model']."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Type:</strong></td>
					<td>".ucfirst($_POST['type'])."</td>
				</tr>
				<tr><td width=\"30\">&nbsp;</td>
					<td><strong>Serial:</strong></td>
					<td>".$_POST['serial']."</td>
				</tr>\n";
	if($uni_owned)
		$message.="<tr><td></td><td colspan=\"2\">Device is PSU property</td></tr>";
	else
		$message.="<tr><td></td><td colspan=\"2\">Device is personal property</td></tr>";
	$message.="<tr><td colspan=\"3\"><h4>Peripherals Dropped Off</h4></td></tr>
				<tr><td></td><td>";
	if($monitor)
		$message .="Monitor, ";
	if($keyboard)
		$message .="Keyboard, ";
	if($mouse)
		$message .="Mouse, ";
	if($ac_adapter)
		$message .="AC Adapter, ";
	if($printer & $_POST['type']!="printer")
		$message .="Printer, ";
	if($scanner & $_POST['type']!="scanner")
		$message .="Scanner, ";
	if($cable)
		$message .="Printer or Scanner Cable, ";
	if($software)
		$message .="Software";
	$message .= "</td></tr>\n";
	if($_POST['other']!="")
		$message .="<tr><td></td><td colspan=\"2\">".wordwrap(stripslashes($_POST['other']),65,"\n")."</td></tr>";
	$message .="</table>
	<br /><br />
	<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\">
		<tr><td colspan=\"3\"><h5>Description Of The Problem</h5></td></tr>
		<tr>
			<td width=\"30\">&nbsp;</td>
			<td colspan=\"2\">".wordwrap(stripslashes($_POST['problem']),65,"\n")."</td>
		</tr>
	</table>
	<br />
	<p align=\"left\"><strong>Plymouth State University Repair Shop</strong><br />
	See our <a href=\"http://www.plymouth.edu/office/information-technology/help/repair/policies/\">hours, rates, and policies online</a>, if you have any questions, please give us a call at 535-3499 or email us at <a href=\"mailto:".$GLOBALS['SHOP_EMAIL']."\">".$GLOBALS['SHOP_EMAIL']."</a>.  Thank You.</p>
	</body>
	</html>";
	
	mail($to, $subject, $message, $headers);
	
	/********End Confirmation Email **************/
}
if($_SESSION['privileged'])
	header("Location: confirmation.html?l=0&wo=".$workorder_num); // don't log out
else
	header("Location: confirmation.html?l=1&wo=".$workorder_num);

?>
