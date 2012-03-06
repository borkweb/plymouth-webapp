<?php
require_once('PSUTools.class.php');

class OnlineForm
{
	var $db;					//Database Connection
	var $form;					
	var $form_name;				//Name of this $master_form_id
	var $sections;				//Section info (order, pages, etc.)
	var $master_form;
	var $pages;					//Page info (parent section, name, code, etc.)
	var $page_order;			//Page codes and order
	var $form_version;			//Version info of current form, or most recent version if multiple versions exist
	var $table=array(			//DB Table Names
		'user_form'=>'psu.form_user_form',
		'page'=>'psu.form_page',
		'section'=>'psu.form_section',
		'section_page'=>'psu.form_section_page',
		'field'=>'psu.form_field',
		'page_field'=>'psu.form_page_field',
		'master_form'=>'psu.form',
		'version'=>'psu.form_version',
		'field_value'=>'psu.form_user_value',
		'required_rule'=>'psu.form_required_rule',
		'validation'=>'psu.form_validation',
		'repeating_group'=>'psu.form_repeating_group',
		'repeating_field'=>'psu.form_repeating_field',
		'repeating_value'=>'psu.form_repeating_value'
	);
	var $percent='';					//Percentage of form completed
	var $fields=array();				//Submitted field values for this form and all other field info
	var $field_values=array();			//Submitted values only for this form
	var $previous_fields=array();		//Field information from the user's previous form submission
	var $repeating_fields=array();		//Field info for fields that are part of a repeating group
	var $prev_repeating_fields=array();	//Field info from user's previous form submission for fields that are part of a repeating group
	var $error;
	var $url_prefix;
	var $view_only=false;

/**
  *OnlineForm
  *
  *sets many of the class specific variables for local use
  *
  *@param string $db
  *@param string $master_form
  *@param string $url_pre
  *@param string $current_page
  */
  
	function OnlineForm(&$db,$master_form='',$url_pre='',$current_page='')
	{
		$this->db=$db;
		$this->master_form=$master_form;
		$this->url_prefix=$url_pre;
		
		$this->form_name=$this->getFormName();
		$this->sections=$this->getSections();
		$this->pages=$this->getPages();
		$this->page_order=$this->getOrderedPageCodes();
		
		if($master_form)
			$this->form_version=$this->getFormVersion();

		if($current_page && $current_page!='unknown' && $this->viewOnly==false)
			$this->setCurrentPage($current_page);
		elseif($current_page && $current_page=='unknown' && $this->viewOnly==false)
		{	
			$this->setCurrentPage($this->page_order[0]);
		}//end elseif
	}//end OnlineForm
/**
  *buildURL
  *
  *constructs a url for the passed in page
  *
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
  *checkSubmitStatus
  *
  *checks if all of the pages where submited properly
  *
  *@return boolean
  */
	function checkSubmitStatus()
	{
		$num_sections=count($this->sections);
		$sections_complete=0;
		foreach($this->sections as $key=>$section)
		{
			$section_complete=$this->computeSectionStatus($section['id']);
			if($section_complete['required']==$section_complete['complete'])
				$sections_complete++;
		}//end foreach
		if($sections_complete==$num_sections)
			return true;
		elseif($sections_complete<$num_sections)
			return false;
	}//end checkSubmitStatus
/**
  *checkUserForm
  *
  *checks if the current user form is correct
  *
  *@param string $id
  *@param string $user_id
  *@param integer $days_to_complete
  *@param string $seasonal
  *@return mixed
  */
	function checkUserForm($id,$user_id,$days_to_complete=0,$seasonal)
	{
		if($days_to_complete > 0)
			$where=" AND sysdate - uf.start_date < $days_to_complete";
		if($seasonal)
			$where.=" AND uf.start_date > to_date('{$this->form_version['start_date']}','YYYY-MM-DD HH24:MI:SS')";

		$results=$this->db->Execute("SELECT uf.* FROM {$this->table['user_form']} uf, dual d WHERE uf.pidm=$user_id AND uf.id=$id AND uf.master_form_id={$this->master_form} AND uf.form_status='U' {$where}");
		if($row=$results->FetchRow())
			return $row;
		else
			return false;
	}//end checkUserForm
/**
  *checkVersion
  *
  *checks the version of the current users form
  *
  *@param string $user_id
  *@param string $user_form_id
  *@return mixed
  */
	function checkVersion($user_id,$user_form_id='')
	{
		if($user_form_id)
			$where=" AND id=$user_form_id";
		
		$sql="SELECT * FROM {$this->table['user_form']} WHERE master_form_id={$this->master_form} AND pidm={$user_id} AND start_date > '{$this->form_version['start_date']}' {$where} ORDER BY start_date DESC";

		$results=$this->db->Execute($sql);
		if($row=$results->FetchRow())
		{
			$row=PSUTools::cleanKeys('','',$row);
			if($row['form_status']!='U')
				return $row;
			else
				return false;
		}//end if
		else
			return false;
	}//end checkVersion

	/**
	 * computePageStatus
	 * 
	 * figures out hoe many page fields successfully loaded
	 * 
	 * @param string $page_id
	 * @return string array
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
			if(isset($this->fields[$field['field_name']]))
			{
				if($required && $this->fields[$field['field_name']]['field_value']!='')
				{
					$page_fields['complete']++;
				}//end if
			}//end if
			elseif(isset($this->repeating_fields[$field['field_name']]))
			{
				if($required)
				{
					foreach($this->repeating_fields[$field['field_name']] as $disp_order=>$field_data)
					{
						if($field_data['field_value']!='')
						{
							$page_fields['complete']++;
							break;
						}//end if
					}//end foreach
				}//end if
			}//end elseif	
		}//end foreach
		return $page_fields;
	}//end computePageStatus

	/**
	 * computeSectionStatus
	 * 
	 * figures out how much items of a section loaded
	 *
	 * @param string $section_id
	 * @return string
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
	}//end computeSectionStatus
/**
  *createUserForm
  *
  *gets and retursn the necessary information for a user form
  *
  *@param string $user_id
  *@return string
  */
	function createUserForm($user_id)
	{
		$new_id=$this->db->Execute("INSERT INTO {$this->table['user_form']} (master_form_id, pidm) VALUES ({$this->master_form}, $user_id)");	
		if($user_form=$this->db->GetRow("SELECT * FROM {$this->table['user_form']} WHERE pidm=$user_id AND master_form_id={$this->master_form} ORDER BY start_date DESC"))
			return $user_form;
	}//end createUserForm
/**
  *findUserForm
  *
  *retrieves a form partially or wholey filled out
  *
  *@param string $user_id
  *@return string
  */
	function findUserForm($user_id,$days_to_complete,$seasonal)
	{
		$form=false;
		if($days_to_complete > 0)
			$where=" AND sysdate - uf.start_date < $days_to_complete";
		
		if($seasonal)
			$form=$this->checkVersion($user_id);	

		//Retrieve the most recently started unsubmitted entry (started within specified time, if given), or create a new entry
		if($form)
			return $form;
		else
		{	
			$results=$this->db->Execute("SELECT uf.* FROM {$this->table['user_form']} uf, dual d WHERE uf.pidm={$user_id} AND uf.master_form_id={$this->master_form} AND uf.form_status='U' {$where} ORDER BY uf.start_date DESC");
			if($form=$results->FetchRow())
			{
				//$discovered_id=true;
				$form=PSUTools::cleanKeys('','',$form);
				return $form;
			}//end if
			else
			{
				$form=$this->createUserForm($user_id);
				return $form;
			}//end else
		}//end else
	}//end findUserForm
/**
  *getFields
  *
  *returns the form fields
  *
  *@param string $user_id
  *@param mixed $values_only
  *@param string $user_form_id
  *@return string
  */
	function getFields($user_id,$values_only=false,$user_form_id='')
	{
		if(!$user_form_id)
			$sql="SELECT fv.*, f.field_name FROM {$this->table['field_value']} fv, {$this->table['user_form']} uf, {$this->table['field']} f WHERE uf.pidm=$user_id AND fv.user_form_id='{$this->id}' AND fv.user_form_id=uf.id AND fv.field_id=f.id ORDER BY f.field_name";
		else
			$sql="SELECT fv.*, f.field_name FROM {$this->table['field_value']} fv, {$this->table['user_form']} uf, {$this->table['field']} f WHERE uf.pidm=$user_id AND fv.user_form_id=$user_form_id AND fv.user_form_id=uf.id AND fv.field_id=f.id ORDER BY f.field_name";	

		$data=array();

		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);

				$value = ($values_only)?$row['field_value']:$row;
				// Here, $value could either equal the user's submitted info (e.g. 'Maziarz') or it could be the entire row array 
				// (with each key a column name, and each value being the data in that row/column)
				
				//if this field is part of a repeating group
				if(!$row['repeating_value_id'])
				{
					$data[$row['field_name']]=$value;
				}//end if
			}//end while
		}//end if		
		return $data;
	}//end getFields	
/**
  *getForms
  *
  *retrievs a compilation of all the forms
  *
  *@param string $string
  *@return mixed
  */
	function getForms($field='*')
	{	
		$data=array();
	
		if($field=='*' || $field=='')
		{
			$sql="SELECT f.* FROM {$this->table['master_form']} f, {$this->table['version']} v WHERE f.master_form_id=v.master_form_id AND v.end_date IS NULL ORDER BY f.name";
		}
		else
		{
			$sql="SELECT f.$field FROM {$this->table['master_form']} f, {$this->table['version']} v WHERE f.master_form_id=v.master_form_id AND v.end_date IS NULL ORDER BY f.name";
		}

		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[]=$row;
			}//end while
		}//end if
		
		return (count($data)>0)?$data:false;
	}//end getForms

/**
  *getFormName
  *
  *returns the name of the db form form
  *
  *@return mixed
  */
	function getFormName()
	{
		return $this->db->GetOne("SELECT name FROM {$this->table['master_form']} WHERE master_form_id={$this->master_form}");
	}//end getFormName

	/**
	 * getFormVersion
	 * 
	 * gets the version of the current form
	 * 
	 * @return mixed
	 */
	function getFormVersion()
	{
		$row = $this->db->GetRow("SELECT v.* FROM {$this->table['version']} v, {$this->table['master_form']} f WHERE v.master_form_id=f.master_form_id AND v.master_form_id={$this->master_form} AND v.end_date IS NULL ORDER BY v.start_date DESC");
		$row = PSUTools::cleanKeys('','',$row);
		return $row;
	}//end getFormVersion

/**
  *getNextPage
  *
  *searches for and returns the selected page info
  *
  *@param string $page_code
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
  *getOrderedPageCodes
  *
  *returns all the page code in order
  *
  *@param mixed $section
  *@return string array
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
  *returns all the page information for the form
  *
  *@return string array
  */
	function getPages()
	{
		$data=array();
		
		if($results=$this->db->Execute("SELECT p.* FROM {$this->table['page']} p, {$this->table['section_page']} sp, {$this->table['section']} s WHERE p.master_form_id={$this->master_form} AND p.id=sp.page_id AND s.master_form_id={$this->master_form} AND s.id=sp.section_id AND p.id IN (SELECT DISTINCT page_id FROM {$this->table['section_page']} WHERE end_date IS NULL) ORDER BY s.section_order, p.page_order, p.name"))
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
  *returns the page selection
  *
  *@return mixed
  */
	function getPageSection($page_code)
	{
		return $this->db->GetOne("SELECT sp.section_id FROM {$this->table['section_page']} sp,{$this->table['page']} p WHERE p.code='$page_code' AND sp.page_id=p.id AND p.master_form_id={$this->master_form} AND sp.end_date IS NULL");
	}//end getPageSection
/**
  *getPreviousPage
  *
  *returns the previous page information using the passed in page as the current
  *
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
  *getPreviousSubmission
  *
  *gets saved information on the form
  *
  *@param string $user_id
  *@param string $user_form_id
  *@return mixed
  */
	function getPreviousSubmission($user_id,$user_form_id='')
	{
		if($user_form_id)
			$where=" AND id<$user_form_id";

		return $this->db->GetOne("SELECT id FROM {$this->table['user_form']} WHERE pidm=$user_id AND master_form_id={$this->master_form} AND form_status<>'U' {$where} ORDER BY submit_date DESC, id DESC");
	}
/**
  *getReoeatingFields
  *
  *returns any fields that are repeating
  *
  *@param string $user_id
  *@param string $user_form_id
  *@return string
  */
	function getRepeatingFields($user_id,$user_form_id='')
	{
		$data=array();
		
		if(!$user_form_id)
			$sql="SELECT uv.*, f.field_name, rv.display_order FROM {$this->table['field_value']} uv, {$this->table['field']} f, {$this->table['repeating_value']} rv, {$this->table['user_form']} uf WHERE uf.pidm=$user_id AND uv.user_form_id={$this->id} AND uv.user_form_id=uf.id AND uv.field_id=f.id AND uv.repeating_value_id=rv.id ORDER BY f.field_name, rv.display_order";
		else
			$sql="SELECT uv.*, f.field_name, rv.display_order FROM {$this->table['field_value']} uv, {$this->table['field']} f, {$this->table['repeating_value']} rv, {$this->table['user_form']} uf WHERE uf.pidm=$user_id AND uv.user_form_id=$user_form_id AND uv.user_form_id=uf.id AND uv.field_id=f.id AND uv.repeating_value_id=rv.id ORDER BY f.field_name, rv.display_order";
		
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[$row['field_name']][$row['display_order']]=$row;
			}//end while
		}//end if

		return $data;
	}//end getRepeatingFields
/**
  *getRepeatingGroupFields
  *
  *repeating group fields are returned from the repeating group ids
  *
  *@param string $group_id_in
  *@param string $in_type
  *@param string $out_type
  *@return mixed
  */
	function getRepeatingGroupFields($group_id_in,$in_type='repeating_group_id',$out_type='field_name')
	{	
		/*
			in types:
				repeating_group_id
				repeating_group_name
			
			out types:
				field_name
				field_id
		*/

		$from='';

		if($out_type=='field_name')
		{
			$select="f.field_name";
			$column='field_name';
		}//end if
		elseif($out_type=='field_id')
		{
			$select="rf.field_id";
			$column='field_id';
		}//end elseif
		else
			return false;;
		
		if($in_type=='repeating_group_id')
			$where="rf.repeating_group_id=$group_id_in";
		elseif($in_type=='repeating_group_name')
		{
			$from="{$this->table['repeating_group']} rg";
			$where="rg.group_name='$group_id_in' AND rf.repeating_group_id=rg.id";
		}//end elseif
		else
			return false;

		$sql="SELECT ".$select." FROM {$this->table['repeating_field']} rf, {$this->table['field']} f, {$this->table['page_field']} pf {$from} WHERE ".$where." AND f.id=rf.field_id AND pf.field_id=f.id AND pf.end_date IS NULL ORDER BY f.required_rule_id ASC";

		if($res=$this->db->Execute($sql))
		{
			while($row=$res->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$data[]=$row[$column];
			}//end while
			return $data;
		}//end if
		else
			return false;
	}//end getRepeatingGroupFields
/**
  *getRequiredFields
  *
  *returns the requrired feields for marking
  *
  *@param string $page_id
  *@return string
  */
	function getRequiredFields($page_id)
	{
		$data=array();
		if($results=$this->db->Execute("SELECT f.* FROM {$this->table['field']} f, {$this->table['page']} p, {$this->table['page_field']} pf WHERE p.id=$page_id AND p.master_form_id={$this->master_form} AND pf.page_id=p.id AND f.master_form_id={$this->master_form} AND f.required_rule_id IS NOT NULL AND pf.field_id=f.id AND pf.end_date IS NULL"))
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
  *getRequiredRule
  *
  *returns a rules required for the fillng in of a field
  *
  *@param string $rule_id
  *@return mixed
  */
	function getRequiredRule($rule_id)
	{
		return $this->db->GetOne("SELECT rule FROM {$this->table['required_rule']} WHERE id=$rule_id");
	}//end getRequiredRule
/**
  *getSections
  *
  *returns the different section breakdowns
  *
  *@return mixed
  */
	function getSections()
	{
		$data=array();
		
		if($results=$this->db->Execute("SELECT * FROM {$this->table['section']} WHERE master_form_id={$this->master_form} AND id IN (SELECT DISTINCT section_id FROM {$this->table['section_page']} WHERE end_date IS NULL) ORDER BY section_order,name"))
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
  *saves the insert information 
  *
  *@param string $user_form_id
  *@param string $field
  *@param integer $value
  *
  */
	function saveField($user_form_id,$field,$value)
	{
		$field_exists=$this->db->GetOne("SELECT 1 FROM {$this->table['field_value']} WHERE user_form_id=$user_form_id AND field_id=$field");
		if($field_exists)
		{
			$sql="UPDATE {$this->table['field_value']} SET field_value='$value',activity_date=sysdate WHERE user_form_id=$user_form_id AND field_id=$field";
			if($this->db->Execute($sql))
			{
				return true;
			}//end if
			else return false;
		}//end if
		else
		{
			$sql="INSERT INTO {$this->table['field_value']} (user_form_id,field_id,field_value,field_status,activity_date) VALUES ($user_form_id,$field,'$value','P',sysdate)";
			if($this->db->Execute($sql))
			{
				return true;
			}//end if
			else return false;
		}//end else
	}//end saveField
/**
  *saveRepeatingField
  *
  * saves the input of a field that repeats for user convience
  *
  *@param string $user_form_id
  *@param string $field
  *@param integer $value
  *@param string $repeating_val_id
  *@return boolean
  */
	function saveRepeatingField($user_form_id,$field,$value,$repeating_val_id)
	{	
		$sql="INSERT INTO {$this->table['field_value']} (user_form_id,field_id,field_value,field_status,activity_date,repeating_value_id) VALUES ($user_form_id,$field,'$value','P',sysdate,$repeating_val_id)";
		if($this->db->Execute($sql))
		{
			return true;
		}//end if
		else 
			return false;
	}//end saveRepeatingField
/**
  *saveFields
  *
  *saves all the filled out fields
  *
  *@param string $user_form_id
  *@param string $info
  *
  */
	function saveFields($user_form_id,&$info)
	{
		foreach($info as $key=>$field)
		{
			if(is_array($field))
			{				
				$repeating_group_id=$this->db->GetOne("SELECT DISTINCT rg.id FROM {$this->table['repeating_group']} rg, {$this->table['repeating_field']} rf, {$this->table['field']} f WHERE rg.group_name='$key' AND rg.id=rf.repeating_group_id AND rf.field_id=f.id AND f.master_form_id={$this->master_form}");
				$fields_in_group=$this->getRepeatingGroupFields($repeating_group_id);
				$num_fields_in_group=count($fields_in_group);
				$used_disp_orders=array();
					
				//handle the fields one submit/display group at a time
				foreach($field as $group=>$f_name)
				{
					$save_data=false;
					//$num_fields_submitted=count($f_name);
					
					//check to see if any of the fields being submitted have already been submitted (regardless of their values)
					foreach($fields_in_group as $rep_field)
					{
						//automatically save the submitted fields if they haven't been submitted yet
						if(!isset($this->repeating_fields[$rep_field]))
						{
							$save_data=true;
						}//end if
					}//end foreach

					//if these fields have already been submitted
					if($save_data===false)
					{
						//organize the previously submitted fields by submission group/display order
						$display_group=array();
						foreach($fields_in_group as $rep_field)
						{
							foreach($this->repeating_fields[$rep_field] as $disp_order=>$field_data)
							{
								$display_group[$disp_order][$rep_field]=$field_data['field_value'];
							}//end foreach
						}//end foreach

						//check each previously submitted display group to see if all those values match all the submitted values exactly...
						$num_matching_groups=0;
						$num_null_groups=0;
						foreach($display_group as $d_order=>$d_order_fields)
						{
							$values_match=0;
							$null_values=0;
							foreach($f_name as $field_name=>$field_value)
							{
								$field_value=stripslashes($field_value);
								if($field_value=='')
									$null_values++;
								if($display_group[$d_order][$field_name]==$field_value)
									$values_match++;
							}//end foreach
							if($null_values==$num_fields_in_group)
								$num_null_groups++;

							//...and tally those that match
							if($values_match==$num_fields_in_group)
								$num_matching_groups++;
						}//end foreach

						//if there are no groups of fields from previous form submits that have identical values to the values currently being submitted, save the values in the current submission
						if($num_matching_groups===0 && $num_null_groups===0)
							$save_data=true;
					}//end if
						
					if($save_data)
					{
						$use_prev_record=false;
						$disp_order_found=array();
						reset($fields_in_group);
						foreach($fields_in_group as $rep_field)
						{
							$num_grp_fields_saved=0;
							$num_grp_fields_saved=count($this->repeating_fields[$rep_field]);
							if($num_grp_fields_saved>0)
							{
								for($i=1;$i<=$num_grp_fields_saved;$i++)
								{
									if(!isset($disp_order_found[$i]))
										$disp_order_found[$i]=false;
									$disp_order_exists=array_key_exists($i,$this->repeating_fields[$rep_field]);
									if($disp_order_exists!==false)
										$disp_order_found[$i]=true;
								}//end for
							}//end if
						}//end foreach
						foreach($disp_order_found as $disp_order=>$found)
						{
							if($found===false && !in_array($disp_order,$used_disp_orders))
							{
								$use_prev_record=$this->db->GetOne("SELECT id FROM {$this->table['repeating_value']} WHERE user_form_id=$user_form_id AND repeating_group_id=$repeating_group_id AND display_order=$disp_order");
								$used_disp_orders[]=$disp_order;
								break;
							}//end if
						}//end foreach
						
						if($use_prev_record!==false)
							$rep_val_id=$use_prev_record;
						else
						{
							$prev_record=$this->db->Execute("SELECT display_order FROM {$this->table['repeating_value']} WHERE repeating_group_id=$repeating_group_id AND user_form_id=$user_form_id ORDER BY display_order DESC");
							if($row=$prev_record->FetchRow())
							{
								$row=PSUTools::cleanKeys('','',$row);
								$new_rep_order=$row['display_order']+1;
								$this->db->Execute("INSERT INTO {$this->table['repeating_value']} (repeating_group_id, display_order, user_form_id) VALUES ($repeating_group_id, $new_rep_order, $user_form_id)");
							}//end if
							else
								$this->db->Execute("INSERT INTO {$this->table['repeating_value']} (repeating_group_id, display_order, user_form_id) VALUES ($repeating_group_id, 1, $user_form_id)");

							$rep_val_id=$this->db->GetOne("SELECT id FROM {$this->table['repeating_value']} WHERE user_form_id=$user_form_id AND repeating_group_id=$repeating_group_id ORDER BY display_order DESC");
						}//end else
						
						foreach($f_name as $field_name=>$field_value)
						{
							$this_field=$this->db->GetRow("SELECT id, field_type FROM {$this->table['field']} WHERE field_name='$field_name' AND master_form_id={$this->master_form}");
							$this_field=PSUTools::cleanKeys('','',$this_field);
							$field_value=stripslashes($field_value);
							$field_value=PSUTools::cleanOracle($field_value);
							$this->saveRepeatingField($user_form_id,$this_field['id'],$field_value,$rep_val_id);
						}//end foreach
					}//end if
				}//end foreach($field as $group=>$f_name)
			}//end if(is_array($field))
			elseif($this->fields[$key]['field_value']!=$field)
			{
				$this_field=$this->db->GetRow("SELECT id, field_type FROM {$this->table['field']} WHERE field_name='$key' AND master_form_id={$this->master_form}");
				$this_field=PSUTools::cleanKeys('','',$this_field);
				$field=stripslashes($field);
				$field=PSUTools::cleanOracle($field);
				$this->saveField($user_form_id,$this_field['id'],$field);
			}//end elseif
		}//end foreach	
	}//end saveFields
/**
  *setCurrentPage
  *
  * set the current page to the passed in value
  *
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
  *setForm
  *
  * sets the form to new status
  *
  *@param string $id
  *@param integer $days_to_complete
  *@param boolean $seasonal
  *@param string $user_id
  */
	function setForm($id='',$days_to_complete=0,$seasonal=true,$user_id='')
	{
		$discovered_id=false;
		if(!$user_id)
		{
			$user_id=$this->user_id;
			//$this->error.='no user_id passed into setForm. user_id is now '.$user_id.' ';
		}//end if

		if($id)
		{
			$form=$this->checkUserForm($id,$user_id,$days_to_complete,$seasonal);
			if(!$form)
				$form=$this->findUserForm($user_id,$days_to_complete,$seasonal);
		}//end if
		else
		{	
			$form=$this->findUserForm($user_id,$days_to_complete,$seasonal);
		}//end else
		
		$form=PSUTools::cleanKeys('','',$form);
		if(count($form)>0)
		{
			$this->id=$form['id'];
			$this->status=$form['form_status'];
			$this->percent='';
		}//end if

		if($previous_submission=$this->getPreviousSubmission($user_id,$this->id))
			$this->previous_submission_id=$previous_submission;
		if($this->status!="U")
			$this->view_only=true;
		
		$this->setUser($user_id);
	}//end setForm
/**
  *setUser
  *
  * changes the local variables to decribed the current user
  *
  *@param string $user_id
  */
	function setUser($user_id)
	{
		$this->user_id=$user_id;
		$this->fields=$this->getFields($this->user_id);
		$this->field_values=$this->getFields($this->user_id,true);
		$this->repeating_fields=$this->getRepeatingFields($this->user_id);

		if($this->previous_submission_id)
		{
			$this->previous_fields=$this->getFields($this->user_id,false,$this->previous_submission_id);
			$this->prev_repeating_fields=$this->getRepeatingFields($this->user_id,$this->previous_submission_id);
		}//end if
	}//end setUser
/**
  *submitForm
  *
  *updates all the fields for the current users form
  *
  *@param string $user_form_id
  *@return string
  */
	function submitForm($user_form_id)
	{
		$can_submit=$this->checkSubmitStatus();
		if($can_submit)
		{
			$this->db->Execute("UPDATE {$this->table['field_value']} SET field_status='S' WHERE user_form_id=$user_form_id");
			$this->db->Execute("UPDATE {$this->table['user_form']} SET form_status='P', submit_date=sysdate WHERE id=$user_form_id");
			return 'success';
		}//end if
		else
			return 'incomplete';
	}//end submitForm

}//end OnlineForm
