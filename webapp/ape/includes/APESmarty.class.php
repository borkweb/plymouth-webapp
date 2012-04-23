<?php

/**
 * APESmarty provides a custom Smarty object for the Academic Excellence
 * application.
 */

require_once('PSUTemplate.class.php');

class APESmarty extends PSUTemplate
{
	function __construct()
	{
		parent::__construct();

		// general template vars
		$this->assign('title', 'Analysis and Provisioning Engine');
		$this->assign('icon', $GLOBALS['ape']->icons);

		$this->template_dir = $GLOBALS['BASE_DIR'] . '/templates';
		
		// custom template functions
		$this->register_function('ape_bool', array($this, 'ape_bool'));

		$this->assign('username', $_SESSION['username']);

		$this->assign('ape', $GLOBALS['ape']);
		$this->assign('myuser', $GLOBALS['myuser']);

		$this->assign('infodesk', APEAuthZ::infodesk() );

		// get svn dataz for this application
		$this->assign('svninfo', PSU::get_svn_info());

		$this->xhtml = false;

		$this->load_authz();

		/*** set up navigation links ***/
		$links = array(
			'nav-home' => $this->createLink('Home', $GLOBALS['BASE_URL'].'/', 'nav-icon nav-home'),
			'nav-identity' => $this->createLink('Identity/Access', $GLOBALS['BASE_URL'].'/user/'.$_SESSION['ape_identifier'], 'nav-icon nav-identity')
		);

		if( APEAuthZ::advancement() ) {
			$links['nav-advancement'] = $this->createLink('Advancement', $GLOBALS['BASE_URL'].'/user/advancement/'.$_SESSION['ape_identifier'], 'nav-icon nav-advancement');
			$this->assign('advancement_link' , true );
		}//end if

		if( APEAuthZ::hr() ) {
			$links['nav-hr'] = $this->createLink('HR', '#', 'nav-icon nav-advancement');
			$this->assign('hr_link' , true );
		}//end if

		if( APEAuthZ::family() ) {
			$links['nav-family'] = $this->createLink('Family', $GLOBALS['BASE_URL'].'/user/family/'.$_SESSION['ape_identifier'], 'nav-icon nav-family');
			$this->assign('family_link' , true );
		}//end if

		if( APEAuthZ::student() ) {
			$links['nav-student'] = $this->createLink('Student', $GLOBALS['BASE_URL'].'/user/student/'.$_SESSION['ape_identifier'], 'nav-icon nav-student');
			$this->assign('student_link' , true );
		}//end if

		if($_SESSION['AUTHZ']['admin'])  $links['nav-identity']['children'][] = $this->createLink('Access Management', $GLOBALS['BASE_URL'].'/authz.html', 'nav-icon nav-access');
		if(IDMObject::authZ('permission', 'ape_mailing'))  $links['nav-identity']['children'][] = $this->createLink('Mailing Lists', $GLOBALS['BASE_URL'].'/lists/', 'nav-icon nav-mailing');
		if(IDMObject::authZ('oracle', 'reporting_security'))  $links['nav-identity']['children'][] = $this->createLink('Banner Security', $GLOBALS['BASE_URL'].'/banner/', 'nav-icon nav-banner');
		if($GLOBALS['ape']->canResetPassword())
		{
			$links['nav-identity']['children'][] = $this->createLink('Password Test', $GLOBALS['BASE_URL'].'/password-test.html', 'nav-icon nav-pass');
			$links['nav-identity']['children'][] = $this->createLink('Locked ('.$GLOBALS['ape']->locks_count().')', $GLOBALS['BASE_URL'].'/locks.html', 'nav-icon nav-locked');
		}//end if
		$links['nav-identity']['children'][] = $this->createLink('Creation ('.$GLOBALS['ape']->pending_accounts_count().')', $GLOBALS['BASE_URL'].'/pending.html', 'nav-icon nav-pend-create');
		$links['nav-identity']['children'][] = $this->createLink('Deletion ('.$GLOBALS['ape']->pending_deletion_count().')', $GLOBALS['BASE_URL'].'/deletion.html', 'nav-icon nav-pend-delete');

		if( IDMObject::authz('permission', 'mis')) {
			$links['nav-identity']['children'][] = $this->createLink('Provision/Deprovision Docs', 'https://docs.google.com/Doc?docid=0AcDtIeWVN6nGYWNmZ3dxamRqOW5jXzE0N2dndHBqNmZn&hl=en', 'nav-icon nav-identity');
		}//end if

		if( APEAuthZ::hr() ) {
			$links['nav-hr']['children'][] = $this->createLink('Employee Clearance', $GLOBALS['BASE_URL'].'/checklist-admin.html', 'nav-icon nav-advancement');
		}//end if

		// if there are only 2 root links, replace root link #2 with its children
		if(count($links) == 2)
		{
			$parent_link = array_pop($links);
			$links = array_merge($links, $parent_link['children']);
		}//end if

		$this->assign('nav_links', $links);
	}

	function createLink($title, $url, $class)
	{
		$link = array();
		$link['title'] = $title;
		$link['url'] = $url;
		$link['class'] = $class;
		$link['children'] = array();
		return $link;
	}//end createLink

	function display($resource_name = 'index.tpl', $wrap = true, $cache_id = null, $compile_id = null)
	{
		// search-related template vars
		$this->assign('search_term', $_SESSION['ape_search_identifier']);
		if($_SESSION['ape_search_type'])
		{
			$this->assign('search_'.$_SESSION['ape_search_type'], 'selected="1"');
		}
		else
		{
			$this->assign('search_name','selected="1"');
		}

		parent::display($resource_name, $wrap, $cache_id, $compile_id);
	}

	/**
	 * Convert null/true/false to Unknown/Yes/No in a <span>.
	 *
	 * {ape_bool value=true} -> <span class="yes">Yes</span>
	 */
	function ape_bool($params, &$tpl)
	{
		if($params['value'] === null)
		{
			return '<span class="unknown">Unknown</span>';
		}

		if($params['value'])
		{
			return '<span class="yes">Yes</span>';
		}
		else
		{
			return '<span class="no">No</span>';
		}
	}//end ape_bool
}//end APESmarty
