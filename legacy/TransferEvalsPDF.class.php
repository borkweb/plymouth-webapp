<?php
require_once('PSUTools.class.php');
require('fpdf/fpdf.php');
set_time_limit(180);

//$GLOBALS['BANNER']->debug = true;

/**
 * TransferEvalsPDF.class.php
 *
 * Transfer Student Evaluation Report PDF Object
 *
 * &copy; 2010 Plymouth State University ITS
 *
 * @author		Laurianne Olcott <max@plymouth.edu>
 */

class TransferEvalsPDF extends FPDF
{
	function Header()
	{
		$this->Image('images/small_header.png',10,4,60);
		$this->SetFont('Arial','B',8);
		$this->ln(12);
		$msg="Office of Undergraduate Studies, MSC#8";
		$this->Cell(90,4,$msg,'0',1,'L');
		$msg="17 High Street";
		$this->Cell(90,4,$msg,'0',1,'L');
		//Move to the right and produce date
		$this->Cell(73);
		$this->SetFont('Arial','',7);
		$this->Cell(90,0,date("l, F d Y"),'0',1,'R');
		$this->SetFont('Arial','B',8);
		$msg="Plymouth, New Hampshire 03264";
		$this->Cell(90,4,$msg,'0',0,'L');
		$this->ln(12);
	}

	function Footer()
	{
		$this->SetY(-25);
		//Arial bold 7
		$this->SetFont('Arial','B',7);
		//Page footnotes
		$msg="*SB* = PSU course substitution";
		$this->Cell(180,0,$msg,0,0,'C');
		$this->ln(3);
		$msg="*1 Student provides course description; *2 Student provides syllabus; *3 PSU Chair is reviewing";	
		$this->Cell(180,0,$msg,0,0,'C');
		$this->ln(3);
		$msg="Courses with identical GROUP codes have been grouped together to create PSU equivalent(s)";
		$this->Cell(180,0,$msg,0,0,'C');	
		$this->ln(3);
		$msg="Note: 1999 = 1000 level transfer credit;  2999 = 2000 level transfer credit";
		$this->Cell(180,0,$msg,0,0,'C');	
		$this->ln(3);
		$msg="3999 = 3000 level transfer credit;  4999 = 4000 level transfer credit";
		$this->Cell(180,0,$msg,0,0,'C');	
	}

	function getTransferMajors($pidm,$studentid,$termcode,$fullname,$type)
	{
		if($type=="pre")
		{
			$majorinfo=BannerTrForms::getPreMajorInfo($pidm);
		}
		else
		{
			$majorinfo=BannerTrForms::getMajorInfo($pidm);
		}
		$i=0;
		$j=0;
		$major=array();
		$option=array();
		$prevmajor=" ";
		$prevoption=" ";
		foreach($majorinfo as $mval)
		{
			if($mval['lfst_code']=='MAJOR' && $i<2)
			{
				if (($prevmajor != $mval['majr_code'])&& !is_null($mval['majr_code']))
				{
					$i++;
					$major[$i] = $mval['description'];
					$major_seqno = $mval['lcur_seqno'];
					if($i==1)
					{
						$this->Cell(20,4,'NAME:','0',0,'L');
						$this->Cell(20,4,$fullname,'0',0,'L');
					}
					elseif($i==2 && $j==0)
					{
						$this->Cell(20,4,'Student ID: ','0',0,'L');
						$this->Cell(20,4,$studentid,'0',0,'L');
					}
					else
					{
						$this->Cell(40);
					}
					$this->Cell(92,4,'Major '.$i.': ','0',0,'R');
					$this->Cell(55,4,$major[$i],'0',1,'LR');
					$prevmajor=$mval['majr_code'];
					$first=true;
				}
			}
			if($mval['lfst_code']=='CONCENTRATION')
			{
				if($first)
				{
					$j=0;
					$first=false;
				}
				$j++;
				if($major_seqno == $mval['lcur_seqno']  && !is_null($mval['majr_code']) && $prevoption != $mval['majr_code'])
				{
					$option[$j] = $mval['description'];
					if($i==1 && $j==1)
					{
						$this->Cell(20,4,'Student ID: ','0',0,'L');
						$this->Cell(20,4,$studentid,'0',0,'L');
					}
					else
					{
						$this->Cell(40);
					}
					$this->Cell(92,4,'Option '.$j.': ','0',0,'R');
					$this->Cell(55,4,$option[$j],'0',1,'LR');
					$prevoption = $mval['majr_code'];
				}
			}
		} //end foreach
	}// end function getTransferMajors

	function printForms($pidm,$studentid,$termcode,$fullname,$inst_name,$ceeb,$transterm,$oldgened)
	{
		$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
		$this->Cell(58,4,$transterm,'B',1,'LR');
		$this->Cell(12,4,'CEEB #:','0',0,'L');
		$this->SetFont('Arial','',7);
		$this->Cell(20,4,$ceeb,'0',0,'L');
		$this->SetFont('Arial','B',8);
		$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
		$this->SetFont('Arial','',7);
		$this->Cell(120,4,$inst_name,'0',0,'LR');
		$this->ln(8);
		$this->SetFont('Arial','B',8);
		$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
		$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
		$this->Cell(20,4,'COURSE:','0',0,'LR');
		$this->Cell(15,4,'GROUP:','0',0,'LR');
		$this->Cell(45,4,'TITLE:','0',0,'LR');
		$this->Cell(15,4,'CREDITS:','0',0,'LR');
		$this->Cell(15,4,'SUBJECT:','0',0,'LR');
		$this->Cell(15,4,'COURSE:','0',0,'LR');
		$this->Cell(15,4,'CREDITS:','0',0,'LR');
		$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
		$this->ln(4);
		$this->Cell(190,4,'','B',1,'LR');
		$detaillines=0;
		$courseinfo=BannerTrForms::getTransferCourses($pidm,$ceeb);
		if($courseinfo)
		{
			foreach($courseinfo as $cval)
			{
				$course_num=rtrim($cval['r_trans_course_numbers']);
				$group=$cval['r_group'];
				if (strlen($course_num)> 20 && $group !='')
				{
					$course_num=$course_num.':';
					$this->SetFont('Arial','',7);
					$this->Cell(strlen($course_num)+95-strlen($course_num),4,$course_num,'R',1,'LR');
					$this->Cell(20,4,'','0',0,'LR');
					$detaillines++;
				}
				if (strlen($course_num)>20 && strlen($course_num)<35 && $group=='')
				{
					$this->SetFont('Arial','',7);
					$this->Cell(35-strlen($course_num)+strlen($course_num)-15,4,$course_num,'0',0,'LR');
				}
				$course_title=$cval['r_tcrse_title'];
				$course_credits=$cval['r_trans_credit_hours'];
				if ($course_credits > 0 && $course_credits < 1)
				{
					$course_credits='0'.$course_credits;
				}
				if ($course_credits=='')
				{
					$course_credits='0';
				}
				if(strlen($course_num) <=20)
				{
					$this->SetFont('Arial','',7);
					$this->Cell(20,4,$course_num,'0',0,'LR');
				}
				$this->SetFont('Arial','',7);
				$this->Cell(15,4,$group,'0',0,'C');
				$this->Cell(45,4,$course_title,'0',0,'LR');
				$this->Cell(15,4,$course_credits,'R',0,'R');
				$trit_seqno=$cval['r_trit_seq_no'];
				$tram_seqno=$cval['r_tram_seq_no'];
				$trcr_seqno=$cval['r_seq_no'];
				// now get equivalent courses
				$eqv=0;
				$equivalents=BannerTrForms::getPsuEquivalents($pidm,$trit_seqno,$tram_seqno,$trcr_seqno);
				if($equivalents)
				{
					foreach($equivalents as $eval)
					{
						$eqv++;
						if(count($equivalents) > 1 && $eqv > 1 )
						{
							$this->SetFont('Arial','',7);
							$this->Cell(20,4,'','0',0,'LR');
							$this->Cell(15,4,'','0',0,'C');
							$this->Cell(45,4,'','0',0,'LR');
							$this->Cell(15,4,'','R',0,'C');
							$equiv_subj=$eval['r_subj_code'];
							$equiv_numb=$eval['r_crse_numb'];
							$equiv_credits=$eval['r_credit_hours'];
							if ($equiv_credits > 0 && $equiv_credits < 1)
							{
								$equiv_credits='0'.$equiv_credits;
							}
							if ($equiv_credits=='')
							{
								$equiv_credits='0';
							}
							$this->Cell(15,4,$equiv_subj,'0',0,'LR');
							$this->Cell(15,4,$equiv_numb,'0',0,'LR');
							$this->Cell(15,4,$equiv_credits,'0',0,'R');
							$trce_seqno=$eval['r_seq_no'];
							$trcr_seqno=$eval['r_trcr_seq_no'];
							$trit_seqno=$eval['r_trit_seq_no'];
							$tram_seqno=$eval['r_tram_seq_no'];
						}
						else
						{
							$equiv_subj=$eval['r_subj_code'];
							$equiv_numb=$eval['r_crse_numb'];
							$equiv_credits=$eval['r_credit_hours'];
							if ($equiv_credits > 0 && $equiv_credits < 1)
							{
								$equiv_credits='0'.$equiv_credits;
							}
							if ($equiv_credits=='')
							{
								$equiv_credits='0';
							}
							$this->Cell(15,4,$equiv_subj,'0',0,'LR');
							$this->Cell(15,4,$equiv_numb,'0',0,'LR');
							$this->Cell(15,4,$equiv_credits,'0',0,'R');
							$trce_seqno=$eval['r_seq_no'];
							$trcr_seqno=$eval['r_trcr_seq_no'];
							$trit_seqno=$eval['r_trit_seq_no'];
							$tram_seqno=$eval['r_tram_seq_no'];
						}
						$att=0;
						$attributes=BannerTrForms::getPsuAttributes($pidm,$trit_seqno,$tram_seqno,$trcr_seqno,$trce_seqno,$oldgened);           
						if($attributes)
						{
							$attribs = "";
							foreach($attributes as $aval)
							{	
								$att++;
								if(count($attributes) > 1 && $att > 1)
								{
									$this->SetFont('Arial','',7);
									$this->Cell(20,4,'','0',0,'LR');
									$this->Cell(15,4,'','0',0,'C');
									$this->Cell(45,4,'','0',0,'LR');
									$this->Cell(15,4,'','R',0,'C');
									$this->Cell(15,4,'','0',0,'LR');
									$this->Cell(15,4,'','0',0,'LR');
									$this->Cell(15,4,'','0',0,'C');
									$attribs = $aval['r_desc'];
									$this->Cell(50,4,$attribs,'0',1,'LR');
									$detaillines++;
									if($detaillines>=45)
									{
										$this->AddPage('P','Letter');
										$detaillines=0;
										$this->SetFont('Arial','B',8);
										$this->getTransferMajors($pidm,$studentid,$termcode,$fullname);
										$this->ln(2);
										$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
										$this->Cell(58,4,$transterm,'B',1,'LR');
										$this->Cell(12,4,'CEEB #:','0',0,'L');
										$this->SetFont('Arial','',7);
										$this->Cell(20,4,$ceeb,'0',0,'L');
										$this->SetFont('Arial','B',8);
										$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
										$this->SetFont('Arial','',7);
										$w=$this->GetStringWidth($inst_name);
										$this->Cell($w+2,4,$inst_name,'0',0,'LR');
										$this->SetFont('Arial','I',8);
										$this->Cell(20,4,'(Continued)','0',0,'LR');
										$this->SetFont('Arial','',7);
										$this->ln(8);
										$this->SetFont('Arial','B',8);
										$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
										$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
										$this->Cell(20,4,'COURSE:','0',0,'LR');
										$this->Cell(15,4,'GROUP:','0',0,'LR');
										$this->Cell(45,4,'TITLE:','0',0,'LR');
										$this->Cell(15,4,'CREDITS:','0',0,'LR');
										$this->Cell(15,4,'SUBJECT:','0',0,'LR');
										$this->Cell(15,4,'COURSE:','0',0,'LR');
										$this->Cell(15,4,'CREDITS:','0',0,'LR');
										$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
										$this->ln(4);
										$this->Cell(190,4,'','B',1,'LR');
									}
								}
								else
								{
									$attribs = $aval['r_desc'];
									$this->Cell(50,4,$attribs,'0',1,'LR');
									$detaillines++;
									if($detaillines>=45)
									{
										$this->AddPage('P','Letter');
										$detaillines=0;
										$this->SetFont('Arial','B',8);
										$this->getTransferMajors($pidm,$studentid,$termcode,$fullname);
										$this->ln(2);
										$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
										$this->Cell(58,4,$transterm,'B',1,'LR');
										$this->Cell(12,4,'CEEB #:','0',0,'L');
										$this->SetFont('Arial','',7);
										$this->Cell(20,4,$ceeb,'0',0,'L');
										$this->SetFont('Arial','B',8);
										$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
										$this->SetFont('Arial','',7);
										$w=$this->GetStringWidth($inst_name);
										$this->Cell($w+2,4,$inst_name,'0',0,'LR');
										$this->SetFont('Arial','I',8);
										$this->Cell(20,4,'(Continued)','0',0,'LR');
										$this->SetFont('Arial','',7);
										$this->ln(8);
										$this->SetFont('Arial','B',8);
										$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
										$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
										$this->Cell(20,4,'COURSE:','0',0,'LR');
										$this->Cell(15,4,'GROUP:','0',0,'LR');
										$this->Cell(45,4,'TITLE:','0',0,'LR');
										$this->Cell(15,4,'CREDITS:','0',0,'LR');
										$this->Cell(15,4,'SUBJECT:','0',0,'LR');
										$this->Cell(15,4,'COURSE:','0',0,'LR');
										$this->Cell(15,4,'CREDITS:','0',0,'LR');
										$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
										$this->ln(4);
										$this->Cell(190,4,'','B',1,'LR');
									}
								}
							}
							$coursearray['attributes']=$attribs;
						}
						else // no attributes were found
						{
							$attributes="";
							$this->Cell(50,4,$attributes,'0',1,'LR');
							$detaillines++;
							if($detaillines>=45)
							{
								$this->AddPage('P','Letter');
								$detaillines=0;
								$this->SetFont('Arial','B',8);
								$this->getTransferMajors($pidm,$studentid,$termcode,$fullname);
								$this->ln(2);
								$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
								$this->Cell(58,4,$transterm,'B',1,'LR');
								$this->Cell(12,4,'CEEB #:','0',0,'L');
								$this->SetFont('Arial','',7);
								$this->Cell(20,4,$ceeb,'0',0,'L');
								$this->SetFont('Arial','B',8);
								$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
								$this->SetFont('Arial','',7);
								$w=$this->GetStringWidth($inst_name);
								$this->Cell($w+2,4,$inst_name,'0',0,'LR');
								$this->SetFont('Arial','I',8);
								$this->Cell(20,4,'(Continued)','0',0,'LR');
								$this->SetFont('Arial','',7);
								$this->ln(8);
								$this->SetFont('Arial','B',8);
								$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
								$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
								$this->Cell(20,4,'COURSE:','0',0,'LR');
								$this->Cell(15,4,'GROUP:','0',0,'LR');
								$this->Cell(45,4,'TITLE:','0',0,'LR');
								$this->Cell(15,4,'CREDITS:','0',0,'LR');
								$this->Cell(15,4,'SUBJECT:','0',0,'LR');
								$this->Cell(15,4,'COURSE:','0',0,'LR');
								$this->Cell(15,4,'CREDITS:','0',0,'LR');
								$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
								$this->ln(4);
								$this->Cell(190,4,'','B',1,'LR');
							}
						}
					}// end foreach equivalencies
				}
				else // no equivalents were found
				{
					$equiv_subj="";
					$equiv_numb="";
					$equiv_credits="";
					$attributes="";
					$this->SetFont('Arial','',7);
					$this->Cell(15,4,$equiv_subj,'0',0,'LR');
					$this->Cell(15,4,$equiv_numb,'0',0,'LR');
					$this->Cell(15,4,$equiv_credits,'0',0,'C');
					$this->Cell(50,4,$attributes,'0',1,'LR');
					$detaillines++;
					if($detaillines>=45)
					{
						$this->AddPage('P','Letter');
						$detaillines=0;
						$this->SetFont('Arial','B',8);
						$this->getTransferMajors($pidm,$studentid,$termcode,$fullname);
						$this->ln(2);
						$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
						$this->Cell(58,4,$transterm,'B',1,'LR');
						$this->Cell(12,4,'CEEB #:','0',0,'L');
						$this->SetFont('Arial','',7);
						$this->Cell(20,4,$ceeb,'0',0,'L');
						$this->SetFont('Arial','B',8);
						$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
						$this->SetFont('Arial','',7);
						$w=$this->GetStringWidth($inst_name);
						$this->Cell($w+2,4,$inst_name,'0',0,'LR');
						$this->SetFont('Arial','I',8);
						$this->Cell(20,4,'(Continued)','0',0,'LR');
						$this->SetFont('Arial','',7);
						$this->ln(8);
						$this->SetFont('Arial','B',8);
						$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
						$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
						$this->Cell(20,4,'COURSE:','0',0,'LR');
						$this->Cell(15,4,'GROUP:','0',0,'LR');
						$this->Cell(45,4,'TITLE:','0',0,'LR');
						$this->Cell(15,4,'CREDITS:','0',0,'LR');
						$this->Cell(15,4,'SUBJECT:','0',0,'LR');
						$this->Cell(15,4,'COURSE:','0',0,'LR');
						$this->Cell(15,4,'CREDITS:','0',0,'LR');
						$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
						$this->ln(4);
						$this->Cell(190,4,'','B',1,'LR');
					}
				}
			}// end foreach courses
		}
		else // no courses were found
		{
			$this->SetFont('Arial','',7);
			$course_num="";
			$group="";
			$course_title="";
			$course_credits="";
			$equiv_subj="";
			$equiv_numb="";
			$equiv_credits="";
			$atributes="";
			$this->Cell(20,4,$course_num,'0',0,'LR');
			$this->Cell(15,4,$group,'0',0,'C');
			$this->Cell(45,4,$course_title,'0',0,'LR');
			$this->Cell(15,4,$course_credits,'R',0,'C');
			$this->Cell(15,4,$equiv_subj,'0',0,'LR');
			$this->Cell(15,4,$equiv_numb,'0',0,'LR');
			$this->Cell(15,4,$equiv_credits,'0',0,'C');
			$this->Cell(50,4,$attributes,'0',1,'LR');
			$detaillines++;
			if($detaillines>=45)
			{
				$this->AddPage('P','Letter');
				$detaillines=0;
				$this->SetFont('Arial','B',8);
				$this->getTransferMajors($pidm,$studentid,$termcode,$fullname);
				$this->ln(2);
				$this->Cell(132,4,'ENTERING TERM:','B',0,'R');
				$this->Cell(58,4,$transterm,'B',1,'LR');
				$this->Cell(12,4,'CEEB #:','0',0,'L');
				$this->SetFont('Arial','',7);
				$this->Cell(20,4,$ceeb,'0',0,'L');
				$this->SetFont('Arial','B',8);
				$this->Cell(40,4,'TRANSFER INSTITUTION:','0',0,'LR');
				$this->SetFont('Arial','',7);
				$w=$this->GetStringWidth($inst_name);
				$this->Cell($w+2,4,$inst_name,'0',0,'LR');
				$this->SetFont('Arial','I',8);
				$this->Cell(20,4,'(Continued)','0',0,'LR');
				$this->SetFont('Arial','',7);
				$this->ln(8);
				$this->SetFont('Arial','B',8);
				$this->Cell(95,4,'TRANSFER INSTITUTION:','1',0,'LR');
				$this->Cell(95,4,'PSU EQUIVALENT:','1',1,'LR');
				$this->Cell(20,4,'COURSE:','0',0,'LR');
				$this->Cell(15,4,'GROUP:','0',0,'LR');
				$this->Cell(45,4,'TITLE:','0',0,'LR');
				$this->Cell(15,4,'CREDITS:','0',0,'LR');
				$this->Cell(15,4,'SUBJECT:','0',0,'LR');
				$this->Cell(15,4,'COURSE:','0',0,'LR');
				$this->Cell(15,4,'CREDITS:','0',0,'LR');
				$this->Cell(50,4,'ATTRIBUTES/COMMENTS:','0',0,'LR');
				$this->ln(4);
				$this->Cell(190,4,'','B',1,'LR');
			}
		}
	}// end printForms
}// end function TransferEvalsPDF class

