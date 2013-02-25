<?php
require_once('PSUTools.class.php');
require('fpdf/fpdf.php');
set_time_limit(180);
//$GLOBALS['BANNER']->debug = true;

/**
 * FinaidExitLoansPDF.class.php
 *
 * Finaid PDF Object
 *
 * &copy; 2010 Plymouth State University ITS
 *
 * @author		Laurianne Olcott <max@plymouth.edu>
 */

class FinaidExitLoansPDF extends FPDF
{
	function Header()
	{
		$this->SetFont('Times','B',12);
	}
	var $NewPageGroup;   // variable indicating whether a new group was requested
	var $PageGroups;     // variable containing the number of pages of the groups
	var $CurrPageGroup;  // variable containing the alias of the current page group
	var $type="";				 // variable to define type of page (report or letter) 

	function __construct()
	{
		$this->ExitLoansImages='/web/pscpages/webapp/templates/images/exit-loans';
		parent::__construct();
	}

	// create a new page group; call this before calling AddPage()
	function StartPageGroup()
	{
			$this->NewPageGroup = true;
	}

	// current page in the group
	function GroupPageNo()
	{
			return $this->PageGroups[$this->CurrPageGroup];
	}

	// alias of the current page group -- will be replaced by the total number of pages in this group
	function PageGroupAlias()
	{
			return $this->CurrPageGroup;
	}

	function _beginpage($orientation)
	{
			parent::_beginpage($orientation);
			if($this->NewPageGroup)
			{
					// start a new group
					$n = sizeof($this->PageGroups)+1;
					$alias = "{nb$n}";
					$this->PageGroups[$alias] = 1;
					$this->CurrPageGroup = $alias;
					$this->NewPageGroup = false;
			}
			elseif($this->CurrPageGroup)
					$this->PageGroups[$this->CurrPageGroup]++;
	}

	function _putpages()
	{
			$nb = $this->page;
			if (!empty($this->PageGroups))
			{
					// do page number replacement
					foreach ($this->PageGroups as $k => $v)
					{
							for ($n = 1; $n <= $nb; $n++)
							{
									$this->pages[$n] = str_replace($k, $v, $this->pages[$n]);
							}
					}
			}
			parent::_putpages();
	}

	function getCurrAcadYear()
	{
		$year = date("Y");
		$month = date("M");
		switch($month)
		{
			case 'Jan':
			case 'Feb':
			case 'Mar':
			case 'Apr':
			case 'May':
			case 'Jun':
				$curr_acad_year=$year;
				break;
			case 'Jul':
			case 'Aug':
			case 'Sep':
			case 'Oct':
			case 'Nov':
			case 'Dec':
				$curr_acad_year=($year+1);
				break;
		}//end switch
		return $curr_acad_year;
	}

	function setBorderTop()
	{
		$this->SetFillColor(0,0,0);
		$fill=true;
		$this->Cell(193.9,.5," ",0,1,'L',$fill);

		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(192.1,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->ln();

		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(192.1,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->ln();
	}

	function setBorderBottom()
	{
		$this->SetFillColor(0,0,0);
		$fill=true;
		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(192.1,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->ln();

		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(192.1,.2," ",0,0,'L');
		$this->Cell(.2,.2," ",0,0,'L',$fill);
		$this->Cell(.2,.2," ",0,0,'L');
		$this->Cell(.5,.2," ",0,0,'L',$fill);
		$this->ln();

		$this->Cell(193.9,.5," ",0,1,'L',$fill);
	}

	function setBorderLeft()
	{
		$this->SetFillColor(0,0,0);
		$fill=true;
		$this->Cell(.5,5," ",0,0,'L',$fill);
		$this->Cell(.2,5," ",0,0,'L');
		$this->Cell(.2,5," ",0,0,'L',$fill);
	}

	function setBorderRight()
	{
		$this->SetFillColor(0,0,0);
		$fill=true;
		$this->Cell(.2,5," ",0,0,'L',$fill);
		$this->Cell(.2,5," ",0,0,'L');
		$this->Cell(.5,5," ",0,0,'L',$fill);
	}

	function setSideBorders($text,$alignment)
	{
		$this->setBorderLeft();
		$this->Cell(192.1,5,$text,0,0,$alignment);
		$this->setBorderRight();
		$this->ln();
	}

	function setSideBordersReverse($text,$alignment)
	{
		$this->setFillColor(0,0,0);
		$fill=true;
		$this->setTextColor(255,255,255);
		$this->setBorderLeft();
		$this->Cell(.5,5," ",0,0,'L');
		$this->Cell(191.1,5,$text,0,0,$alignment,$fill);
		$this->Cell(.5,5," ",0,0,'L');
		$this->setBorderRight();
		$this->ln();
	}

	function grandTotalReverse($text,$amount,$text2,$amount2)
	{
		$this->setFillColor(0,0,0);
		$fill=true;
		$this->setTextColor(255,255,255);
		$this->setBorderLeft();
		$this->Cell(.5,5," ",0,0,'L');
		$this->SetFont('Times','BI',9);
		$this->Cell(71.6,5,$text,0,0,'L',$fill);
		$this->SetFont('Times','B',9);
		$this->Cell(24,5,$this->pdf_money_format($amount),0,0,'L',$fill);
		$this->Cell(71.5,5,$text2,0,0,'L',$fill);
		$this->SetFont('Times','B',9);
		$this->Cell(24,5,$this->pdf_money_format($amount2),0,0,'L',$fill);
		$this->Cell(.5,5," ",0,0,'L');
		$this->setBorderRight();
		$this->ln();
	}


	function setTableHead($addon)
	{
		$this->setTextColor(0,0,0);
		$this->setBorderLeft();
		$this->SetFont('Times','B',10);
		$this->Cell(20,5,"",0,0,'C');
		$this->Cell(35,5,"".$addon,0,0,'L');
		$this->Cell(32.1,5,"Amount",0,0,'R');
		$this->Cell(30,5,"Borrowed",0,0,'R');
		$this->Cell(25,5,"",0,0,'C');
		$this->Cell(15,5,"",0,0,'C');
		$this->Cell(35,5,"",0,0,'C');
		$this->SetFont('Times','',10);
		$this->setBorderRight();
		$this->ln();

		$this->setBorderLeft();
		$this->SetFont('Times','BU',10);
		$this->Cell(20,5,"Loan Period",0,0,'C');
		$this->Cell(35,5,"Loan Type".$addon,0,0,'L');
		$this->Cell(32.1,5,"Borrowed",0,0,'R');
		$this->Cell(30,5,"+ Interest",0,0,'R');
		$this->Cell(25,5,"Interest Rate",0,0,'C');
		$this->Cell(15,5,"Term",0,0,'C');
		$this->Cell(35,5," Est. Monthly Pymts",0,0,'C');
		$this->SetFont('Times','',10);
		$this->setBorderRight();
		$this->ln();
	}

	function calculateInterestRate($rate, $amount)
	{
		$rate=$rate/100;
	  $Z  =  1  /  (1  +  ($rate/12)); 
		$monthly_pymt=((1  -  $Z)  *  $amount)  /  ($Z  *  (1  -  pow($Z,120)));     
		return round($monthly_pymt, 2);
	}
	
	function printTableDetails($loan_period, $loan_type, $tot_stmt, $amount, $sastrisk, $interest, $dastrisk, $term, $pymts, $tastrisk)
	{
		$this->setBorderLeft();
		$this->Cell(20,5,$loan_period,0,0,'C');
		$this->Cell(35,5,$loan_type,0,0,'L');
		$this->Cell(32.1,5,$tot_stmt,0,0,'R');
		$this->Cell(30,5,$this->pdf_money_format($amount).$sastrisk,0,0,'R');
		$this->Cell(25,5,$interest."%".$dastrisk,0,0,'C');
		$this->Cell(15,5,$term,0,0,'C');
		if($tastrisk=="***" || $tastrisk=="****")
		{
			$this->Cell(35,5,$this->pdf_money_format($pymts).$tastrisk,0,0,'C');
		}
		else
		{
			$this->Cell(30,5,$this->pdf_money_format($pymts),0,0,'C');
			$this->Cell(5,5,"  ",0,0,'C');
		}
		$this->setBorderRight();
		$this->ln();
	}

	function printSectionSummary($total_amount_borrowed,$total_monthly_paymts,$sastrisk)
	{
		if($total_amount_borrowed==0)
		{
			$total_monthly_paymts=0;
		}
		$this->setBorderLeft();
		$this->Cell(48,5,"",0,0,'C');
		$this->SetFont('Times','B',10);
		$this->Cell(31.1,5,"Cumulative Amount Borrowed:",0,0,'L');
		$this->Cell(38,5,$this->pdf_money_format($total_amount_borrowed).$sastrisk,0,0,'R');
		$this->Cell(40,5,"",0,0,'C');
		$this->Cell(30,5,$this->pdf_money_format($total_monthly_paymts).'  ',0,0,'C');
		$this->Cell(5,5,"  ",0,0,'C');
		$this->setBorderRight();
		$this->ln();
	}

	function pdf_money_format($string, $places= 2)
	{
		$env = localeconv();
		if($env['int_curr_symbol'] != 'USD')
						setlocale(LC_MONETARY, 'en_US');
		return money_format("%.{$places}n", $string);
	}

	
	function letterBody($first_name,$last_name,$street1,$street2,$street3,$city,$state,$zip,$id)
	{
		$this->type="Letter";
    //Logo
		$this->ln(5);
    $this->Image($this->ExitLoansImages.'/PSUlogo_bw.jpg',70,15,0,0);
		$this->ln(35);
		$this->Cell(130,5,$first_name." ".$last_name,0,1,'L');
		$this->Cell(130,5,$street1." ".$street2." ".$street3,0,1,'L');
		if($city !="")
		{
			$this->Cell(130,5,$city.", ".$state." ".$zip,0,1,'L');
		}
		$this->SetFont('Times','',10);
		$this->ln(5);
		$this->Cell(140,5,"Dear ".$first_name.":",0,0,'L');		
		$this->Cell(40,5,"ID: ".$id,0,1,'R');		
		$this->ln(5);

		$this->Cell(130,5,"          It".chr(39)."s time to start thinking about repaying the educational loans you borrowed while at Plymouth State University.  As you ",0,1,'L'); 
		$this->Cell(130,5,"may know, responsible and timely repayment of your student loan obligation will assist you in building your personal credit history. ",0,1,'L');  
		$this->Cell(130,5,"By repaying your loan, you also assure that loan programs remain available to other students as they begin their pursuit of higher",0,1,'L');  
		$this->Cell(130,5,"education.",0,1,'L');
		$this->ln(5);

		$this->Cell(130,5,"          Your first loan payment(s) will be due approximately six months after your graduation date (this may vary depending on your",0,1,'L');  
		$this->Cell(130,5,"lender and loan type).  You will receive a repayment disclosure from your loan servicer(s) 45 to 60 days prior to your first bill, along with ",0,1,'L');  
		$this->Cell(130,5,"payment information.  At that time, if you have any questions or concerns you will want to contact your lender(s) directly for more ",0,1,'L');  
		$this->Cell(130,5,"information.",0,1,'L');   
		$this->ln(5);

		$this->Cell(130,5,"          To ensure you receive timely payment information and statements from your lender(s), please be sure they have your most current",0,1,'L'); 
		$this->Cell(130,5,"address.  We also recommend that you contact your lender to discuss the various repayment options that may be available.  Note, you",0,1,'L'); 
		$this->Cell(130,5,"may be eligible for an interest rate reduction if you set-up automatic payments from a deposit account.   If you are planning on",0,1,'L'); 
		$this->Cell(130,5,"attending graduate school or entering the military, please contact your lender to discuss deferment options.  ",0,1,'L');  
		$this->ln(5);

		$this->Cell(130,5,"          Your educational loans processed through Plymouth State University are detailed on the next page. It is important to ",0,1,'L'); 
		$this->Cell(36,5,"know that these amounts ",0,0,'L'); 
		$this->SetFont('Times','B',10);
		$this->Cell(72,5,"do not reflect any accrued interest or payments ",'B',0,'L');
		$this->SetFont('Times','',10);
		$this->Cell(30,5,"made to your lender.  Also your original lender(s) may",0,1,'L');
		$this->Cell(130,5,"have sold some, or all, of your loans.  Information about your federal loans, including the lender and servicer contact information, can be",0,1,'L');
		$this->Cell(23,5,"found using the ",0,0,'L'); 
		$this->SetFont('Times','B',10);
		$this->Cell(81,5,"National Student Loan Database System (NSLDS) at",0,0,'L');
		$this->Cell(40,5,"http://www.nslds.ed.gov.",0,1,'L');
		$this->SetFont('Times','',10);
		$this->ln(4);

		$this->Cell(130,5,"Additional information regarding federal and private loans can be found on your credit report which an be obtained at ",0,1,'L'); 
		$this->Cell(130,5,"no charge once a year at http://www.annualcreditreport.com.",0,1,'L'); 
		$this->ln(3);

		$this->SetFont('Times','I',10);
		$this->Cell(25,5,"Important Links",'B',1,'L');
		$this->ln(2); 
		$this->SetFont('Times','B',10);
		$this->Cell(70,5,"NSLDS & Exit Counseling",0,0,'L');
		$this->Cell(80,5,"http://www.nslds.ed.gov/",0,1,'L');
		$this->SetFont('Times','',10);
		$this->Cell(70,5,"\$ALT Program",0,0,'L');
		$this->Cell(80,5,"http://www.saltmoney.org",0,1,'L');
		$this->Cell(70,5,"Repayment Information",0,0,'L');
		$this->Cell(80,5,"http://studentaid.ed.gov/PORTALSWebApp/students/english/repaying.jsp",0,1,'L');
		$this->Cell(70,5,"Public Service Loan Forgiveness",0,0,'L');
		$this->Cell(80,5,"http://studentaid.ed.gov/PORTALSWebApp/students/english/PSF.jsp",0,1,'L');
		$this->Cell(70,5,"Credit Report",0,0,'L');
		$this->Cell(80,5,"https://www.annualcreditreport.com/cra/index.jsp",0,1,'L');
		$this->ln(10);

		$this->SetFont('Times','B',12);
		$this->Cell(200,5,"The PSU Financial Aid Team wishes you the best in your future endeavors.",0,1,'C'); 
		$this->ln(3); 
		$this->SetFont('Times','B',10);
		$this->Cell(200,5,"PSU Financial Aid Team, 17 High Street, MSC #18, Plymouth, NH 03264, Tel: 603-535-2338, Fax: 603-535-2627",0,1,'C'); 
	}
	
	function loanSummary($id,$first_name,$last_name)
	{
		if($this->GroupPageNo()==1)
		{
			$this->type="Public";
		}
		else
		{
			$this->type="Report";
		}
		$line=0;
		$this->SetTopMargin(1);
		$line++;
		$this->setBorderTop();
		$line++;

		$this->setSideBorders("ID: ".$id.", ".$first_name." ".$last_name,'L');
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		$this->setSideBorders("PLYMOUTH STATE UNIVERSITY",'C');
		$line++;
		$this->setSideBorders("EDUCATIONAL LOAN SUMMARY",'C');
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setSideBordersReverse("FEDERAL STAFFORD LOAN (STUDENT)",'L'); 
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setTableHead(" (Subsidized)");
		$line++;
		$line++;

		$this->SetFont('Times','',10);

		$grand_total=0;
		$grand_pymt_total=0;
		// getting Stafford loan information...
		$fund_code_key1='STFS%';
		$fund_code_key2='DLS%';
		$recs=BannerExitLoans::srSaluteStaff($fund_code_key1,$fund_code_key2,$id);
		$total_amount_borrowed=0;
		$total_monthly_paymts=0;
		$tflag='F';
		foreach($recs as $rval)
		{
			$century=substr(date("Y"),0,2);
			$loan_period=$century.substr($rval['aid_year_key'],0,2)."-".$century.substr($rval['aid_year_key'],2,2);
			if($rval['aid_year_key']>"0809")
			{
				$loan_type="Federal Direct Student Loan";
			}
			else
			{
				$loan_type="Federal Stafford Loan";
			}
			$amount=$rval['amount_borrowed'];
			$tot_stmt=$this->pdf_money_format($amount);
			$total_amount_borrowed=$total_amount_borrowed+$amount;
			$interest=$rval['interest_rate'];
			$term=$rval['term'];
			$pymts=$this->calculateInterestRate($interest, $amount);
			$total_monthly_paymts=$total_monthly_paymts+$pymts;
			if($pymts < 50)
			{
				$tastrisk="***";
				$tflag='T';
			}
			else
			{
				$tastrisk="";
			}
			$this->printTableDetails($loan_period, $loan_type, $tot_stmt, $amount, "", $interest,"**", $term, $pymts, $tastrisk);
			$line++;
		}
		$grand_total=$grand_total+$total_amount_borrowed;
		$grand_pymt_total=$grand_pymt_total+$total_monthly_paymts;
		$this->printSectionSummary($total_amount_borrowed,$total_monthly_paymts,"");
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setTableHead(" (Unsubsidized)*");
		$line++;
		$line++;

		$this->SetFont('Times','',10);

		// getting Stafford loan information...
		$fund_code_key1='STFU%';
		$fund_code_key2='DLU%';
		$recs=BannerExitLoans::srSaluteStaff($fund_code_key1,$fund_code_key2,$id);
		$total_amount_borrowed=0;
		$total_monthly_paymts=0;
		foreach($recs as $rval)
		{
			$century=substr(date("Y"),0,2);
			$loan_period=$century.substr($rval['aid_year_key'],0,2)."-".$century.substr($rval['aid_year_key'],2,2);
			if($rval['aid_year_key']>"0809")
			{
				$loan_type="Federal Direct Student Loan";
			}
			else
			{
				$loan_type="Federal Stafford Loan";
			}
			$amount=$rval['amount_borrowed'];
			$tot_stmt=$this->pdf_money_format($amount);
			$aidyear=$rval['aid_year_key'];
			$acyr_code=BannerExitLoans::getAcyrCode($aidyear);
			$interest=$rval['interest_rate'];
			$curr_acad_year=$this->getCurrAcadYear();
			$numb_years=($curr_acad_year-$acyr_code)+1.5;
			$term=$rval['term'];
			$new_amount=$amount*((($interest/100)*$numb_years)+1);
			$total_amount_borrowed=$total_amount_borrowed+$new_amount;
			$pymts=$this->calculateInterestRate($interest, $new_amount);
			if($pymts < 50)
			{
				$tastrisk="***";
				$tflag='T';
			}
			else
			{
				$tastrisk="";
			}
			$total_monthly_paymts=$total_monthly_paymts+$pymts;
			$this->printTableDetails($loan_period, $loan_type, $tot_stmt, $new_amount, "", $interest,"**", $term, $pymts, $tastrisk);
			$line++;
		}
		$grand_total=$grand_total+$total_amount_borrowed;
		$grand_pymt_total=$grand_pymt_total+$total_monthly_paymts;
		$this->printSectionSummary($total_amount_borrowed,$total_monthly_paymts,"");
		$line++;
		$this->SetFont('Times','I',10);
		$this->setSideBorders("* Monthly payment calculation for the unsubsidized loan assumes that the student has not paid the accrued interest",'L');
		$line++;
		$this->setSideBorders("due on the loan between the time of award and repayment.",'L');
		$this->setSideBorders(" ",'L');
		$line++;

		$this->SetFont('Times','',9);
		$this->setSideBorders("** The interest rates reflected above represent the initial interest rate on each loan.  Please note that the interest rates were adjustable for the",'L');
		$line++;
		$this->setSideBorders("years 2005-2006 and prior.  PSU recommends that you contact your lender to obtain the current interest rates.",'L');
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		if($tflag=='T')
		{
			$this->SetFont('Times','',9);
			$this->setSideBorders("*** The minimum payment on individual loans is $50.  The above estimates assume loans will be consolidated. ",'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}

		$this->SetFont('Times','B',12);
		$this->setSideBordersReverse("FEDERAL PERKINS LOAN (STUDENT)",'L'); 
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setTableHead(" ");
		$line++;
		$line++;

		$this->SetFont('Times','',10);
		// getting Perkins loan information...
		$fund_code_key='PERK%';
		$recs=BannerExitLoans::srSalutePerk($fund_code_key,$id);
		$total_amount_borrowed=0;
		$total_monthly_paymts=0;
		$tflag='F';
		foreach($recs as $rval)
		{
			$century=substr(date("Y"),0,2);
			$loan_period=$century.substr($rval['aid_year_key'],0,2)."-".$century.substr($rval['aid_year_key'],2,2);
			$loan_type="Federal Perkins Loan";
			$amount=$rval['amount_borrowed'];
			$tot_stmt=$this->pdf_money_format($amount);
			$total_amount_borrowed=$total_amount_borrowed+$amount;
			$interest=$rval['interest_rate'];
			$term=$rval['term'];
			$pymts=$this->calculateInterestRate($interest, $amount);
			if($pymts <= 40 && $pymts > 0)
			{
				$tastrisk="****";
				$tflag='T';
				$pymts=40;
			}
			else
			{
				$tastrisk="";
			}
			$total_monthly_paymts=$total_monthly_paymts+$pymts;
			$this->printTableDetails($loan_period, $loan_type, $tot_stmt, $amount, "", $interest,"", $term, $pymts, $tastrisk);
			$line++;
		}
		$grand_total=$grand_total+$total_amount_borrowed;
		$grand_pymt_total=$grand_pymt_total+$total_monthly_paymts;
		$this->printSectionSummary($total_amount_borrowed,$total_monthly_paymts,"");
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		if($tflag=='T')
		{
			$this->SetFont('Times','',9);
			$this->setSideBorders("****  The minimum payment on all PSU Perkins loans combined is $40",'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}

		$skiphead="F";
		if($line >= 30)
		{
			$line=0;
			$skiphead="T";
			$this->AddPage('P','Portrait');
			$this->ln(5);
			$this->setBorderTop();
			$line++;
			$this->SetFont('Times','B',12);
			$this->setSideBorders("ID: ".$id.", ".$first_name." ".$last_name,'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
			$this->setSideBorders("PLYMOUTH STATE UNIVERSITY",'C');
			$line++;
			$this->setSideBorders("EDUCATIONAL LOAN SUMMARY",'C');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}

		$this->SetFont('Times','B',12);
		$this->setSideBordersReverse("PRIVATE STUDENT LOAN (STUDENT)",'L'); 
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setTableHead(" ");
		$line++;
		$line++;

		$this->SetFont('Times','',10);
		// getting Private Student loan information...
		$fund_source='OTHR';
		$fund_type='LOAN';
		$recs=BannerExitLoans::srSaluteAlt($fund_source,$fund_type,$id);
		$total_amount_borrowed=0;
		$total_monthly_paymts=0;
		$tflag='F';
		foreach($recs as $rval)
		{
			$century=substr(date("Y"),0,2);
			$loan_period=$century.substr($rval['aid_year_key'],0,2)."-".$century.substr($rval['aid_year_key'],2,2);
			$loan_type="Private Student Loan";
			$amount=$rval['amount_borrowed'];
			$tot_stmt=$this->pdf_money_format($amount);
			$aidyear=$rval['aid_year_key'];
			$acyr_code=BannerExitLoans::getAcyrCode($aidyear);
			$interest=$rval['interest_rate'];
			$curr_acad_year=$this->getCurrAcadYear();
			$numb_years=($curr_acad_year-$acyr_code)+1.5;
			$new_amount=$amount*((($interest/100)*$numb_years)+1);
			$term=$rval['term'];
			$total_amount_borrowed=$total_amount_borrowed+$new_amount;
			$pymts=$this->calculateInterestRate($interest, $new_amount);
			if($pymts < 50)
			{
				$tastrisk="***";
				$tflag='T';
			}
			else
			{
				$tastrisk="";
			}
			$total_monthly_paymts=$total_monthly_paymts+$pymts;
			$this->printTableDetails($loan_period, $loan_type, $tot_stmt, $new_amount, "*", $interest,"**", $term, $pymts, $tastrisk);
			$line++;
		}
		$grand_total=$grand_total+$total_amount_borrowed;
		$grand_pymt_total=$grand_pymt_total+$total_monthly_paymts;
		$this->printSectionSummary($total_amount_borrowed,$total_monthly_paymts,"*");
		$line++;
		$this->SetFont('Times','I',10);
		$this->setSideBorders("* Monthly payment calculation for the private loan assumes that the student has not paid the accrued interest due",'L');
		$line++;
		$this->setSideBorders("on the loan between the time of award and repayment.",'L');
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		$this->SetFont('Times','',9);
		$this->SetBorderLeft();
		$this->Cell(47,5,"** The interest rate in this section ",0,0,'L');
		$this->SetFont('Times','B',9);
		$this->Cell(45,5,chr(34)."is not your actual interest rate.".chr(34),'B',0,'L');
		$this->SetFont('Times','',9);
		$this->Cell(100.1,5,"  The interest rates on private student loans are not available to ",0,0,'L');
		$this->SetBorderRight();
		$this->ln();
		$line++;
		$this->setSideBorders("Plymouth State University.  However, PSU wanted to provide you with an estimated monthly payment for planning purposes.  Please contact",'L');
		$line++;
		$this->setSideBorders("your lender for your actual interest rate, term and projected monthly payments.",'L');
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		if($tflag=='T')
		{
			$this->SetFont('Times','',9);
			$this->setSideBorders("*** The minimum payment on individual loans is $50.  The above estimates assume loans will be consolidated. ",'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}

		$this->grandTotalReverse("TOTAL AMOUNT BORROWED (STUDENT):",$grand_total,"TOTAL MONTHLY PAYMENTS (STUDENT):",$grand_pymt_total);
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		$this->setTextColor(0,0,0);

		if($skiphead=="F")
		{
			while($line < 52)
			{
				$this->setSideBorders(" ",'L');
				$line++;
			}
			$line=0;
			$this->AddPage('P','Portrait');
			$this->ln(5);
			$this->setBorderTop();
			$line++;
			$this->SetFont('Times','B',12);
			$this->setSideBorders("ID: ".$id.", ".$first_name." ".$last_name,'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
			$this->setSideBorders("PLYMOUTH STATE UNIVERSITY",'C');
			$line++;
			$this->setSideBorders("EDUCATIONAL LOAN SUMMARY",'C');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}
		$this->setSideBordersReverse("PARENT LOANS FOR UNDERGRADUATE STUDENTS (PARENT)",'L'); 
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;

		$this->setTableHead(" ");
		$line++;
		$line++;

		$overall_total=$grand_total;
		$overall_pymt_total=$grand_pymt_total;
		$grand_total=0;
		$grand_pymt_total=0;
		$this->SetFont('Times','',10);
		// getting Private Student loan information...
		$fund_source='OTHR';
		$fund_type='LOAN';
		$recs=BannerExitLoans::srSaluteParent($id);
		$total_amount_borrowed=0;
		$total_monthly_paymts=0;
		$pgrand_total=0;
		$pgrand_pymt_total=0;
		$tflag='F';
		foreach($recs as $rval)
		{
			$century=substr(date("Y"),0,2);
			$loan_period=$century.substr($rval['aid_year_key'],0,2)."-".$century.substr($rval['aid_year_key'],2,2);
			$loan_type="Parent Loan";
			$aidyear=$rval['aid_year_key'];
			if($aidyear >= '0910')
			{
				$interest=8;
			}
			else
			{
				$interest=8.5;
			}
			$term=$rval['term'];
			$amount=$rval['amount_borrowed'];
			$tot_stmt=$this->pdf_money_format($amount);
			$acyr_code=BannerExitLoans::getAcyrCode($aidyear);
			$curr_acad_year=$this->getCurrAcadYear();
			$numb_years=($curr_acad_year-$acyr_code)+1.5;
			$term=$rval['term'];
			if($acyr_code >= "2008")
			{
				$sastrisk="*";
				$new_amount=$amount*((($interest/100)*$numb_years)+1);
			}
			else
			{
				$sastrisk="";
				$new_amount=$amount;
			}
			$total_amount_borrowed=$total_amount_borrowed+$new_amount;
			$pymts=$this->calculateInterestRate($interest, $new_amount);
			if($pymts < 50)
			{
				$tastrisk="***";
				$tflag='T';
			}
			else
			{
				$tastrisk="";
			}
			$total_monthly_paymts=$total_monthly_paymts+$pymts;
			$this->printTableDetails($loan_period, $loan_type, $tot_stmt, $new_amount, $sastrisk, $interest,"", $term, $pymts, $tastrisk);
			$line++;
		}
		$grand_total=$grand_total+$total_amount_borrowed;
		$grand_pymt_total=$grand_pymt_total+$total_monthly_paymts;
		$overall_total=$overall_total+$grand_total;
		$overall_pymt_total=$overall_pymt_total+$grand_pymt_total;
		$this->printSectionSummary($total_amount_borrowed,$total_monthly_paymts,"");
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		if($acyr_code >= "2008")
		{
			$this->SetFont('Times','I',10);
			$this->setSideBorders("* Monthly payment calculation for the Federal PLUS loan assumes that to date the accrued interest has not been",'L');
			$line++;
			$this->setSideBorders("paid on any loans originated in 2008-09 or after.",'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
			$this->SetFont('Times','',9);
		}

		if($tflag=='T')
		{
			$this->SetFont('Times','',9);
			$this->setSideBorders("*** The minimum payment on individual loans is $50.  The above estimates assume loans will be consolidated. ",'L');
			$line++;
			$this->setSideBorders(" ",'L');
			$line++;
		}

		$this->grandTotalReverse("TOTAL AMOUNT BORROWED (PARENT):",$grand_total,"TOTAL MONTHLY PAYMENTS (PARENT):",$grand_pymt_total);
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		$this->setTextColor(0,0,0);

		$this->grandTotalReverse("TOTAL BORROWED (STUDENT+PARENT):",$overall_total,"TTL MONTHLY PMTS (STUDENT+PARENT):",$overall_pymt_total);
		$line++;
		$this->setSideBorders(" ",'L');
		$line++;
		$this->setTextColor(0,0,0);

		while($line < 54)
		{
			$this->setSideBorders(" ",'L');
			$line++;
		}
	}


	function contactInfoPrivate()
	{
		$line=0;
		$this->AddPage('P','Portrait');
		$this->ln(5);


		$this->setBorderTop();
		$line++;
		$this->setSideBorders(" ",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->setBorderLeft();
		$this->SetFont('Times','BU',12);
		$this->Cell(192.1,5,"Contact Information: Private Student Loans",0,0,'C');
		$this->SetFont('Times','',10);
		$this->setBorderRight();
		$this->ln();
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("NHHEAF-Leaf and Tree loans",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 719-0708",'C');
		$line++;
		$this->setSideBorders("Website: http://www.shheaf.org",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("Discover Student Loans",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (877) 728-3030",'C');
		$line++;
		$this->setSideBorders("Website: http://www.discoverstudentloans.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("Citizens Trufit loan",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 708-6684",'C');
		$line++;
		$this->setSideBorders("Website: http://www.citizensbank.com/TruFitStudentLoan/",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;
		$this->SetFont('Times','B',10);
		$this->setSideBorders("Wells Fargo-Collegiate loan",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: ((866) 380-1727",'C');
		$line++;
		$this->setSideBorders("Website: http://www.wellsfargo.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("Sun Trust Student loans",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 522-3006",'C');
		$line++;
		$this->setSideBorders("Website: http://www.suntrusteducation.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("CitiAssist Student Loans",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 967-24003",'C');
		$line++;
		$this->setSideBorders("Website: http://www.studentloan.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("MEFA Student Alternative Loan:",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 449-6332",'C');
		$line++;
		$this->setSideBorders("Website: http://www.mefa.org",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("Citibank CitiAssist Loan",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: (800) 967-2400",'C');
		$line++;
		$this->setSideBorders("Website: http://www.studentloan.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("RISLA Alternative loan",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone: 1-800-758-7562",'C');
		$line++;
		$this->setSideBorders("Website: http://www.risla.org",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',10);
		$this->setSideBorders("Chase Student Loans",'C');
		$line++;
		$this->SetFont('Times','',10);
		$this->setSideBorders("Phone:  1-800-487-4404",'C');
		$line++;
		$this->setSideBorders("Website:  http://www.chasestudentloans.com",'C');
		$line++;

		$this->setSideBorders(" ",'C');
		$line++;
		$this->setSideBorders(" ",'C');
		$line++;

		$this->SetFont('Times','B',14);
		$this->setSideBorders("CONSOLIDATION",'C');
		$line++;
		$this->setSideBorders(" ",'C');
		$line++;
		$this->SetBorderLeft();
		$this->SetFont('Times','B',12);
		$this->Cell(79,5,"              Federal Student loan consoliation ",0,0,'L');
		$this->SetFont('Times','',12);
		$this->Cell(113.1,5,"is available through the federal government at Direct Loan",0,0,'L');
		$this->SetBorderRight();
		$this->ln();
		$line++;
		$this->SetSideBorders("              borrower services 1-800-557-7392 or via their website:  http://www.loanconsolidation.ed.gov/.","L");
		$line++;
		$this->setSideBorders(" ",'C');
		$line++;
		$this->SetBorderLeft();
		$this->SetFont('Times','B',12);
		$this->Cell(98,5,"              Private alternative student loan consolidation",0,0,'L');
		$this->SetFont('Times','',12);
		$this->Cell(94.1,5,"is currently available through Wells Fargo",0,0,'L');
		$this->SetBorderRight();
		$this->ln();
		$line++;
		$this->setSideBorders("              1-877-336-1307 or website: http://www.wellsfargo.com/jump/regional/privateconsolidation",'L');
		$line++;
		
		while($line < 54)
		{
			$this->setSideBorders(" ",'C');
			$line++;
		}
		
	}

	function Footer()
	{
		if($this->type=="Letter")
		{
			$this->SetY(-20);
			$this->SetFont('Times','BI',7);
			$this->Cell(0,10,' Page '.$this->GroupPageNo().' of '.$this->PageGroupAlias(),0,0,'L');
		}
		elseif($this->type=="Report" || $this->type=="Public")
		{
			$this->SetFont('Times','BI',7);
			$this->setSideBorders(' Page '.$this->GroupPageNo().' of '.$this->PageGroupAlias(),'L');
			$this->setSideBorders('','L');
			$this->setBorderBottom();
		}
	}
}// end function pdf class

