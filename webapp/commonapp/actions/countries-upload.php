<?php

PSU::db('banner')->debug = true;

$filename = $_FILES['uploadedfile']['tmp_name']; 

$datafile = fopen($filename,"r");
if ($datafile)
{
	while (!feof($datafile))
	{
		$items[]=fgetcsv($datafile, 8192);
	}
	fclose($datafile);
}

if( !CommonAppCountries::deleteOldCountryCodes() ) {
	$_SESSION['errors'][] = 'Unable to clear the country table before processing.';
}

elseif( !CommonAppCountries::insertCountryCodes($items) ) {
	$_SESSION['errors'][] = 'Unable to insert all countries.';
}

elseif( !CommonAppCountries::updateCountryCodes() ) {
	$_SESSION['errors'][] = 'Unable to link country tables.';
}

else {
	$_SESSION['messages'][] = 'Country code file was successfully uploaded.';
}

PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/countries.html' );
