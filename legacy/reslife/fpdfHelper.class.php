<?php 
/**
 * fpdf.php
 *
 * defines functions that extend the fpdf pdf generation class
 *
 * @version		0.0.1
 * @module		fdpf
 * @author		Randy Dustin <rldustin@plymouth.edu>
 * @copyright 2007, Plymouth State University, Residential Life
 */ 
require_once('fpdf/fpdf.php');
$current_error_settings = error_reporting();

error_reporting($current_error_settings ^ E_DEPRECATED);

$FPDF = new FPDF();

if (!defined('PARAGRAPH_STRING')) define('PARAGRAPH_STRING', '~~~');

class fpdfHelper extends FPDF 
{
//var $title;
var $footer_text;
var $header_text = array();
var $Yval;
var $header_default;
var $header_exceptions;

function setup ($orientation='L',$unit='mm',$format='A4') 
{
	$this->FPDF($orientation, $unit, $format); 
}

function fpdfOutput ($name, $destination) 
{
	return $this->Output($name, $destination);
}

function Header()
{
	$this->SetLineWidth(.4);
	$this->SetFont('Arial','B',12);
	$this->Cell(58,10,$this->title,0,1);
	$this->SetFont('Arial','B',10);
	
	//Header
	foreach($this->header_text as $col)
	
//	switch($col) 
//	{
//	case 'Full Name':
//		$this->Cell(60,7,$col);
//		break;
//	case 'PSU Email Address':
//		$this->Cell(52,7,$col);
//		break;			
//	default:
//		$this->Cell($this->header_default,7,$col, '',0,'C');		
//	}
	
	if($this->header_exceptions)
	{
		if(array_key_exists($col, $this->header_exceptions))
		{
			$this->Cell($this->header_exceptions[$col],7,$col);
		}
		else
		{
			$this->Cell($this->header_default,7,$col, '',0,'C');
		}
	}
	else
	{
		$this->Cell($this->header_default,7,$col, '',0,'C');
	}
	$this->Ln();
	
	$x = $this->GetX();
	$y = $this->GetY();
	$this->Line($x,$y,$this->Yval,$y);
	$this->Ln(1);
}

function SetFooterText($text=null) 
{
	$this->footer_text = $text;
}

function SetHeaderText($text=null) 
{
	$this->header_text = $text;
}

function SetYLength($val=288) 
{
	$this->Yval = $val;
}


function SetHeaderSize($default=22, $exceptions=array()) 
{
	$this->header_default = $default;
	$this->header_exceptions = $exceptions;
}

//Page footer
function Footer()
{
	$this->SetLineWidth(.4);
	$this->Ln();
	$this->SetTextColor(0,0,0);
	$x = $this->GetX();
	$y = $this->GetY();
	$this->Line($x,$y,$this->Yval,$y);
	$this->Ln(2);
	//Arial italic 8
	$this->SetFont('Arial','I',8);
	$this->Cell(0,1,$this->footer_text);
	//Position at 1.5 cm from bottom
	$this->SetY(-8);
	//Page number
	$this->Cell(0,8,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

function nameOrderTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				$this->Cell(60,6,$col);
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function emailTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				$this->Cell(60,6,$col);
			}
			elseif($key == 'email')
			{
				$this->Cell(52,6,$col, '',0,'L');
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function lockoutTable($data)
{		
	$this->SetFont('Arial','',10);
	//$this->Ln(1);
	//Data
	foreach($data as $row) 
	{
		$this->Ln(1);
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				$this->Cell(60,6,$col);
			}
			elseif($key == 'room')
			{
				$this->Cell(22,4,$col, '',0,'C');
				$this->Cell(10,4,'', 1,0);
				$this->Cell(10,4,'', 1,0);
				$this->Cell(10,4,'', 1,0);
				$this->Cell(10,4,'', 1,0);
				$this->Cell(10,4,'', 1,0);
				$this->Cell(10,4,'', 1,0);
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}


function blankTable($data)
{		
	$this->SetFont('Arial','',10);
	//$this->Ln(1);
	//Data
	foreach($data as $row) 
	{
		$this->Ln(.1);
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				foreach ($col as $ln)
				{
					$this->SetLineWidth(.1);
					$this->Cell('',6,$ln,'B',2);
				}
			}
			else
			{
				$this->Cell(15,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function openBedTable($data)
{		
	$this->SetFont('Arial','',10);
	//$this->Ln(1);
	//Data
	foreach($data as $row) 
	{
		$this->Ln(.1);
		foreach($row as $key => $col) 
		{
			if($key == 'student')
			{
				foreach ($col as $ln)
				{
					$this->Cell(25,6,$ln['id'], '',0);
					$this->Cell(20,6,$ln['birthdate'], '',0);
					$this->Cell(20,6,$ln['meal'], '',0,'C');
					$this->Cell(15,6,$ln['sm'], '',0,'C');
					$this->SetLineWidth(.1);
					$this->Cell('',6,$ln['name'],'B',2);
					//need to reset x to the position equal to the id & meal columns
					$x = $this->GetX();
					$y = $this->GetY();
					$this->SetX($x-80);
				}
			}
			elseif($key == 'desc')
			{
				$this->Cell(20,6,$col, '',0,'C');
			}
			else
			{
				$this->Cell(15,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function checkin($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				$this->Cell(60,6,$col);
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln(20);
	}
}

function hdepoTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			switch($key) 
			{
			case 'tbrdepo_entry_date':
				$this->Cell(20,6,$col);
				break;
			case 'tbrdepo_amount':
			case 'smoker_status':
				$this->Cell(18,6,$col, '',0,'C');
				break;
			case 'slrrasg_bldg_code':
			case 'slrrasg_ascd_code':
				$this->Cell(14,6,$col, '',0,'C');
				break;
			default:
				$this->Cell(22,6,$col, '',0,'C');
			}
			
		}
		$this->Ln();
	}
}

function hdepoAttributeTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		if($row['id'] == $id)
		{
			foreach($row as $key => $col) 
			{					
				if($key == 'stvrdef_desc')
				{
					$this->Cell(40,6,$col);
				}
				else
				{
					if($key == 'tbrdepo_entry_date')
					{
						$this->Cell(24,6,'');
					}
					elseif($key == 'phone')
					{
						$this->Cell(40,6,'');
					}
					else
					{
						$this->Cell(22,6,'', '',0,'C');
					}
				}
			}			
		}
		else
		{
			$this->Ln();
			foreach($row as $key => $col)
			{
				if($key == 'tbrdepo_entry_date')
				{
					$this->Cell(24,6,$col);
				}
				elseif($key == 'phone')
				{
					$this->Cell(40,6,$col);
				}
				elseif($key == 'stvrdef_desc')
				{
					$this->Cell(40,6,$col);
				}
				else
				{
					$this->Cell(22,6,$col, '',0,'C');
				}
			}
		}
		$this->Ln();
		$id = $row['id'];
	}
}

function promptedHdepoAttributeTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'tbrdepo_entry_date')
			{
				$this->Cell(25,6,$col);
			}
			elseif($key == 'stvrdef_desc')
			{
				$this->Cell(40,6,$col);
			}
			elseif($key == 'phone')
			{
				$this->Cell(40,6,$col);
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function orientationTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'tbrdepo_entry_date')
			{
				$this->Cell(20,6,$col);
			}
			elseif($key == 'tbrdepo_desc')
			{
				$this->Cell(40,6,$col);
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function withdrawnOrrevoked($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'slrrasg_room_number')
			{
				$this->Cell(15,6,$col, '',0,'C');
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function missingLeaseOrRDtable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			if($key == 'name')
			{
				$this->Cell(60,6,$col,'',0,'L');
			}
			elseif($key == 'phone')
			{
				$this->Cell(24,6,$col, '',0,'L');
			}
			elseif($key == 'artp_desc')
			{
				$this->Cell(43,6,$col, '',0,'L');
			}
			else
			{
				$this->Cell(22,6,$col, '',0,'C');
			}
		}
		$this->Ln();
	}
}

function idLinkedTable($data)
{		
	$this->SetFont('Arial','',10);
	//Data
	foreach($data as $row) 
	{
		foreach($row as $key => $col) 
		{
			$this->Cell(35,6,$col, '',0,'C');
		}
		$this->Ln();
	}
}

} 
?>
