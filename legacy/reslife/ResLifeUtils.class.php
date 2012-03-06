<?php

// require_once('BannerObject.class.php');
// require_once('PSUAddress.class.php');
// require_once('PSUBill.class.php');
// require_once('PSUTools.class.php');

require_once('fpdf/fpdf.php');
require_once('PSUPerson.class.php');
require_once('BannerStudent.class.php');
require_once('reslife/ResLifeSQL.class.php');

/**
 * ResLifeUtils.class.php.
 *
 * Coordinate common data & functionality across all ResLife apps in 1 location
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Betsy Coleman <bscoleman@plymouth.edu>
 */ 

class ResLifeUtils 
{
/**
 *	All the available residence halls on campus includes both the code and the "long" text form
 *	This is suitable for usage in drop down select lists as is.
 */
	protected $oncampushousing = array(
		'BE'=>'Belknap',
		'BL'=>'Blair',
		'HI'=>'Eco House',
		'GR'=>'Grafton',
		'HA'=>'Hall Hall',
		'LW'=>'Langdon Woods',
		'ML'=>'Mary Lyon',
		'NT'=>'Non-Trad Apts',
		'PE'=>'Pemi',
		'SM'=>'Smith',
		'WM'=>'White Mtn Apts',
		'PEND'=>'Pending (admistrative use only)'
	);

	protected $tradhousing = array(
		'BE'=>'Belknap',
		'BL'=>'Blair',
		'HI'=>'Eco House',
		'GR'=>'Grafton',
		'HA'=>'Hall Hall',
		'ML'=>'Mary Lyon',
		'PE'=>'Pemi',
		'SM'=>'Smith',
	);

	protected $tradhousingroomdraw = array(
		''=>'',
		'BE'=>'Belknap',
		'BL'=>'Blair',
		'HI'=>'Eco House',
		'GR'=>'Grafton',
		'HA'=>'Hall Hall',
		'ML'=>'Mary Lyon',
		'PE'=>'Pemi',
		'SM'=>'Smith',
		'DC'=>'Any Hall',
	);

/**
 * Off Campus, anywhere is lumped into this one code
 */
	protected $offcampushousing = array(
		'OC'=>'Off Campus'
	);
		
  protected $allhousing = array();

/**
 * Interest Attributes
 */
	protected $interestattributes = array(
		'BIK'=>'Biking',
		'CS' =>'Community Service',
		'FIT'=>'Fitness',
		'MU' =>'Music / Theatre Arts',
		'OR' =>'Outdoor & Hiking',
		'QUI'=>'Quiet',
		'SKI'=>'Skiing',
		'SM' =>'Smoker',
		'SNO'=>'Snowboarding',
		'WE'=>'Wellness'
	);
		
/**
 * Res North Room Options 
 */

	protected $resnorthroomoptions = array(
		'4P'=>'4-person apartment or suite',
		'LW1'=>'Langdon Woods single',
		'LW2' =>'Langdon Woods double',
		'UA2' =>'University Apartment 2-person apartment',
		'NT'=>'Non-Traditional Student Apartment',
	);
	
	protected $resnorthalloptions = array(
		''=>'',
	);
	
  protected $resnorthchoices = array();

  protected $ressql;

	public function __construct()
	{
		$this->allhousing = array_merge($this->oncampushousing, $this->offcampushousing);
		$this->resnorthchoices = array_merge($this->resnorthalloptions, $this->resnorthroomoptions);
		$this->ressql = new ResLifeSQL();
	}

/*
 * 	Prints to an Avery 5160 label sheet which is a label
 * 	2 5/8" wide by 1" tall, they are 3 across on a page
 * 	and 10 rows per page. (30 per page). The upper left
 * 	corner is label(0,0) The X co-ord goes horizontally
 * 	accross the page and Y goes vertically down the page
 * 	Left/Right page margins are 4.2 MM (1/6 inch)
 * 	Top/Botton page margines are 12.7 MM (.5 inch)
 * 	Horizontal gap between labels is 4.2 MM (1/6 inch)
 * 	There is no vertial gap between labels
 * 	Labels are 66.6 MM (2 5/8") Wide
 * 	Labels are 25.5 MM (1" ) Tall
 * 	The labels are slightly taller than the 25.5mm height so
 * 	to adjust for that, add a multiplier to creep down vertically
 * 	the farther you get down the page.
 *	 
 * 	@param 	x 1,2,3 which label column you are printing
 * 	@param  y 1-10, which label row you are printing
 * 	@param  handle to the pdf file you are printing to
 * 	@param  $string the data you are printing
 * 	@access public
 * 	@return excel file
 */

	protected function Avery5160($x, $y, $pdf, $Data)
	{
		$LeftMargin = 4.2;
		$TopMargin = 12.7;
		$LabelWidth = 66.6;
		$LabelHeight = 25.5;

		// Create Co-Ords of Upper left of the Label
		$AbsX = $LeftMargin + (($LabelWidth + 4.22) * $x);
		$AbsY = $TopMargin + ($LabelHeight * $y);

		// Fudge the Start 3mm inside the label to avoid alignment errors
		$pdf->SetXY($AbsX+3,$AbsY+3+($y*2)); // account for interlabel creep
		$pdf->MultiCell($LabelWidth-8,4.5,$Data);

		return;
	}

/*
 * 	gets data from an array and returns it in excel format
 *	 
 * 	@param 	array $results in the form of rows & columns, walk the rows and inside that the columns, grab the data
 *					and print it out to an excel file
 * 	@param  string $name the name of the file
 * 	@access public
 * 	@return excel file
 */
	public function createExcelFile($results, $name="dummy_", $headings=null)
	{
		$header = '';
		$data = '';
    $name .= date("M_d_Y");

    // create column header information
		//
		// if they want to give their own headers, use those instead...
		// need to be ordered properly!
		if (isset($headings))
		{
		  foreach($headings as $h)
			  $header .= $h."\t";
		}
		else
		{
			foreach($results[0] as $h => $value)
				$header .= strtoupper($h)."\t";
		}

		// populate the date in each of the rows
		foreach ($results as $row)
		{
			$line = '';
			foreach($row as $value)
			{
				if (!isset($value) || $value == ""){ $value = "\t"; }
				else
				{
					$value = trim($value);

					// important to escape any quotes to preserve them in the data.
					$value = str_replace('"', '""', $value);

					// needed to encapsulate data in quotes because some data might be multi line. 
					// the good news is that numbers remain numbers in Excel even though quoted.
					$value = '"' . $value . '"' . "\t";
				}

				$line .= $value;
			}
			$data .= trim($line)."\n";
		}

		// this line is needed because returns embedded in the data have "\r" and this looks like a "box character" in Excel
		$data = str_replace("\r", "", $data);

		// This line will stream the file to the user rather than spray it across the screen
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$name.xls");
		header("Cache-Control: cache, must-revalidate");
		header("Pragma: public");
		header("Expires: 0");
		echo $header."\n".$data;
		exit;
	}

/*
 * take a value, combines it with the salt and encodes it to base64
 * 
 * @param string $value the data we want to pass
 * @param string $salt to help prevent someone from decoding and getting the data
 * @access public
 * @return string encoded
 * 
 */
  function createHash($value, $salt = null)
  {
    $combination = $this->salt . $value;
    return base64_encode($combination);
  }
/*
 * 	gets data from an array and returns it in excel format
 *	 
 * 	@param 	array $data in the form of rows & columns, walk the rows and inside that the columns, grab the data
 *					and print it out to the pdf file
 * 	@param  string $name the name of the file
 * 	@access public
 * 	@return pdffile
 */
	public function create5160LabelFile($results, $name="dummy_")
	{
		$name .= date("M_d_Y") . ".pdf";;

		$fpdf = new FPDF();  
		$fpdf->Open();
		$fpdf->AddPage();
		$fpdf->SetFont('Arial','B',10);
		$fpdf->SetMargins(0,0);
		$fpdf->SetAutoPageBreak(false);

		$x = 0;
		$y = 0;

		// create the array of the ids want to query the database for addresses
		foreach ($results as $labeltext)
		{
			$this->Avery5160($x,$y,$fpdf,$labeltext);

			$y++; // next row
			if ($y == 10 ) 
			{ // end of page wrap to next column
				$x++;
				$y = 0;
				if ($x == 3 ) 
				{ // end of page
					$x = 0;
					$y = 0;
					$fpdf->AddPage();
				}
			}
		}
		echo $fpdf->Output($name, $destination = 'd');
	}

/*
 * 	gets data from an array grabs the mailing information for each of the records and creates mailing labels
 *	 
 * 	@param 	array $results in the form of rows & columns, walk the rows and inside that the columns, grab the data
 *					and print it 
 * 	@param  string $name the name of the file
 * 	@access public
 * 	@return pdffile file
 */
	public function create5160MailingLabelFile($results, $name="dummy_")
	{
		$name .= date("M_d_Y") . ".pdf";;

		$fpdf = new FPDF();  
		$fpdf->Open();
		$fpdf->AddPage();
		$fpdf->SetFont('Arial','B',10);
		$fpdf->SetMargins(0,0);
		$fpdf->SetAutoPageBreak(false);

		$x = 0;
		$y = 0;

		// create the array of the ids want to query the database for addresses
		foreach ($results as $student)
		{
			$st = new PSUPerson($student['spriden_id'], array('address'));

			$LabelText = sprintf("%s\n%s\n%s, %s, %s",
										$student['spriden_last_name'].", ".$student['spriden_first_name']." ".$student['spriden_mi'],
										$st->address['CA'][0]->street_line1,
										$st->address['CA'][0]->city,
										$st->address['CA'][0]->stat_code,
										$st->address['CA'][0]->zip );

			$this->Avery5160($x,$y,$fpdf,$LabelText);

			$y++; // next row
			if ($y == 10 ) 
			{ // end of page wrap to next column
				$x++;
				$y = 0;
				if ($x == 3 ) 
				{ // end of page
					$x = 0;
					$y = 0;
					$fpdf->AddPage();
				}
			}
			$st->destroy();
		}
		echo $fpdf->Output($name, $destination = 'd');
	}

/**
 * take a base64 encoded value and unencodes it
 *  
 * @param string $hash the encoded data
 * @param string $salt so we can separate it from the value we want
 * @access public
 * @return string encoded
 * 
 **/
  function decodeHash($hash, $salt=null)
  {
    $exposed = base64_decode($hash);

    $salt_length = strlen($this->salt);
    $retrieved_salt = substr($exposed, 0, $salt_length);
    if($retrieved_salt == $this->salt)
    {
      return substr($exposed,$salt_length);
    }
    else
    {
      return null;
    }
  }
/**
 * 	Return the list of all housing options that the student may select from.  Currently this list
 * 	contains both oncampus options and offcampus as a single selection.
 *	  
 * 	@access public
 * 	@param  none
 * 	@return housing options array
 */
	public function getAllHousingOptions()
	{
		return $this->allhousing;
	}

/**
 * 	Return the list of all housing room options for Res North that the student may select from.  
 *	  
 * 	@access public
 * 	@param  none
 * 	@return housing options array
 */
	public function getAllResNorthChoices()
	{
		return $this->resnorthchoices;
	}

/**
 *	Return the column headings for the HDepo Name/Date reports
 *  
 * 	@access public
 * 	@param  pdf - true, excel - false
 * 	@return array
 */
	public function getHeaderAttributesHDepo($pdf=true)
	{
		return array('Deposit Date', 'Release Date', 'Term', 'ID', 'Last Name', 'First Name',
								 'Middle', 'Home Phone', 'Attribute Description');
	}

/**
 *  Return the column headings for the Birthday Bed Reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderBirthday($pdf=true)
	{
	  if ($pdf)
      return array('ID', 'Full Name',  'Building', 'Room/Apt', 'Birthdate');
		else
      return array('Student ID', 'Last Name', 'First Name', 'Middle', 'Building', 'Room/Apt', 'Birthdate');
	}

/**
 *  Return the column headings for the Blank Page Reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderBlank($pdf=true)
	{
	  if ($pdf)
      return array('Bldg', 'Room', 'Cap/Max', 'Sex', 'Rate', '','Full Name');
		else
      return array('Bldg', 'Room', 'Cap/Max', 'Sex', 'Rate', '','Last Name', 'First Name', 'Middle');
	}

/**
 *  Return the column headings for the Checkin reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderCheckin($pdf=true)
	{
	  if ($pdf)
			return array('Full Name', 'ID', 'Hall/Area', 'Room/Apt', 'Notes / Comments');
		else
			return array('Last Name', 'First Name', 'Middle', 'ID', 'Hall/Area', 'Room/Apt', 'Notes / Comments');
	}

/**
 *	Return the column headings for the Email Reports
 *	  
 *	@access	public
 *	@param  pdf - true, excel - false
 *	@return	array
 */
	public function getHeaderEmail($pdf=true)
	{
	  if ($pdf)
      return array('ID', 'Full Name',  'Building', 'Room/Apt', 'PSU Email Address');
		else
			return array('Student ID', 'Last Name', 'First Name',  'Middle', 'Building', 'Room/Apt', 'PSU Email Address');
	}

/**
 *  Return the column headings for the IdLinkdedDB Name/Date reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderIdLinkedDB($pdf=true)
	{
		return array('First Name', 'Middle Name', 'Last Name', 'ID', 'Issue Number', 'Issue Date', 'Update Date');
	}

/*
 *  Return the column headings for the HDepo Gender reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
*/
	public function getHeaderHDepoByGender($pdf=true)
	{
		return array('Term', 'ID', 'Last Name', 'First Name', 'Middle', 'Depo Date',
								 'Release Date', 'Amt', 'Building', 'Room', 'Status', 'Birthdate', 'Smoker', 'Gender');
	}

/*
 *  Return the column headings for the HDepo Name/Date reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
*/
	public function getHeaderHDepoByNameDate($pdf=true)
	{
		return array('Term','ID','Last Name','First Name','Middle', 'Depo Date', 
								 'Release Date','Amt','Building','Room','Status','Birthdate','Smoker');
	}

/*
 *  Return the column headings for the Lockout Bed Reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
*/
	public function getHeaderLockout($pdf=true)
	{
	  if ($pdf)
      return array('ID', 'Full Name',  'Building', 'Room/Apt');
		else
      return array('Student ID', 'Last Name', 'First Name', 'Middle', 'Building', 'Room/Apt');
	}

/*
 *	Return the column headings for the MissingLeasesOrRD Reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
*/
	public function getHeaderMissingLeasesOrRD($pdf=true)
	{
	  if ($pdf)
      return array('ID', 'Name', 'Phone', 'ARTP DESC', 'BLDG', 'Room Num', 'RRCD', 'Term', 'Deposit Eff Date');
		else
		  return array('Student ID', 'Name', 'Phone', 'ARTP DESC', 'BLDG', 'Room Num', 'RRCD', 'Term', 'Deposit Eff Date');
	}

/*
 *  Return the column headings for the Name/Room Order Reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderNameRoomOrder($pdf=true)
	{
	  if ($pdf)
			return array('Term','Building','Room/Apt','Capacity','Full Name','ID','Meal Plan','Rate','Gender','Birthdate');
		else
			return array('Term','Building','Room/Apt','Capacity','Last Name','First Name','Middle','ID','Meal Plan','Rate','Gender','Birthdate');
	}

/*
 *  Return the column headings for the Open Bed Reports
 *  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderOpenBed($pdf=true)
	{
	  if ($pdf)
      return array('Term', 'Bldg', 'Room', 'Cap/Max', 'Sex', 'Rate', '','ID', 'Birthdate','Meal Plan',
                   'Smoker','Full Name');
		else
      return array('Term', 'Bldg', 'Room', 'Cap/Max', 'Sex', 'Rate', '','ID', 'Birthdate','Meal Plan',
                   'Smoker','Last Name', 'First Name');
	}

/*
 *  Return the column headings for the Orientation reports
 *  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderOrientationRoster($pdf=true)
	{
		return array('Last Name', 'First Name', 'Middle', 'ID', 'Depo Date', 'Description', 'Amt',
								 'Paperwork', 'Gender', 'Meal Plan', 'City', 'State');
	}

/*
 *  Return the column headings for the PT or Low Credit reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderPTorLowCredit($pdf=true)
	{
	  if ($pdf)
			return array('ID', 'First Name', 'Middle', 'Last Name', 'Building', 'Room', 'FT/PT', 'Credits');
		else
			return array('Student ID', 'First Name', 'Middle', 'Last Name', 'Building', 'Room', 'FT/PT', 'Credits');
	}

/*
 *  Return the column headings for the Prompted Attributes reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderPromptedAttributesByHDepo($pdf=true)
	{
		return array('Deposit Date', 'Release Date','Deposit Term', 'ID','Last Name', 'First Name', 'Middle',
								 'Home Phone','Attribute Description');

	}

/*
 *  Return the column headings for the Winter Summer reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array
 */
	public function getHeaderWinterSummer($pdf=true)
	{
	  if ($pdf)
			return array('Term', 'Building', 'Room/Apt', 'Capacity', 'Full Name', 'ID', 'Rate', 'Gender', 'Arrival', 'Departure');
		else
			return array('Term', 'Building', 'Room/Apt', 'Capacity', 'Last Name', 'First Name','Middle','ID', 'Rate', 'Gender', 'Arrival', 'Departure');
	}

/*
 *  Return the column headings for the Withdrawn or Revoked reports
 *	  
 *  @access	public
 *  @param  pdf - true, excel - false
 *  @return	array 
 */
	public function getHeaderWithdrawnRevoked($pdf=true)
	{
	  if ($pdf)
			return array('ID', 'Term', 'Last Name', 'First Name', 'Middle', 'Status', 'Date', 'Admit Code',
									 'Room Status', 'Building', 'Room', 'MP Status', 'Mealplan');
		else
			return array('Student ID', 'Term', 'Last Name', 'First Name', 'Middle', 'Status', 'Date', 'Admit Code',
									 'Room Status', 'Building', 'Room', 'MP Status', 'Mealplan');
	}

/**
 * 	Return the list of tracked interests in the database
 *	  
 * 	@access public
 * 	@param  none
 * 	@return interest array
 */
	public function getInterests()
	{
		return $this->interestattributes;
	}

/*
 *  Return the list of all off campus housing options that the student may select from.  
 *	  
 *  @access	public
 *  @param  none
 *  @return	housing options array
 */
	public function getOffCampusHousingOptions()
	{
		return $this->offcampushousing;
	}

/*
 *  Return the list of all on campus housing options that the student may select from.  
 *	  
 *  @access	public
 *  @param  none
 *	@return	housing options array
 */
	public function getOnCampusHousingOptions()
	{
		return $this->oncampushousing;
	}

/*
 *  Return the list of traditional halls on campus that the student may select from.  
 *	  
 *  @access	public
 *  @param  none
 *	@return	housing options array
 */
	public function getTradHallOptions()
	{
		return $this->tradhousing;
	}

/*
 *  Return the list of traditional halls on campus that the student may select from for Housing Selection.  
 *	  
 *  @access	public
 *  @param  none
 *	@return	housing options array
 */
	public function getTradHallRoomDrawOptions()
	{
		return $this->tradhousingroomdraw;
	}

/*
 * goes through array of people and gets their username and creates a base64 encoded pidm
 * 
 * @param array $people
 * @access public
 * @return array of people
 *
 */
  function setUpPeopleList($people)
  {
    foreach($people as $p)
    {
      $person = new PSUPerson($p['r_pidm'], array('address'));

      $p['username'] = $person->username;
      $p['city'] = $person->address['MA'][0]->city;
      $p['state'] = $person->address['MA'][0]->stat_code;
      $p['birthdate'] = date("m/d/Y", $person->birth_date);
      $p['hash'] = $this->createHash($p['r_pidm']);
      $peeps[] = $p;

			$person->destroy();
    }

    return $peeps;
  }

// NEW FROM RD MENU TREE>>>-------------------------------------------------------------------------------


/**
 * Get a list of all housing applications that a student may currently apply for
 *
 * @access public
 * @return array of year/term available for this application type
 *
 */
  function housingApplicationOptions($student_type, $admin=null)
  {
    $applicationoptions= $this->ressql->getHousingApplicationOptions($student_type, $admin);

		if (isset($admin))
			return $applicationoptions;

    $apparray = array();

		foreach ($applicationoptions as $opt)
		{
				$link = 
					'<a href=' . $GLOBALS['BASE_URL'] . 'housingapp/index.html?student_type='. $student_type .'&amp;area='. $opt['app_area'] .'&amp;term='. $opt['year_term']. 
					'>' .  $this->getSemName($opt['year_term']) . ' Housing Application';
				if ($student_type == 'RE')
					$link .= ($opt['app_area'] == 'TR') ? ' - Traditional Halls' : ' - ResNorth';

				$link .= '</a>';

				$apparray[]= $link;
		}

    return $apparray;
  }

/**
 * Get a list of all housing applications that a student may currently apply for
 *
 * @access public
 * @return array of year/term available for this application type
 *
 */
	function getHousingApplicationOption($id=-1) 
	{
    // if no id is given, will return the "default"
    return $this->ressql->getHousingApplicationOption($id);
	}

/**
 * combines current, past, and future termcodes with labels for use in a select box
 *
 * @access public
 * @return array of termcodes
 *
 */
  function termSelectBuilder()
  {
    if( !isset($GLOBALS['BannerStudent']) ){
      require_once('BannerStudent.class.php');
      $GLOBALS['BannerStudent'] = new BannerStudent( PSU::db('banner') );
    }

    $ugterm = $GLOBALS['BannerStudent']->reslifeCurrentTerm('UG');
    $gterm = $GLOBALS['BannerStudent']->reslifeCurrentTerm('GR');

    $label = $this->getSemName($ugterm);

    $currentArray[$ugterm."/".$gterm] = 'Current: '.$label;

    $past = $this->oldTerms();
    $future = $this->nextTerms();
    return array_merge($currentArray, $future, $past);
  }

/**
 * creates appropriate label for terms based on the term code
 *
 * @param string $ugterm undergraduate term code
 * @access public
 * @return string of label for term
 *
 */
  function getSemName($ugterm)
  {
    $year = substr($ugterm, 0, 4);
    $code = substr($ugterm, 4,2);
    switch($code)
    {
      case '10':
        $name = 'Fall '.($year -1);
        break;
      case '20':
        $name = 'Winterim '.$year;
        break;
      case '30':
        $name = 'Spring '.$year;
        break;
      case '40':
        $name = 'Summer '.$year;
        break;
    }
    return $name;
  }


/**
 * gets future termcodes for both Graduate and Undergraduate
 *
 * @access public
 * @return array of termcodes and labels
 *
 */
  function nextTerms()
  {
    $sql;
    $usql;
    $this->ressql->getSQLNextTerms(&$sql, &$usql);

    $rs = PSU::db('banner')->Execute($sql);
    $gterms = $rs->GetRows();

    $urs = PSU::db('banner')->Execute($usql);
    $uterms = $urs->GetRows();

    $futureTerms[$uterms[0]['stvterm_code']."/".$gterms[0]['stvterm_code']] = "Future: ".$this->getSemName($uterms[0]['stvterm_code']);
    $futureTerms[$uterms[1]['stvterm_code']."/".$gterms[1]['stvterm_code']] = "Future: ".$this->getSemName($uterms[1]['stvterm_code']);

    return $futureTerms;
  }

/**
 * gets past termcodes for both Graduate and Undergraduate
 *
 * @access public
 * @return array of termcodes and labels
 *
 */
  function oldTerms()
  {
    $sql;
    $usq;
    $this->ressql->getSQLOldTerms(&$sql, &$usql);

    $rs = PSU::db('banner')->Execute($sql);
    $terms = $rs->GetRows();
    $trms = $terms;

    $urs = PSU::db('banner')->Execute($usql);
    $uterms = $urs->GetRows();
    $utrms = $uterms;

    $termArray = array();
    foreach ($trms as $g)
    {
      foreach ($utrms as $u)
      {
        if($g['stvterm_housing_start_date'] == $u['stvterm_housing_start_date'])
        {

          $label = 'Past: '.$this->getSemName($u['stvterm_code']);

          $termArray[$u['stvterm_code']."/".$g['stvterm_code']]= $label;
        }
      }
    }

    return $termArray;
  }

 /**
 * separates undergrad and grad terms into a terms array
 *
 * @access public
 * @return array of termcodes
 *
 */
   function termSeparator($terms)
   {
		 $ex = explode('/', $terms);
		 $uterm = $ex[0];
		 $gterm = $ex[1];
		 $terms = array();
		 $terms['ug']=$uterm;
		 $terms['g']=$gterm;
		 return $terms;
	 }

/*
 * sets up phone number in human readable format
 *
 * @param string $number a phone number
 * @param string $separator a character to separate the phone number with
 * @access public
 * @return string
 *
 */
  function formatPhoneNumber($number, $separator)
  {
    if(strlen($number) == 7)
    {
      $pre = substr($number, 0, 3);
      $ext = substr($number, 3, 4);
      $phone_number = $pre.$separator.$ext;

      return $phone_number;
    }
    else
    {
      return $number;
    }
  }
	function formatStudentData($student_data)
	{
		//check for y in confidential flag and add a class to make it stand out
		if($student_data['confident'] == 'Y')
		{
			$student_data['true_conf'] = "class='true_conf'";
		}
		else
		{
			$student_data['true_conf'] = null;
		}

		//only puts comma in if the city is present in the addresses
		if(!isset($student_data['lo_city']))
		{
			$student_data['lo_comma'] = '';
		}
		else
		{
			$student_data['lo_comma'] = ',';
		}

		if(!isset($student_data['ma_city']))
		{
			$student_data['ma_comma'] = '';
		}
		else
		{
			$student_data['ma_comma'] = ',';
		}

		if(!isset($student_data['ca_city']))
		{
			$student_data['ca_comma'] = '';
		}
		else
		{
			$student_data['ca_comma'] = ',';
		}

		$student_data['dob'] = date('F j, Y', strtotime($student_data['dob']));

		$student_data['ma_phone_number'] = $this->formatPhoneNumber($student_data['ma_phone_number'], '-');

	return $student_data;
	}
}

