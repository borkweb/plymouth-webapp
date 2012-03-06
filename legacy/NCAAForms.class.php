<?php
require_once('PSUTools.class.php');

class OnlineForm
{
	var $db;					//Database Connection
	var $form;					//Un-needed?
	var $sections;				//Section info (order, pages, etc.)
	var $pages;					//Page info (parent section, name, code, etc.)
	var $page_order;			//Page codes and order
	var $table=array(			//DB Table Names
		'user_form'=>'psu.form_user_form',
		'page'=>'psu.form_page',
		'section'=>'psu.form_section',
		'section_page'=>'psu.form_section_page',
		'field'=>'psu.form_field',
		'page_field'=>'psu.form_page_field',
		'master_form'=>'psu.form',
		'version'=>'psu.form_version',
		'user_value'=>'psu.form_user_value',
		'required_rule'=>'psu.form_required_rule',
		'validation'=>'psu.form_validation',
		'sports'=>'psu.ncaa_sports'
	);
	var $percent='';			//Percentage of form completed
	var $fields=array();		//Field information
	var $field_values=array();	//User submitted values
	var $url_prefix='/user/';	//[BASE_DIR]/current directory ?Move this var??

/**
  *OnlineForm
  *
  *sets specific class vars to what is passed in
  *
  *@param string $db
  *@param boolean $current_page
  *@param string $master_form
  */
	function OnlineForm(&$db,$current_page='',$master_form)
	{
		$this->db=$db;
		$this->master_form=$master_form;
		
		$this->sections=$this->getSections();
		$this->pages=$this->getPages();
		$this->page_order=$this->getOrderedPageCodes();

		if($current_page)
		{
			$this->setCurrentPage($current_page);
		}//end if
	}//end OnlineForm

/**
  *buildURL
  *
  *constructs the url for this page
  *
  *@access public
  *@param string $page
  *@return string
  */
	function buildURL($page)
	{
		if($this->id)
			return $this->url_prefix.$this->id.'/'.$page;
		return $this->url_prefix.$page;
	}//end buildURL

/**computeApplicationPercentage
  *
  *the percentage of the completed fields v the required app feilds
  *
  *@access public
  *@return integer
  */
	function computeApplicationPercentage()
	{
		//$app_fields=array('required'=>0,'complete'=>0);
		$form_fields=array('required'=>0,'complete'=>0);
		if($this->percent=='')
		{
			foreach($this->sections as $section)
			{
				$section_fields=$this->computeSectionStatus($section['id']);
				$app_fields['required']+=$section_fields['required'];
				$app_fields['complete']+=$section_fields['complete'];
			}//end foreach
			if($app_fields['complete']>0)
				$this->percent = floor(100*($app_fields['complete']/$app_fields['required']));
			else
				$this->percent = 0;
		}//end if

		return $this->percent;
	}//end computeApplicationPercentage

/**
  *
  * TODO ?
  *
  *
  *
  */
	function computeSectionStatus($section_id)
	{
		
	}//end computeSectionStatus
/**getFields
  *
  *adding fields the fields to data array then returning it
  *
  *@param  $app_id
  *@param string $user_id
  *@param boolean $values_only
  *@return mixed
  */
	//this function is still un-modified
	function getFields($user_id,$values_only=false,$app_id='')
	{
		$sql="SELECT * FROM ".(($app_id)?$this->table['user_value']:$this->table['field_value'])." WHERE user_id=$user_id ".(($app_id)?" AND application_id='$app_id'":"")." ORDER BY app_field";

		$data=array();
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				
				$value = ($values_only)?$row['field_value']:$row;


				if(!isset($data[$row['app_field']]['user_id']) && is_array($data[$row['app_field']]))
				{
					$data[$row['app_field']][] = $value;
				}
				elseif(isset($data[$row['app_field']]))
				{
					$temp = $data[$row['app_field']];
					$data[$row['app_field']] = array($temp, $value);
				}
				else
				{
					$data[$row['app_field']]=$value;
				}
			}//end while
		}//end if
		
		return $data;
	}//end getFields

/**getNextPage
  *
  *returns the url for the next page
  *
  *@access public
  *@param integer $page_code
  *@return string
  */
	function getNextPage($page_code)
	{
		//find the current page in the list of pages
		$current_index=array_search($page_code,$this->page_order);

		//return the next page
		if($current_index<count($this->page_order)-1)
			return $this->pages[$this->page_order[$current_index+1]];
		else
			return '';
	}//end getNextPage
/**
  *getIrderedPageCodes
  *
  *adds multiple string of code to the array then returns the array
  *
  *@access public
  *@param boolean $section
  *@return array
  */
	function getOrderedPageCodes($section=false)
	{
		if($section!==false)
			$where=" AND sp.section_id=$section";

		$sql="SELECT p.code FROM {$this->table['page']} p,{$this->table['section']} s,{$this->table['section_page']} sp WHERE sp.section_id=s.id AND s.master_form_id={$this->master_form} AND p.id=sp.page_id AND sp.end_date is null {$where} ORDER BY section_order,page_order";

		$data=array();
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[]=$row['code'];
			}//end while
		}//end if
		
		return $data;
	}//end getOrderedPageCodes
/**
  *getPages
  *
  *returns an array of URL's
  *
  *@access public
  *@return array
  */
	function getPages()
	{
		$data=array();
		if($results=$this->db->Execute("SELECT * FROM {$this->table['page']} WHERE master_form_id={$this->master_form} ORDER BY page_order,name"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[$row['code']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getPages
/**
  *getpageSection
  *
  *returning specific information from the database
  *
  *@access public
  *@param string $page_code
  *@return string
  */
	function getPageSection($page_code)
	{
		return $this->db->GetOne("SELECT sp.section_id FROM {$this->table['section_page']} sp,{$this->table['page']} p WHERE p.code='$page_code' AND sp.page_id=p.id AND p.master_form_id={$this->master_form}");
	}//end getPageSection
/**
  *getPreviousPage
  *
  *Returns the last page viewed
  *
  *@acces public
  *@param string $page_code
  *@return string
  */
	function getPreviousPage($page_code)
	{
		//find the current page in the list of pages
		$current_index=array_search($page_code,$this->page_order);

		//return the previous page
		if($current_index>0)
			return $this->pages[$this->page_order[$current_index-1]];
		else
			return '';
	}//end getPreviousPage
/**
  *getSections
  *
  *constructs an array of the different web site sections
  *
  *@access public
  *@return mixed
  */
	function getSections()
	{
		$data=array();
		if($results=$this->db->Execute("SELECT * FROM {$this->table['section']} WHERE master_form_id={$this->master_form} ORDER BY section_order,name"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$row['pages']=$this->getOrderedPageCodes($row['id']);
				$data['section_'.$row['section_order']]=$row;
			}//end while
		}//end if
		
		return (count($data)>0)?$data:false;
	}//end getSections
/**
  *
  * TODO ?
  *
  *
  *
  */
	function processRepeatingFields()
	{
	}
/**
  *setCurrentPage
  *
  *changes class vars to information for a specific page 
  *
  *@access public
  *@param string $page_code
  */
	function setCurrentPage($page_code)
	{
		$this->current_page=$page_code;
		$this->current_section=$this->getPageSection($this->current_page);
		$this->next_page=$this->getNextPage($this->current_page);
		$this->previous_page=$this->getPreviousPage($this->current_page);
	}//end setCurrentPage
/**
  *setUser
  *
  *changes current vars to user specific settings.
  *
  *@access public
  *@param string $user_id
  */
	function setUser($user_id)
	{
		$this->user_id=$user_id;
		$this->fields=$this->getFields($this->user_id);
		$this->field_values=$this->getFields($this->user_id,true);
		$this->repeating_fields=$this->getRepeatingFields();
	}//end setUser

}//end OnlineForm
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class PSPApplication
{
	var $db;
	var $app;
	var $sections;
	var $pages;
	var $page_order;
	var $table=array(
		'application'=>'psu_psp.app_user_application',
		'submit_value'=>'psu_psp.app_submitted_user_value',
		'page'=>'psu_psp.app_page',
		'section'=>'psu_psp.app_section',
		'form'=>'psu_psp.application',
		'field_value'=>'psu_psp.app_user_value',
		'required_rule'=>'psu_psp.app_required_rule',
		'program'=>'psu_psp.program',
		'field'=>'psu_psp.app_field',
		'file'=>'psu_psp.app_user_file',
		'repeating_field'=>'psu_psp.app_user_repeating_field'
	);
	var $percent='';
	var $fields=array();
	var $field_values=array();
	var $programs='';
	var $url_prefix='/user/app/';
/**
  *PSPApplication
  *
  *sets class vars to passed in and too defaults
  *
  *@access public
  *@param string $db
  *@param string $current_page
  *@param integer $master_app
  *
  */
	function PSPApplication(&$db,$current_page='',$master_app=1)
	{
		$this->db=$db;
		$this->master_app=$master_app;

		$this->sections=$this->getSections();
		$this->pages=$this->getPages();
		$this->page_order=$this->getOrderedPageCodes();

		if($current_page)
		{
			$this->setCurrentPage($current_page);
		}//end if	
	}//end PSPApplication
/**
  *buildFriendlyPhone
  *
  * makes the phone number and area code clean for display
  *
  *@access public
  */
	function buildFriendlyPhone()
	{
		if($this->field_values['phone_area'] && $this->field_values['phone_number'])
			$this->field_values['friendly_phone']='('.$this->field_values['phone_area'].')'.substr($this->field_values['phone_number'],0,3).'-'.substr($this->field_values['phone_number'],3);
	}//end buildFriendlyPhone
/**
  *buildURL
  *
  *builds the url for the pasted in page
  *
  *@access public
  *@param string $page
  *@return string
  */
	function buildURL($page)
	{
		if($this->id)
			return $this->url_prefix.$this->id.'/'.$page;
		return $this->url_prefix.$page;
	}//end buildURL
	
/**
  *comuteApplicationPercentage
  *
  *computes the completed application v the required and returns the percent
  *
  *@acces public
  *@return integer
  */
	function computeApplicationPercentage()
	{
		$app_fields=array('required'=>0,'complete'=>0);
		if($this->percent=='')
		{
			foreach($this->sections as $section)
			{
				$section_fields=$this->computeSectionStatus($section['id']);
				$app_fields['required']+=$section_fields['required'];
				$app_fields['complete']+=$section_fields['complete'];
			}//end foreach
			if($app_fields['complete']>0)
				$this->percent = floor(100*($app_fields['complete']/$app_fields['required']));
			else
				$this->percent = 0;
		}//end if

		return $this->percent;
	}//end computeApplicationPercentage
/**
  *computePageStatus 
  *
  *computs the current status of the page applications
  *
  *@access public
  *@param string $page_id
  *@return string
  */
	function computePageStatus($page_id)
	{
		$page_fields=array('required'=>0,'complete'=>0);
		$fields=$this->getRequiredFields($page_id);
		foreach($fields as $field)
		{
			$required=0;
			$rule=$this->getRequiredRule($field['required_rule_id']);
			$rule='if('.$rule.') $required=1;';
			eval($rule);
			if($required) $page_fields['required']++;
			if($required && $this->fields[$field['field_name']]['field_value']!='')
			{
				$page_fields['complete']++;
			}//end if
		}//end foreach
		return $page_fields;
	}//end computePageStatus
	
/**
  *computeSectionStatus
  *
  *gets the status of a section on the page
  *
  *@access public
  *@param sting section_id
  *@return string
  */
  
	function computeSectionStatus($section_id)
	{
		$section_fields=array('required'=>0,'complete'=>0);
		foreach($this->sections['section_'.$section_id]['pages'] as $page_code)
		{
			$page_fields=$this->computePageStatus($this->pages[$page_code]['id']);
			$section_fields['required']+=$page_fields['required'];
			$section_fields['complete']+=$page_fields['complete'];
		}//end foreach
		return $section_fields;
	}//end computePageStatus
/**
  *debug
  *
  *provides debuging information
  *
  *@access public
  *@param interger $val
  */
	function debug($val)
	{
		if($val==1) $this->db->debug=$val;
		$this->debug=$val;
	}//end debug
/**
  *deleteFile
  *
  *delets a given file from the database
  *
  *@access public
  *@param string $user_id
  *@param string $file_code
  *@param string $app_id
  */
	function deleteFile($user_id,$file_code,$app_id='')
	{
		$app_id=($app_id)?$app_id:$this->id;
		$sql="DELETE FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id";
		$this->db->Execute($sql);
	}//end deleteFile
/**
  *deleteRepeatingFields
  *
  *Looks through the database and cleans out repeating fields
  *
  *@access public
  *@param string $repeating_code
  *@param string $repeating_id
  *@param string $app_id
  */
	function deleteRepeatingFields($repeating_code,$repeating_id,$app_id='')
	{
		if(is_array($this->repeating_fields[$repeating_code][$repeating_id]))
		{
			foreach($this->repeating_fields[$repeating_code][$repeating_id] as $key=>$field)
			{
				$sql="DELETE FROM ".(($app_id)?$this->table['submit_value']:$this->table['field_value'])." WHERE user_id={$this->user_id} AND app_field='$key' AND repeating_id=$repeating_id ".(($app_id)?" AND application_id='$app_id'":"");
				$this->db->Execute($sql);
			}//end foreach
		}//end if
	}//end deleteRepeating
/**
  *getApplications
  *
  *Loads applications from the db so the page can load them
  *
  *@access public
  *@param string $user_id
  *@return string
  */
	function getApplications($user_id)
	{
		$data=array();
		$sql="SELECT * FROM app_user_application WHERE user_id=$user_id";
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[]=$row;
			}//end while
		}//end if
		
		return $data;
	}//end getApplications
/**
  *getFields
  *
  *returns all fields information in an array
  *
  *@access public
  *@param string $user_id
  *@param boolean $values_only
  *@param string $app_id
  *@return array
  */
	function getFields($user_id,$values_only=false,$app_id='')
	{
		$sql="SELECT * FROM ".(($app_id)?$this->table['submit_value']:$this->table['field_value'])." WHERE user_id=$user_id ".(($app_id)?" AND application_id='$app_id'":"")." ORDER BY app_field";

		$data=array();
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				
				$value = ($values_only)?$row['field_value']:$row;


				if(!isset($data[$row['app_field']]['user_id']) && is_array($data[$row['app_field']]))
				{
					$data[$row['app_field']][] = $value;
				}
				elseif(isset($data[$row['app_field']]))
				{
					$temp = $data[$row['app_field']];
					$data[$row['app_field']] = array($temp, $value);
				}
				else
				{
					$data[$row['app_field']]=$value;
				}
			}//end while
		}//end if
		
		return $data;
	}//end getFields
/**
  *getFile
  *
  *returns a file with the given params
  *
  *@access public
  *@param string $user_id
  *@param string $app_id
  *@param boolean $data
  *@return string 
  */
	function getFile($user_id,$file_code,$app_id,$data=false)
	{
		if($data)
			return $this->db->GetOne("SELECT file_data FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id");
		else
			return PSUTools::cleanKeys('','',$this->db->GetRow("SELECT user_id,file_code,file_type,file_name,application_id FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id"));
	}//end getFile
/**
  *getNextPage
  *
  *returns the next page
  *
  *@access public
  *@param array $current
  *@return string
  */
	function getNextPage($current)
	{
		//find the current page in the list of pages
		$current_index=array_search($current,$this->page_order);

		//return the next page
		if($current_index<count($this->page_order)-1)
			return $this->pages[$this->page_order[$current_index+1]];
		else
			return '';
	}//end getNextPage
/**
  *getOrderedPageCodes
  *
  *returns the order for the page code in an array
  *
  *@access public
  *@param boolean $section
  *@return array
  */
	function getOrderedPageCodes($section=false)
	{
		if($section!==false)
			$where=" AND p.section_id=$section";

		$sql="SELECT p.code FROM {$this->table['page']} p,{$this->table['section']} s WHERE p.section_id=s.id AND s.application_id={$this->master_app} {$where} ORDER BY section_order,page_order";

		$data=array();
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[]=$row['code'];
			}//end while
		}//end if
		
		return $data;
	}//end getOrderedPageCodes
/**
  *getPages
  *
  *gets all the information about all the relative pages and returns an array with it
  *
  *@access public
  *@return array
  */
	function getPages()
	{
		$data=array();
		if($results=$this->db->Execute("SELECT * FROM {$this->table['page']} ORDER BY page_order,name"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[$row['code']]=$row;
			}//end while
		}//end if
		
		return $data;
	}//end getPages
/**
  *getPageSection
  *
  *returns the url for the selected page
  *
  @@access public
  *@param string $page_code
  *@return string
  */
	function getPageSection($page_code)
	{
		return $this->db->GetOne("SELECT section_id FROM {$this->table['page']} WHERE code='$page_code'");
	}//end getPageSection
/**
  * getPercentageBar
  *
  *returns a percentage bar with the given text
  *
  *@acces public
  *@param string $text
  *@return string
  */
	function getPercentageBar($text='')
	{
		if($text=='%')
			$text=$this->computeApplicationPercentage().'%';
		$string='<div class="percent_bar"><div class="fill" style="width:'.$this->computeApplicationPercentage().'%;">'.$text.'</div></div>';
		return $string;
	}//end getPercentageBar
/**
  * getPreviousPage
  *
  * gets the url for the previous page and returns it
  *
  *@access public
  *@param string $page_code
  *@return string
  */
	function getPreviousPage($page_code)
	{
		//find the current page in the list of pages
		$current_index=array_search($page_code,$this->page_order);

		//return the previous page
		if($current_index>0)
			return $this->pages[$this->page_order[$current_index-1]];
		else
			return '';
	}//end getPreviousPage
/**
  *getProgramDetail
  *
  *gets the detailed information about the code passed in
  *
  *@access public
  *@param string $code
  *@return string
  *
  */
	function getProgramDetail($code)
	{
		return PSUTools::cleanKeys('','',$this->db->GetRow("SELECT * FROM {$this->table['program']} WHERE code='$code'"));
	}//end getProgramDetail

	function getPrograms()
	{
		if($this->programs=='')
		{
			$data=array();
			if($results=$this->db->Execute("SELECT * FROM {$this->table['program']} ORDER BY name"))
			{
				while($row=$results->FetchRow())
				{
					$row=PSUTools::cleanKeys('','',$row);
					$data[$row['code']]=$row;
				}//end while
			}//end if
			$this->programs=$data;
		}//end if

		return $this->programs;
	}//end getPrograms
/**
  *getRequiredFields
  *
  *returns all the information for the required fields provieded by the set database
  *
  *@access public
  *@param string $page_id
  *@return array
  */
	function getRequiredFields($page_id)
	{
		$data=array();
		if($results=$this->db->Execute("SELECT * FROM {$this->table['field']} WHERE page_id=$page_id AND required_rule_id IS NOT NULL"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[$row['field_name']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getRequiredFields
/**
  *getRepeatingFields
  *
  *retrieves the repeating fields in the database
  *
  *@access public
  *@param string $app_id
  *@return array
  */
	function getRepeatingFields($app_id='')
	{
		$data=array();
		if($results=$this->db->Execute("SELECT r.repeating_code,v.app_field,v.field_value,v.field_status,v.field_comments,v.repeating_id FROM {$this->table['repeating_field']} r,".(($app_id)?$this->table['submit_value']:$this->table['field_value'])." v WHERE r.id=v.repeating_id AND r.user_id=v.user_id AND r.user_id=".$this->user_id." ".(($app_id)?" AND v.application_id='$app_id'":"")." ORDER BY r.repeating_code,r.repeating_order"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[$row['repeating_code']][$row['repeating_id']][$row['app_field']]=$row;
			}//end while
		}//end if
		return $data;
	}//end getRepeatingFields
/**
  *getRequiredRule
  *
  *returns required rule for the given id
  *
  *@access public
  *@param string $rule_id
  *@return string
  */
	function getRequiredRule($rule_id)
	{
		return $this->db->GetOne("SELECT rule FROM {$this->table['required_rule']} WHERE id=$rule_id");
	}//end getRequiredRule
/**
  *getSection
  *
  *returns sections that was requested
  *
  *@access public
  *@return mixed
  */
	function getSections()
	{
		$data=array();
		if($results=$this->db->Execute("SELECT * FROM {$this->table['section']} WHERE application_id={$this->master_app} ORDER BY section_order,name"))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$row['pages']=$this->getOrderedPageCodes($row['id']);
				$data['section_'.$row['id']]=$row;
			}//end while
		}//end if
		
		return (count($data)>0)?$data:false;
	}//end getSections
/**
  *saveField
  *
  *attempts to save the passed in field to the database and returns a boolean according to its success or failure
  *
  *@acces public
  *@param string $user_id
  *@param string $field
  *@param integer @value
  *@param integer $repeating
  *@param integer $status
  *@param boolean $comments
  *@return boolean
  */
	function saveField($user_id,$field,$value,$repeating_id=0,$status=0,$comments=false)
	{
		$field_exists=$this->db->GetOne("SELECT 1 FROM {$this->table['field_value']} WHERE user_id=$user_id AND app_field='$field' AND repeating_id=$repeating_id");
		if($field_exists)
		{
			$sql="UPDATE {$this->table['field_value']} SET field_value='$value',activity_date=sysdate,repeating_id=$repeating_id WHERE user_id=$user_id AND app_field='$field'";
			if($this->db->Execute($sql))
			{
				return true;
			}//end if
			else return false;
		}//end if
		else
		{
			$sql="INSERT INTO {$this->table['field_value']} (user_id,app_field,field_value,field_status,activity_date,repeating_id) VALUES ($user_id,'$field','$value',0,sysdate,$repeating_id)";
			if($this->db->Execute($sql))
			{
				return true;
			}//end if
			else return false;
		}//end else
	}//end saveField
/**
  *saveFile
  *
  *Saves paseed in file to the correct db
  *
  *@access public
  *@param string $user_id
  *@param string $file_code
  *@param string $file_type
  *@param string $file_name
  *@param string $app_id
  */
	function saveFile($user_id,$file_code,$file_type,$file_name,$app_id)
	{
		if(!$this->db->GetOne("SELECT 1 FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id"))
		{
			$sql="INSERT INTO {$this->table['file']} (user_id,file_code,file_type,file_name,file_data,application_id) VALUES ($user_id,'$file_code','$file_type','$file_name',EMPTY_BLOB(),$app_id)";
			$this->db->Execute($sql);
			$this->db->UpdateBlob($this->table['file'],'file_data',file_get_contents($_FILES[$file_code]['tmp_name']),"user_id=$user_id AND file_code='$file_code' AND application_id=$app_id");
		}//end
	}//end saveFile
/**
  *setApplication
  *
  *sets application specific vars to a specific applications information 
  *
  *@access public
  *@param string $id
  *@param string @user_id
  */
	function setApplication($id='',$user_id='')
	{
		if($user_id) $this->setUser($user_id);
		if($id)
		{
			$app=$this->db->GetRow("SELECT * FROM {$this->table['application']} WHERE user_id={$this->user_id} AND id=$id");
		}//end if
		else
		{
			$app=$this->db->GetRow("SELECT * FROM {$this->table['application']} WHERE user_id={$this->user_id} AND application_status='U'");
		}//end else
		$app=PSUTools::cleanKeys('','',$app);
		if(count($app)>0)
		{
			$this->id=$id;
			if($app['application_status']!='U')
			{
				$this->fields=$this->getFields($this->user_id,false,$this->id);
				$this->field_values=$this->getFields($this->user_id,true,$this->id);
				$this->repeating_fields=$this->getRepeatingFields($this->id);
				$this->buildFriendlyPhone();
			}//end if
		}//end if
	}//end setApplication
/**
  *setCurrentPage
  *
  *sets the current page specific information to the class variables
  *
  *@access public
  *@param string $page_code
  */
	function setCurrentPage($page_code)
	{
		$this->current_page=$page_code;
		$this->current_section=$this->getPageSection($this->current_page);
		$this->next_page=$this->getNextPage($this->current_page);
		$this->previous_page=$this->getPreviousPage($this->current_page);
	}//end setCurrentPage
/**
  *setUser
  *
  *sets current user specific information to the class variables
  *
  *@access public
  *@param string $user_id
  */
	function setUser($user_id)
	{
		$this->user_id=$user_id;
		$this->fields=$this->getFields($this->user_id);
		$this->field_values=$this->getFields($this->user_id,true);
		$this->repeating_fields=$this->getRepeatingFields();
		$this->buildFriendlyPhone();
	}//end setUser

}//end PSPApplication

?>
