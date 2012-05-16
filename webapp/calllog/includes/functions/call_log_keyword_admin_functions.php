<?php

	function addKeyword(){
	return buildKeywordForm();
	}// end function addKeyword


	function editKeyword($keyword_id, $keyword_user_name=''){
		global $db;

		$keyword_results = Array();
		$query = "SELECT * FROM call_log_keywords WHERE keyword_id = '$keyword_id'";
		$result = $db->Execute($query);
		$row = $result->FetchRow();
		$keyword_results[] = $row;
	
		return buildKeywordForm($keyword_results);
	}// end function editKeyword

	
	function buildKeywordForm($keyword_results=''){
		global $db;
		
		$father_name = 'call_log_keyword_admin';
		$father_page = $father_name.'.html';
		$template_file = $father_name.'_add_edit.tpl';
		$form_action = '';
		$submit_button = '';

		$tpl = new XTemplate(TEMPLATE_ADMIN_DIR.'/'.$template_file);
		$tpl->assign('father_page', $father_page);
		
		// If keyword results are empty, add a new keyword.
		if(empty($keyword_results)){ 
		   $form_action = $father_page.'?action=add_keyword_form';
		   $submit_button = 'add_keyword';
		}// end if
		else{
		   $form_action = $father_page.'?action=update_keyword_form';
		   $submit_button = 'update_keyword';
		   foreach($keyword_results as $key){
				   if($key['active'] == 1){
					  $active_option = 'selected_active';
				   }// end if
				   else{
					  $active_option = 'selected_inactive';
				   }
				   $tpl->assign($active_option, ' selected="selected"');
				   $tpl->assign('key', $key);	
		   }// end foreach
		}
		$tpl->assign('form_action', $form_action);
		$tpl->parse('main.'.$submit_button);
		$tpl->parse('main');
	
		return $tpl->text('main');
	}// end function buildKeywordForm


	function insertKeywordIntoDB($keyword_data){
		global $db;
	
		// Keyword data
		$keyword = $keyword_data['keyword'];
		$name = $keyword_data['name'];
		$active = $keyword_data['active'];
		
		$query = "INSERT INTO call_log_keywords (`keyword`, `name`, `active`) VALUES('$keyword', '$name', '$active')";
		
		if($db->Execute($query)){
		   return 'Keyword inserted successfully.';
		}// end if
		else{
           $message = 'Error inserting keyword.';
		   $_SESSION['user_message'] = $message;
		   return $message;
		}

	}// end function insertKeywordIntoDB


	function updateKeywordInDB($keyword_data, $keyword_id=''){
		global $db;

		// Keyword data
		$keyword_id = $keyword_data['keyword_id'];
		$keyword = $keyword_data['keyword'];
		$name = $keyword_data['name'];
		$active = $keyword_data['active'];

		$query = "UPDATE call_log_keywords SET keyword = '$keyword', name = '$name', active = '$active' WHERE keyword_id = '$keyword_id'";

		if($db->Execute($query)){
		   return 'Keyword updated successfully.';
		}// end if
		else{
           $message = 'Error updating keyword.';
		   $_SESSION['user_message'] = $message;
		   return $message;
		}

	}// end function updateKeywordInDB


	function displayKeywords($display){
		global $db;
		
		$father_name = 'call_log_keyword_admin';
		$father_page = $father_name.'.html';
		$template_file = $father_name.'_display.tpl';
		$tpl = new XTemplate(TEMPLATE_ADMIN_DIR.'/'.$template_file);
		$keyword_results = Array();
		$query = '';

		// Build query based off of [valid] $display variable.
		switch($display){
			case 'activekeywords':
				$query = "SELECT * FROM call_log_keywords WHERE active = 1";
			break;
			case 'inactivekeywords':
				$query = "SELECT * FROM call_log_keywords WHERE active = 0";
			break;
			case 'allkeywords':
				$query = "SELECT * FROM call_log_keywords";
			break;
			default:
				$query = "SELECT * FROM call_log_keywords WHERE active = 1";
			break;
		}// end switch
		
		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  if($row['active'] == 0){
                 $status_name = 'Inactive';
				 $other_status = 1;
				 $other_status_name = 'Active';
			  }// end if
			  else{
				 $status_name = 'Active';
				 $other_status = 0;
				 $other_status_name = 'Inactive';
			  }
			  $tpl->assign('status_name', $status_name);
			  $tpl->assign('other_status', $other_status);
			  $tpl->assign('other_status_name', $other_status_name);
			  $tpl->assign('key', $row);
			  $tpl->parse('main.display_keyword');
		}// end while
		$tpl->parse('main');
	
		return $tpl->text('main');
	}// end function displayKeywords


	function setKeywordStatus($keyword_id, $status=''){
		global $db;

		$query = "UPDATE call_log_keywords SET active = '$status' WHERE keyword_id = '$keyword_id'";
		if($db->Execute($query)){
		   $message = 'Keyword Updated Successfully.';
		   return $message;	
		}// end if
		else{
		   $message = 'Error Updating Keyword.';
		   $_SESSION['user_message'] = $message;
		   return $message;	
		}

	}// end function setKeywordStatus

