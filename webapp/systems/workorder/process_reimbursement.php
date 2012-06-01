<?php
$month = $_POST['month'];
if($month<10)
	$month="0".$month;
$insert_query = "insert into shop_reimbursement(description,value,applied) values('".addslashes($_POST['description'])."',".$_POST['val'].",'".$_POST['year']."-".$month."-01 12:01:00')";

$GLOBALS['SYSTEMS_DB']->Execute($insert_query) or die("DB error inserting reimbursement");
		
/********End Confirmation Email **************/	
header("Location: admin.html");

?>