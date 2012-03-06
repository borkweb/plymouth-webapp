<?php
require_once('PSUTools.class.php');

class PSPApplication
{
	var $db;
	var $app;
	var $sections;
	var $pages;
	var $page_order;
	var $table=array(
		'application'=>'psu_psp.app_user_application',
		'page'=>'psu_psp.app_page',
		'section'=>'psu_psp.app_section',
		'form'=>'psu_psp.application',
		'field_value'=>'psu_psp.app_user_value',
		'required_rule'=>'psu_psp.app_required_rule',
		'program'=>'psu_psp.program',
		'field'=>'psu_psp.app_field',
		'file'=>'psu_psp.app_user_file',
		'repeating_field'=>'psu_psp.app_user_repeating_field',
		'user'=>'psu_psp.psp_user'
	);
	var $percent='';
	var $fields=array();
	var $field_values=array();
	var $programs='';
	var $url_prefix='/user/app/';
	var $view_only=false;
/**
  *PSPApplication
  *
  *Updates class specific variables with the passed in information
  *
  *@param string $db
  *@param string $current_page
  *@param integer $master_app
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
   * buildURL
   *
   * builds a complete url for the passed in web page
   *
   * @param string $page
   * @return string
   */
	function buildURL($page)
	{
		if($this->id)
			return $this->url_prefix.$this->id.'/'.$page;
		return $this->url_prefix.$page;
	}//end buildURL
/**
  *changeFormStatus
  *
  *updates the current stats of the form in the DB 
  *
  *@param string $status
  *@return boolean
  */

	function changeFormStatus($status)
	{
		$sql="UPDATE {$this->table['application']} SET application_status='$status'".(($status=='P')?",submitted=sysdate":'')." WHERE user_id='{$this->user_id}' AND id='{$this->id}'";
		if($this->db->Execute($sql))
		{
			if($status=='P')
			{
				//submitted form
				//check to see if user has an already existing unfinished form if so, don't bother copying data over.
				if(!$this->db->GetOne("SELECT 1 FROM {$this->table['application']} WHERE user_id='{$this->user_id}' AND application_status='U'"))
				{
					//create a new unfinished application for the user
					$sql="INSERT INTO {$this->table['application']} (user_id,master_application_id) VALUES ({$this->user_id},1)";
					if($this->db->Execute($sql))
					{
						//get the new application's id
						$new_app_id=$this->db->GetOne("SELECT id FROM {$this->table['application']} WHERE user_id='{$this->user_id}' AND application_status='U'");

						//insert the JUST submitted application values into the new application...exclude program information
						$sql="INSERT INTO {$this->table['field_value']} (user_id,application_id,app_field,field_value) (SELECT {$this->user_id},$new_app_id,app_field,field_value FROM {$this->table['field_value']} WHERE application_id={$this->id} AND repeating_id=0 AND app_field<>'program_entered')";
						$this->db->Execute($sql);

						//do the same thing with repeating fields...this is a little more complicated...exclude program information
						$sql="SELECT * FROM {$this->table['repeating_field']} WHERE application_id={$this->id} AND user_id={$this->user_id} AND repeating_code<>'program'";
						if($results=$this->db->Execute($sql))
						{
							$crosswalk=array();
							while($row=$results->FetchRow())
							{
								$row=PSUTools::cleanKeys('','',$row);

								//create the new repeating fields for the new application
								$sql="INSERT INTO {$this->table['repeating_field']} (user_id,application_id,repeating_code,repeating_order,activity_date) VALUES ({$this->user_id},$new_app_id,'{$row['repeating_code']}',{$row['repeating_order']},sysdate)";
								$this->db->Execute($sql);

								//get the new repeating id
								$crosswalk[$row['id']]=$this->db->GetOne("SELECT max(id) FROM {$this->table['repeating_field']} WHERE application_id=$new_app_id AND user_id={$this->user_id}");

								//insert the repeating field data into the new application
								$sql="INSERT INTO {$this->table['field_value']} (user_id,application_id,repeating_id,app_field,field_value) (SELECT {$this->user_id},$new_app_id,{$crosswalk[$row['id']]},app_field,field_value FROM {$this->table['field_value']} WHERE application_id={$this->id} AND repeating_id={$row['id']})";
								$this->db->Execute($sql);
							}//end while
							$results->Close();
						}//end if
					}//end if
				}//end if
			}//end 
			return true;
		}//end if
		return false;
	}//end changeFormStatus
	
/**
  *computeApplicationPercentage
  *
  *figures out the percentage of complete applications vrs required
  *
  *@param integer $add
  *@return int
  */

	function computeApplicationPercentage($add=0)
	{
		$app_fields=array('required'=>0,'complete'=>0);
		foreach($this->sections as $section)
		{
			$section_fields=$this->computeSectionStatus($section['id']);
			$app_fields['required']+=$section_fields['required'];
			$app_fields['complete']+=$section_fields['complete'];
		}//end foreach
		if($app_fields['complete']!=$app_fields['required'])
		{
			$app_fields['complete']+=$add;
			if($app_fields['complete']>$app_fields['required'])
				$app_fields['complete']=$app_fields['required'];
		}//end 
		if($app_fields['complete']>0)
			$percent = floor(100*($app_fields['complete']/$app_fields['required']));
		else
			$percent = 0;
		if(!$add) $this->percent = $percent;
		return $percent;
	}//end computeApplicationPercentage

/**
  *computePageStatus
  *
  *returns the amount of applications loaded vrs the number 
  *
  *@param string $page_id
  *@return integer
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
  *takes the selection and figures out how many of the applications have loaded so far
  *
  *@param string $section_id
  *@return integer
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
  *the passed in value is recorded at the debug variable
  *
  *@aram integer $val
  */

	function debug($val)
	{
		if($val==1) $this->db->debug=$val;
		$this->debug=$val;
	}//end debug
/**
  *deleteFile
  *
  *searches for the passed in file and deletes it if found
  *
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
  *deletes any rows that match the passed in code more than once
  *
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
				$sql="DELETE FROM {$this->table['field_value']} WHERE user_id={$this->user_id} AND app_field='$key' AND repeating_id=$repeating_id AND application_id='{$this->id}";
				$this->db->Execute($sql);
				$this->db->Execute("DELETE FROM {$this->table['repeating_field']} WHERE user_id={$this->user_id} AND id=$repeating_id");
			}//end foreach
		}//end if

		if($repeating_code=='program')
		{
			$num_programs_entered=$this->db->GetOne("SELECT count(*) FROM {$this->table['repeating_field']} WHERE user_id={$this->user_id} AND repeating_code='program' AND application_id={$this->id}");
			if(!$num_programs_entered)
			{
				$this->db->Execute("DELETE FROM {$this->table['field_value']} WHERE user_id={$this->user_id} AND application_id={$this->id} AND app_field='program_entered'");
			}//end 
		}//end if
	}//end deleteRepeatingFields
/**
  *getApplications
  *
  *retrieves and cleans application information from the database
  *
  *@param string $user_id
  *@return string array
  */

	function getApplications($user_id)
	{
		$data=array();
		$sql="SELECT * FROM {$this->table['application']} WHERE user_id=$user_id";
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
  *getApplicationsByStatus
  *
  *returns the application information stored in the database
  *
  *@return string array
  */


	function getApplicationsByStatus($status_code)
	{
		$data=array();
		$sql = "SELECT app.* FROM {$this->table['application']} app,{$this->table['user']} u WHERE app.user_id=u.id AND app.application_status='$status_code' AND u.account_status>0";

		$data = $results=$this->db->GetAll($sql);
		$data=PSUTools::cleanKeys('','',$data);
		
		return $data;
	}//end getApplicationsByStatus
	
/**
  *getApplicationStatusCounts
  *
  *returns the current status the applications
  *
  *@return string array
  */


	function getApplicationStatusCounts()
	{
		$temp = $this->db->GetAll("SELECT count(*) num, application_status FROM app_user_application GROUP BY application_status");

		$app_count = array('U'=>0, 'P'=>0, 'D'=>0, 'A'=>0);
		foreach($temp as $row)
		{
			$app_count[$row['APPLICATION_STATUS']] = $row['NUM'];
		}

		return $app_count;
	} // getApplicationStatusCounts
	
/**
  *getFields
  *
  *returns all the specified fields
  *
  *@param string $user_id
  *@param boolean $values_only
  *@param string @app_id
  *@return string array
  */


	function getFields($user_id,$values_only=false,$app_id='')
	{
		$sql="SELECT * FROM {$this->table['field_value']} WHERE user_id=$user_id AND application_id='{$this->id}' ORDER BY app_field";

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
			$results->Close();
		}//end if
		
		return $data;
	}//end getFields

/**
  *getFiles
  *
  *returns specified files from the database
  *
  *@param string $user_id
  *@param string $file_code
  *@param mixed $data
  *@return string 
  */
  
	function getFile($user_id,$file_code,$app_id='',$data=false)
	{
		if(!$app_id) $app_id=$this->id;
		if($data)
			return $this->db->GetOne("SELECT file_data FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id");
		else
		{
			$row=$this->db->GetRow("SELECT user_id,file_code,file_type,file_name,application_id FROM {$this->table['file']} WHERE user_id=$user_id AND file_code='$file_code' AND application_id=$app_id");
			$row=(is_array($row))?$row:array();
			return PSUTools::cleanKeys('','',$row);
		}//end else
	}//end getFile
/**
  *getNextField
  *
  *returns the next field to be loaded
  *
  *@return mixed
  */
	function getNextField()
	{
		//find the current page in the list of pages
		$sql="SELECT f.field_name,f.description,f.required_rule_id,p.code FROM ".$this->table['field']." f,".$this->table['page']." p WHERE f.page_id=p.id AND f.field_name NOT IN(SELECT fv.app_field FROM {$this->table['field_value']} fv WHERE fv.user_id={$this->user_id} AND fv.application_id='{$this->id}' AND fv.field_value IS NOT NULL) AND f.required_rule_id IS NOT NULL ORDER BY f.page_id,f.id";
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$row=PSUTools::cleanKeys('','',$row);
				$rule=$this->getRequiredRule($row['required_rule_id']);
				$rule='if('.$rule.') $required=1;';
				eval($rule);
				
				if($required && ($this->fields[$row['field_name']]['field_value']=='' || !isset($this->fields[$row['field_name']]['field_value'])))
				{
					return $row;
				}//end if
			}//end while
		}//end if
		return false;
	}//end getNextField
/**
  *getNextPage
  *
  *returns the next page to be displaced
  *
  *@param string $current
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
  *
  *
  *@param mixed $section
  *@return string array
  */

	function getOrderedPageCodes($section=false)
	{
		$where = '';

		if($section!==false)
		{
			$where=" AND p.section_id=$section";
		}

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
  *returns the urls for the pages
  *
  *@return string array
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
  *returns the url or the passed in page code
  *
  *@return mixed
  */
	function getPageSection($page_code)
	{
		return $this->db->GetOne("SELECT section_id FROM {$this->table['page']} WHERE code='$page_code'");
	}//end getPageSection
/**
  *getPercentageBar
  *
  *displays a percentage bar of progress
  *
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
  *getPreviousPage
  *
  *returns the url for the last page
  *
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
  *getProgramdetail
  *
  *returns detailed information about the passed in code
  *
  *@param string $code
  *@return string
  */
	function getProgramDetail($code)
	{
		return PSUTools::cleanKeys('','',$this->db->GetRow("SELECT * FROM {$this->table['program']} WHERE code='$code'"));
	}//end getProgramDetail
/**
  *getPrograms
  *
  *returns all programs in an array takin from the db
  *
  *@return string
  */
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
  *getRecentFieldActivity
  *
  *returns the most recent changes to a field
  *
  *
  *@return string
  */
	function getRecentFieldActivity()
	{
		$row=$this->db->GetRow("SELECT * FROM {$this->table['field_value']} WHERE user_id={$this->user_id} ORDER BY activity_date DESC");
		return PSUTools::cleanKeys('','',$row);
	}//end function
/**
  *getRepeatingFields
  *
  *searches for any fields that would be repeating in the database and returns then im an array
  *
  *@param string $app_id
  *@return string array
  */
	function getRepeatingFields($app_id='')
	{
		$data=array();
		if($results=$this->db->Execute("SELECT r.repeating_code,v.app_field,v.field_value,v.field_status,v.field_comments,v.repeating_id FROM {$this->table['repeating_field']} r,{$this->table['field_value']} v WHERE r.id=v.repeating_id AND r.user_id=v.user_id AND r.user_id=".$this->user_id." AND v.application_id='{$this->id}' ORDER BY r.repeating_code,r.repeating_order"))
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
  *getRequiredFields
  *
  *returns all of the fields necessary for the passed in page
  *
  *@param string $page_id
  *@return string array
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
  *getRequiredRule
  *
  *returns the information for the passed in rule id
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
  *returns all the informtion from the selections
  *
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
		$results->Close();
		
		return (count($data)>0)?$data:false;
	}//end getSections
/**
  *getUserData
  *
  *returns the data for the current user
  *
  *@param string $field
  *@param string $user_id
  *@return mixed
  */
	function getUserData($user_id='',$field='*')
	{
		if(!$user_id) $user_id=$this->user_id;
		if($field=='*' || $field=='')
		{
			$row =  $this->db->GetRow("SELECT * FROM {$this->table['user']} WHERE id=$user_id");
			$row = PSUTools::cleanKeys('','',$row);
			return $row;
		}//end if
		else
		{
			return $this->db->GetOne("SELECT $field FROM {$this->table['user']} WHERE id=$user_id");
		}//end else
	}//end getUserData
/**
  *saveField
  *
  *adds a field to the database of fields
  *
  *@param string $user_id
  *@param integer $value
  *@param integer $repeating_id
  *@param integer $status
  *@param mixed $commets
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
			$sql="INSERT INTO {$this->table['field_value']} (user_id,application_id,app_field,field_value,field_status,activity_date,repeating_id) VALUES ($user_id,{$this->id},'$field','$value',0,sysdate,$repeating_id)";
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
  *saves the passed in information into its own row in the datebase
  *
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
  *updates all variables with the information for the current application
  *
  *@param string $id
  *@param string $user_id
  */
	function setApplication($id='',$user_id='')
	{
		if(!$user_id) $user_id=$this->user_id;
		
		if($id)
		{
			$app=$this->db->GetRow("SELECT * FROM {$this->table['application']} WHERE user_id={$user_id} AND id=$id");
		}//end if
		else
		{
			$app=$this->db->GetRow("SELECT * FROM {$this->table['application']} WHERE user_id={$user_id} AND application_status='U'");
		}//end else
		$app=PSUTools::cleanKeys('','',$app);
		if(count($app)>0)
		{
			$this->id=$app['id'];
			$this->status=$app['application_status'];
			$this->percent='';
		}//end if
		$this->setUser($user_id);
	}//end setApplication
/**
  *setCurrentPage
  *
  *sets the variables to the current page code
  *
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
  *setUser
  *
  *updates class variables in accordance with the passed in value
  *
  *
  *@param string $user_id
  */
	function setUser($user_id)
	{
		$this->user_id=$user_id;
		$this->fields=$this->getFields($this->user_id);
		$this->field_values=$this->getFields($this->user_id,true);
		$this->repeating_fields=$this->getRepeatingFields();
	}//end setUser
/**
  *updateUserPidm
  *
  *updates the current plymouth id
  *
  *@param string $current_pidm
  *@param string $new_pidm
  */
	function updateUserPidm($current_pidm,$new_pidm)
	{
		$this->db->Execute("UPDATE {$this->table['file']} SET user_id=$new_pidm WHERE user_id=$current_pidm");
		$this->db->Execute("UPDATE {$this->table['repeating_field']} SET user_id=$new_pidm WHERE user_id=$current_pidm");
		$this->db->Execute("UPDATE {$this->table['field_value']} SET user_id=$new_pidm WHERE user_id=$current_pidm");
		$this->db->Execute("UPDATE {$this->table['application']} SET user_id=$new_pidm WHERE user_id=$current_pidm");
		$this->db->Execute("UPDATE {$this->table['user']} SET id=$new_pidm WHERE id=$current_pidm");
	}//end updateUserPidm

}//end PSPApplication
