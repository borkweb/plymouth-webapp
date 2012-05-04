<?php

exit();

$db->debug=true;
$rows = $db->GetAll("SELECT * FROM temp");

foreach($rows as $row)
{
	$mac = substr($row['mac'],0,2).':'.substr($row['mac'],2,2).':'.substr($row['mac'],4,2).':'.substr($row['mac'],6,2).':'.substr($row['mac'],8,2).':'.substr($row['mac'],10,2);
	$sql = "INSERT INTO hardware_inventory (email, mac_address, computer_name) VALUES ('clusters@plymouth.edu', '{$row['name']}','$mac')";
	$db->Execute($sql);
}

?>