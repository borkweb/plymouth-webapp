<?php

$to = $GLOBALS['ORDER_USERNAME']."@mail.plymouth.edu";
$subject = "Repair Shop Part Request";
$headers = 'From: computer-service@plymouth.edu' . "\r\n" .
'Reply-To: computer-service@plymouth.edu' . "\r\n" .
'X-Mailer: PHP/' . phpversion();
	
$send_mail = true;

$id = $_POST['id'];
$vendor = $_POST['vendor'];
if($vendor == "Other")
	$vendor = $_POST['other'];
else if($vendor=="Stock")
	$send_mail=false;
	
$message = "Part Request for Work Order #".$id."\n\n
Vendor:\t".$vendor."\n";
	
for($i=0; $i<5; $i++) //check for items
{
	$element = "item".$i;
	$quant= "quantity".$i;
	$ourcost = "cost".$i;
	$custcost = "customercost".$i;
	$prodnum = "productnum".$i;
	$warranty = "warranty".$i;
	
	if($_POST[$element]!="")
	{
		$item = strip_tags($_POST[$element]);
		$quantity = $_POST[$quant];
		$partcost = $_POST[$ourcost];
		$customercost = $_POST[$custcost];
		$productnumber = $_POST[$prodnum];
		$totalcost = $customercost*$quantity;
		if($_POST[$warranty]==1)
		{
			$warranty_part = 1;
			$warranty_text = " - Warranty";
		}
		else
		{
			$warranty_part = 0;
			$warranty_text = "";
		}
		 
		$insert_query = "INSERT INTO shop_workorder_items(workorder_id,item,vendor,product_num,quantity,part,warranty,ordered,part_cost,part_charged,cost,username,time_entered) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)";
		//echo $insert_query;
		$args = array(
			'workorder_id'=>$id,
			'item'=>$quantity." x ".$item.$warranty_text,
			'vendor'=>addslashes($vendor),
			'product_num'=>$productnumber,
			'quantity'=>$quantity,
			'part'=>1,
			'warranty'=>$warranty_part,
			'ordered'=>0,
			'part_cost'=>$partcost,
			'part_charged'=>$customercost,
			'cost'=>$totalcost,
			'username'=>$_SESSION['username']
		);
		$GLOBALS['SYSTEMS_DB']->Execute($insert_query, $args) or die("DB error updating items & costs");
		if($warranty_part)
			$message.="WARRANTY - ";
		$message .=$quantity."\tx\t".$productnumber."\t".$item."\t$".$partcost."\t\n";	
	}
}

$update_current = "UPDATE shop_workorder SET current_status='Delayed: waiting for parts', closed=0, time_closed=0 WHERE id=?";
$args = array(
	'id'=>$id
);
$GLOBALS['SYSTEMS_DB']->Execute($update_current, $args) or die("DB error updating current status");
$insert_current = "INSERT INTO shop_status_history(workorder_id,status,username) VALUES(?,'Delayed: waiting for parts','Part Request')";
$GLOBALS['SYSTEMS_DB']->Execute($insert_current, $args) or die("DB error inserting current status update into history");

if($send_mail)
	PSU::mail($to, $subject, $message, $headers);
	
/********End Confirmation Email **************/	
header("Location: parts_request.html?id=".$id."&s=1");

?>