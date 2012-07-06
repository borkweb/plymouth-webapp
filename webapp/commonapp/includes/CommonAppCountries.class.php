<?php

/*
 * CommonAppCountries.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  02-APR-2009  [lmo]  original<br/>
 *
 */

/**
 * CommonAppCountries.class.php
 *
 * @version		1.0.0
 * @module		CommonAppCountries.class.php
 * @author		Laurianne Olcott <max@mail.plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 
class CommonAppCountries extends BannerGeneral
{
 /**
  * first clear out the old data and start fresh
  */
	public static function deleteOldCountryCodes()
	{
		$query="TRUNCATE TABLE country_codes";
		return PSU::db('banner')->Execute($query);
	}// end deleteOldCountryCodes

 /**
  * now retieve the file and insert the records
  */
	public static function insertCountryCodes($items)
	{
		$query = "INSERT INTO country_codes (country, coa_code) VALUES (:country, :coa_code)";

		foreach( $items as $item )
		{
			list(, $country, $coa_code) = $item;

			$country = trim($country);
			$coa_code = trim($coa_code);

			// skip empty records
			if( empty($country) || empty($coa_code) )
			{
				continue;
			}

			$result = PSU::db('banner')->Execute($query, compact('country', 'coa_code'));

			if( !$result ) {
				return false;
			}
		}//end for

		return true;
	}//end insertCountryCodes

 /**
  * update the table from stvnatn to get the proper Banner nation codes for saraddr
  */
	public static function updateCountryCodes()
	{
		$query="UPDATE country_codes
							SET nation_code = (SELECT rtrim(a.stvnatn_code) 
																	 FROM stvnatn a
																	WHERE rtrim(upper(a.stvnatn_nation))=rtrim(upper(country))
																	  AND a.stvnatn_activity_date=(select max(b.stvnatn_activity_date)
																		                             from stvnatn b
																																 where b.stvnatn_code=a.stvnatn_code)
																		AND ROWNUM=1)";
		return PSU::db('banner')->Execute($query);	
	}//end updateCountryCodes

 /**
  * Return a count of the countries that were matched.
  */
	public static function countMatchedCountries()
	{
		$query = "SELECT COUNT(*) FROM country_codes
						WHERE nation_code is not null";
		return PSU::db('banner')->GetOne($query);
	}// end checkSuccesses

 /**
  * check for duplicate nations in stvnatn and bring back a report
  */
	public static function getDuplicates()
	{
		// returns a report of the resulting table country_codes
		$query="SELECT a.stvnatn_code,
									 a.stvnatn_nation,
									 a.stvnatn_capital,
									 a.stvnatn_scod_code_iso,
									 a.stvnatn_activity_date
							FROM stvnatn a
						 WHERE a.stvnatn_nation IN
									(SELECT b.stvnatn_nation
									 FROM stvnatn b
									 GROUP BY b.stvnatn_nation HAVING COUNT(*) > 1)
						 ORDER BY a.stvnatn_nation,
											a.stvnatn_code,
											a.stvnatn_activity_date DESC";
		$duplicates=PSU::db('banner')->GetAll($query);
		return $duplicates;
	}// end getDuplicates

 /**
  * check the failures and bring back a report
  */
	public static function checkFailures()
	{
		// returns a report of the resulting table country_codes
		$query="SELECT COUNT(*) FROM country_codes
						WHERE nation_code is null";
		$failures=PSU::db('banner')->GetOne($query);
		return $failures;
	}// end checkFailures

 /**
  * list the failures in an array for display
  */
	public static function getFailedCountries()
	{
		// returns array of missing nation codes
		$data=array();
		$query="SELECT country,
		               coa_code
						 FROM country_codes
						WHERE nation_code is null";
		$data = PSU::db('banner')->GetAll($query);
		return $data;
	}// end listFailures
}//end CommonAppCountries
