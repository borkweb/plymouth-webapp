<?php

/**
 * faculty.class.php
 *
 * code for working with faculty members
 *
 * @module		faculty.class.php
 * @copyright 2008, Plymouth State University, ITS
 */
require_once 'PSUWordPress.php';
class faculty
{
	var $db_old;
	var $db;
	var $catalog;
	var $_debug=false;

	/**
	 * __construct
	 *
	 * constructor to make database connections and create a catalog
	 *
	 * @access	public
	 * @param	mixed $pidm pidm that defaults to false. Does not appear to actually be used
	 */
	function __construct($pidm=false)
	{
		$this->connect();

		require_once 'catalog.class.php';
		$this->catalog = new Catalog(false,'working_catalog');
	}
	
	/**
	 * connect
	 *
	 * handle connetions to the database
	 *
	 * @access	public
	 */
	function connect()
	{
		require_once 'PSUDatabase.class.php';
		$this->db = PSUDatabase::connect('mysql/faculty');
	}

	/**
	 * getDepartments
	 *
	 * get all of the departments and a count of the number of faculty in each department
	 *
	 * @access	public
	 * @return	array array of departments
	 */
	function getDepartments()
	{
		$departments = array();
		$codes = $this->db->GetAll("SELECT department, count(*) as num FROM reformedfaculty GROUP BY department ORDER BY department");
		foreach($codes as $code)
		{
			$department = $this->getDepartment($code['department']);
			$name = ($department['name'])?$department['name']:'*'.$code['department'];
			$departments[] = array('code'=>$code['department'], 'name'=>$name.' ('.$code['num'].')');
		}
		return $departments;
	}

	/**
	 * getDepartment
	 *
	 * get a department with the given department code
	 *
	 * @access	public
	 * @param	mixed $code department code
	 * @return	mixed returns result of getDepartment from catalog class
	 */
	function getDepartment($code)
	{
		return $this->catalog->getDepartment($code);
	}

	/**
	 * getDepartmentWebDirectory
	 *
	 * get a departments web directory with the given department code
	 *
	 * @access	public
	 * @param	mixed $code department code
	 * @return	string path of web directory
	 */
	function getDepartmentWebDirectory($code)
	{
		return $this->db->GetOne("SELECT web_dir FROM department_web WHERE department_code='$code'");
	}

	/**
	 * getFacultyByDepartment
	 *
	 * get the faculty associated with a particular department
	 *
	 * @access	public
	 * @param	mixed $dept_code department code
	 * @param	string $order_by order by parameter, defaults to lastname
	 * @return	array array of faculty
	 */
	function getFacultyByDepartment($dept_code, $order_by='lastname')
	{
		return $this->db->GetAll("SELECT r.* FROM reformedfaculty r,faculty_department d WHERE d.department_code LIKE '$dept_code' AND r.active_status=1 AND r.uid = d.faculty_username ORDER BY $order_by");
	}

	/**
	 * getFacultyMember
	 *
	 * get the information for a particular faculty
	 *
	 * @access	public
	 * @param	string $username username of faculty member
	 * @return	array array of information about faculty member
	 */
	function getFacultyMember($username)
	{
		return $this->db->GetRow("SELECT * FROM reformedfaculty WHERE uid='$username'");
	}

	/**
	 * getFacultyByLastName
	 *
	 * get faculty members by last name
	 *
	 * @access	public
	 * @param	string $lastname last name to look for
	 * @return	array array of faculty and information
	 */
	function getFacultyByLastName($lastname, $type = 'all')
	{
		if($type == 'active')
			$type_where = " AND active_status = 1";
		elseif($type == 'inactive')
			$type_where = " AND active_status = 0";
			
		return $this->db->GetAll("SELECT * FROM reformedfaculty WHERE lastname LIKE '$lastname' $type_where ORDER BY lastname, firstname");
	}

	/**
	 * printFacultyList
	 *
	 * print a list of faculty for a goven department
	 *
	 * @access	public
	 * @param	mixed $department_code Department code to look for
	 * @param	string $link_to page to link to, defaults to faculty_member.html
	 * @param	array $options array of options, defaults to array('include_education'=>false,'lastname_first'=>false)
	 */
	function printFacultyList($department_code, $link_to='faculty_member.html', $options=array('include_education'=>false,'lastname_first'=>false))
	{
		$faculty = $this->getFacultyByDepartment($department_code);
		if($faculty)
		{
			$faculty_html = '<ul class="faculty-list">';
			foreach($faculty as $person)
			{
				$faculty_html .= '<li><span class="faculty-name">';
				if($link_to!==false)
					$faculty_html .= '<a href="'.$link_to.'?fac='.$person['uid'].'">';

				if($options['lastname_first'])
				{
					$faculty_html .= $person['lastname'].', ';
				}
				
				$faculty_html .= $person['firstname'];

				if($person['middlename'] != '')
					$faculty_html .=  ' '.$person['middlename'].'.';
				
				if(!$options['lastname_first'])
				{
					$faculty_html .= ' '.$person['lastname'];
				}
				
				if($person['suffix'] != '') 
					$faculty_html .= ' '.$person['suffix'].".";
	
				if($link_to!==false)
					$faculty_html .= '</a>';
				
				$faculty_html .= '</span> ('.$person['yearhired'].'),';
		
				$faculty_html .= ' <span class="faculty-title">' . $person['title'].'</span>';
				
				if($options['include_education'])
				{
					$faculty_html .= '<br /><span class="faculty_education">'.$person['education'].'</span>';
				}

				$faculty_html .= "</li>\n"; 
			}
			$faculty_html .= '</ul>';
		}
		print $faculty_html;
	}

	/**
	 * printFacultyMember
	 *
	 * print information about a faculty member
	 *
	 * @access	public
	 * @param	string $username username of faculty member
	 */
	function printFacultyMember($username)
	{
		$faculty_html = '';

		$person = $this->getFacultyMember($username);
		if($person)
		{
			$path = explode('/',$_SERVER['SCRIPT_FILENAME']);
			array_pop($path);
			$path = implode('/',$path);

			if(!file_exists($path.'/staff'))
			{
				$dept_web_dir = $this->getDepartmentWebDirectory($person['department']);
				$path = $_SERVER['DOCUMENT_ROOT'].'/'.$dept_web_dir;

				$img_path = '/'.$dept_web_dir.'/staff/images/';
			}
			else
			{
				$img_path = 'staff/images/';
			}

			if (file_exists($path.'/staff/images/'.$username.'.jpg'))
			{
				$image_html = '<img src="'.$img_path.$username.'.jpg" class="faculty-img" />';
			}
			else
				$image_html = '';


			$html = $path.'/staff/'.$username.'.html';
			if (file_exists( $html))
			{
				$addition_html = file_get_contents($html);
			}
			else
				$addition_html = '';
			
			$faculty_html .= $image_html.'<h2>'.$person['firstname'];
			if($person['middleinitial'] != '') 
				$faculty_html .= ' '.$person['middleinitial'].".";
			$faculty_html .= ' '.$person['lastname'];
			if($person['suffix'] != '') 
				$faculty_html .= ' '.$person['suffix']."."; 
			$faculty_html .= '</h2><br />';

			$faculty_html .= '<em>'.$person['title'].'</em><br /><br />'.$person['education'].'<br />';
			$faculty_html .= '<strong>Email:</strong> <a href=mailto:'.$person['uid'].'@plymouth.edu>'.$person['uid'].'@plymouth.edu</a><br />';

			if($person['homepageurl'] != '')
				$faculty_html .= '<strong>Homepage:</strong> <a href="'.$person['homepageurl'].'" target="_blank">'.$person['homepageurl'].'</a><br />';
			$faculty_html .= '<div style="padding:20px 0 0 0;">'.$addition_html.'</div>';
		}

		print $faculty_html;
	}

	/**
	 * replaceFacultyMember
	 *
	 * remove a faculty member, and replace with another
	 *
	 * @access	public
	 * @param	array $person array of information about the person
	 * @return	boolean success indicator of replace
	 */
	function replaceFacultyMember($person)
	{
		if(!is_array($person['department']))
		{
			$person['department'] = array($person['department']);
		}
		$this->db->Execute("DELETE FROM faculty_department WHERE faculty_username='{$person['uid']}'");
		foreach($person['department'] as $department)
		{
			$this->db->Execute("INSERT INTO faculty_department (faculty_username, department_code) VALUES ('{$person['uid']}', '$department')");
		}
		
		$sql = "REPLACE INTO reformedfaculty (
						  `uid`, 
						  `ssn`, 
						  `firstname`, 
						  `middleinitial`, 
						  `lastname`, 
						  `suffix`, 
						  `yearhired`, 
						  `department`, 
						  `title`, 
						  `education`, 
						  `facultynumber`, 
						  `homepageurl`, 
						  `active_status`
						) VALUES (
						  '{$person['uid']}', 
						  '1', 
						  '{$person['firstname']}', 
						  '{$person['middleinitial']}', 
						  '{$person['lastname']}', 
						  '{$person['suffix']}', 
						  '{$person['yearhired']}', 
						  '".implode(', ',$person['department'])."', 
						  '{$person['title']}', 
						  '{$person['education']}', 
						  '{$person['facultynumber']}',
						  '{$person['homepageurl']}', 
						  '{$person['active_status']}'
						)";
		
		return $this->db->Execute($sql);
	}

	function deleteFacultyMember($username)
	{
		$this->db->Execute("DELETE FROM faculty_department WHERE faculty_username='$username'");
		return $this->db->Execute("DELETE FROM reformedfaculty WHERE uid='$username' LIMIT 1");
	}
}

?>
