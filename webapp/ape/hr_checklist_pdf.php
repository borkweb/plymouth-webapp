<?php
require_once 'includes/HRChecklist.class.php';
PSU::downloadFix();
require('fpdf/fpdf.php');

$myuser = new PSUPerson($_SESSION['wp_id']);
$person = new PSUPerson($_GET['identifier']);
$complete = $_GET['complete'];
$list = $_GET['checklist'];

//$GLOBALS['BANNER']->debug = true;

/**
 * hr_checklist_pdf.php
 *
 * HR Employee Exit Report PDF Object
 *
 * &copy; 2010 Plymouth State University ITS
 *
 * @author		Laurianne Olcott <max@plymouth.edu>
 */

class PDF extends FPDF
{
	function Header()
	{
		$this->SetFont('Arial','B',10);
	}

	function Footer()
	{
		$this->SetY(-25);
		//Arial bold 7
	}


}// end function PDF class
$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('p');
$pdf->SetFont('Arial','',10);

//identifiers
$pdf->SetFillColor(5,66,6);
$pdf->SetTextColor(255,255,255);
$fill=true;
$pdf->SetFont('Arial','',13);
$pdf->Cell(190,7,'Employee Clearance Checklist for '.$person->formatName('f m l'),0,1,'C',$fill);
$pdf->SetFont('Arial','',10);
$pdf->Cell(190,6,'Username:  '.$person->username,0,1,'C',$fill);
$pdf->setTextColor(5,66,6);
$pdf->ln(5);
$checklist_items=array();
$categories=array();
$checklist = HRChecklist::get($person->pidm, $list);
$categories = HRChecklist::categories($checklist['type']);
$checklist_id = HRChecklist::get( $person->pidm, $list, 'id' );
$closed = HRChecklist::meta_exists( $checklist_id, 'closed', 1 );
if( IDMObject::authZ('permission', 'ape_checklist_employee_exit_hr') ) {
	if( $_POST['checklist_closed'] && !$closed ) {	
		HRChecklist::add_meta( $checklist_id, 'closed', 1 ); 
		HRChecklist::toggle_checklist( $checklist_id, $_REQUEST[ 'identifier' ], true );
		HRChecklist::add_meta( $checklist_id, 'closed_marked_by', $_SESSION['pidm'] ); 
	} elseif( !$_POST[ 'checklist_closed' ] && $closed) {
		HRChecklist::add_meta( $checklist_id, 'closed', 0 ); 
		HRChecklist::toggle_checklist( $checklist_id, $_REQUEST[ 'identifier' ], false );
		HRChecklist::add_meta( $checklist_id, 'closed_marked_by', $_SESSION['pidm'] ); 
	}//end elseif
}//end if

$closed_person = HRChecklist::get_meta( $checklist['id'], 'closed_marked_by', 1 );
$closed_person = $closed_person['meta_value'];

if( $closed_person ) {
	$pdf->SetFont('Arial','B',13);
	$pdf->setTextColor(120,7,41);
	$closed_person = new PSUPerson($closed_person);
	$pdf->Cell(190,5,'This Employee Clearance Form has been closed by '.$closed_person->formatName('f m l').'.',0,1,'L');
	$pdf->setTextColor(0,0,0);
	$pdf->SetFont('Arial','',10);
	$pdf->ln(5);
}//end if

foreach($categories as $category) 
{
	$checklist_items[ $category[ 'name' ] ] = HRChecklist::checklist_items( $category[ 'id' ] );
}//end foreach
if( !$checklist_items)
{
	if (APEAuthZ::employee_clearance()) 
	{
		$pdf->setTextColor(120,7,41);
		$pdf->Cell(190,5,'The employee clearance process has not been started for this individual',0,1,'L');
		$pdf->setTextColor(0,0,0);
	}
	if (APEAuthZ::employee_clearance())
	{
		if ($complete && !$closed)
		{
			$pdf->setTextColor(120,7,41);
			$pdf->Cell(190,5,'It appears as if all of the items have been reviewed and completed.',0,1,'L');
			$pdf->setTextColor(0,0,0);
		}
	}
	if ($closed && !APEAuthZ::employee_clearance())
	{
		$pdf->setTextColor(120,7,41);
		$pdf->Cell(190,5,'This Employee Clearance Form has been closed by HR.',0,1,'L');
		$pdf->setTextColor(0,0,0);
	}
}
else
{
	if (!APEAuthZ::employee_clearance())
	{
		$pdf->setTextColor(0,0,0);
		$pdf->Cell(190,5,'Please contribute any information you may have regarding the following item(s).');
	}
	foreach ($checklist_items as $key=>$checklist_item)
	{
		if ($key != 'id' && $key != 'type' && $key != 'pidm')
		{
			if (($key == 'Campus Police' && $AUTHZ.permission.ape_checklist_employee_exit_police) || 
				($key == 'Travel Office/Accounts Payable' && $AUTHZ.permission.ape_checklist_employee_exit_payable) ||
				($key == 'Residential Life' && $AUTHZ.permission.ape_checklist_employee_exit_reslife) || 
				($key == 'Library' && $AUTHZ.permission.ape_checklist_employee_exit_library) || 
				($key == 'Keys' && $AUTHZ.permission.ape_checklist_employee_exit_keys) || 
				($AUTHZ.permission.ape_checklist_employee_exit_hr) || 
				($key == 'Information Technology' && $AUTHZ.permission.ape_checklist_employee_exit_infotech) ||
				($key == 'Department' && $myuser->department == $person->department ))
			{
				$pdf->SetFont('Arial','BU',12);
				$pdf->setTextColor(5,66,6);
				$pdf->Cell(190,5,$key,0,1,'L');
				foreach ($checklist_item as $entry)
				{
					$pdf->SetFont('Arial','',10);
					$pdf->setTextColor(0,0,0);
					$pdf->Cell(190,5,$entry['name'].': '.$entry['description'],0,1,'L');
					$response=HRChecklist::item_responses( $person->pidm,$entry['id'],'*' );
					if($response)
					{
						$no_answer='t';
						foreach ($response as $record)
						{
							$pdf->setTextColor(120,7,41);
							$prev_response=$record['response'];
							$pdf->Cell(25,5,'     Marked as',0,0,'L'); 
							if ($record['response'] == 'incomplete')
							{
								$pdf->Cell(20,5,'incomplete',0,0,'L');
								$no_answer='f';
							}
							elseif ($record['response'] == 'complete')
							{
								$pdf->Cell(20,5,'complete',0,0,'L');
								$no_answer='f';
							}
							elseif ($record['response'] == 'n/a')
							{
								$pdf->Cell(20,5,'n/a',0,0,'L');
								$no_answer='f';
							}
							$pdf->Cell(5,5,'by',0,0,'L');
							$responder = new PSUPerson( $record[ 'updated_by' ] );
							$record[ 'updated_by' ] = $responder->formatName( 'f m l' );
							$responder->destroy();

							$pdf->Cell(80,5,$record['updated_by'].' at '.$record['activity_date'],0,1,'L');
							$pdf->setTextColor(0,0,0);

							if($no_answer=='t')
							{
								$pdf->setTextColor(120,7,41);
								$pdf->Cell(190,5,'     No action taken',0,1,'L');
								$pdf->setTextColor(0,0,0);
								$no_answer='f';
							}
							if ($record['notes'])
							{
								$pdf->setTextColor(0,0,0);
								$pdf->Cell(95,5,'Do you have more details or anything else to add?',0,1,'L');
								$pdf->setTextColor(120,7,41);
								$pdf->Cell(190,5,"     ".$record['notes'],0,1,'L');
							}						
						}
					}
					else
					{
						$pdf->setTextColor(120,7,41);
						$pdf->Cell(190,5,'     No action taken',0,1,'L'); 
						$pdf->setTextColor(0,0,0);
					}
				}				
			}
		}
    $pdf->ln(5);
	}
}
$pdf->Output($person->formatName('f m l').'-'.time().".pdf",'D');
