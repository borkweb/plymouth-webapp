<?php
	function checkAuthorization($username)
	{
		$query = "SELECT * FROM shop_users WHERE username='".$username."'";
		$res = $GLOBALS['SYSTEMS_DB']->Execute($query);
		if($res->RecordCount()<1)
		{
			if($GLOBALS['IS_HD'])
				$_SESSION['LOCATION']=2; //helpdesk = 2
			else
				$_SESSION['LOCATION']=1; //repair shop =1
			return false;
		}
		else
		{
			$array = $res->FetchRow();
			$_SESSION['privileged']=$array['privileged'];
			$_SESSION['financial']=$array['financial'];
			if($GLOBALS['IS_HD'] || (($GLOBALS['IP'][2]==32 || $GLOBALS['IP'][2]==33) && $array['location']==2))
				$_SESSION['LOCATION']=2; //helpdesk = 2
			else
				$_SESSION['LOCATION']=1; //repair shop =1
			return true;
		}
	}
	
function formatPhone($number)
{
    $number = preg_replace('/[^\d]/', '', $number); //Remove anything that is not a number
    $num_length = strlen($number);
	if($num_length == 10)
     {
        return '('.substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6);
     }
	 else if($num_length == 7)
	 {
    	return  substr($number, 0, 3) . '-' . substr($number, 3);
	 }
	 return $number;  //number didn't match 10 or 7 digit format, pass back unformatted
}
?>