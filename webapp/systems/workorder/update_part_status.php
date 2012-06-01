<?php

for($i=0; $i<$_POST['num_ordered']; $i++) //check for items
{
	$shipping = $_POST['ship'.$i];
	if($shipping=="")
		$shipping=0;
	
	$update_query = "update shop_workorder_items set ordered=1, part_shipping=".$shipping.",cost=cost+".$shipping." where id=".$_POST['ordered'.$i];
	if($_POST['ordered'.$i]!="")
		$GLOBALS['SYSTEMS_DB']->Execute($update_query);
}
for($i=0; $i<$_POST['num_received']; $i++) //check for items
{
	$update_query = "update shop_workorder_items set received=1 where id=".$_POST['received'.$i];
	if($_POST['received'.$i]!="")
		$GLOBALS['SYSTEMS_DB']->Execute($update_query);
}
for($i=0; $i<$_POST['num_undo']; $i++) //check for items
{
	$update_query = "update shop_workorder_items set received=0 where id=".$_POST['undo'.$i];
	if($_POST['undo'.$i]!="")
		$GLOBALS['SYSTEMS_DB']->Execute($update_query);
}
	
/********End Confirmation Email **************/	
header("Location: parts_status.html");

?>