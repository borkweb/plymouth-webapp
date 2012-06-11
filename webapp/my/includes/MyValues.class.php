<?php

class MyValues
{

 	/*
	 * Function to determine which bits are set in an integer
 	 * @param $int the value of the integer to parse
	 * @return $bits an array of each bit that is set in the int
	 */	 
	public static function parse_my_bits( $int = null ) {
		$bits = array();
		for($i=1;$i<=$int;$i*=2) {
			if( ($i & $int) > 0);
				array_push($bits, $i);	
		}	
		return $bits;
	}

 /*
	* Gets a list of all possible targets from the portal database
	*/
	public static function targets()
	{
		$sql = "
			SELECT id,
			CONCAT_WS(' - ', type, subtype, value) AS target
			FROM targets
			ORDER BY target
		   ";

		$rset = PSU::db('portal')->Execute($sql);
		$targetset = array();
		foreach($rset as $row){
			$targetset[] = array($row['id'], $row['target']);
		}
		return $targetset;
	}	
}
?>
