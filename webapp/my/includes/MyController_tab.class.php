<?php
/**
 * MyController_tab.class.php
 *
 * Portal Controller controls the tab delegation page loads within the portal
 *
 * @version		1.0.0
 * @author		Adam Backstrom <ambackstrom@plymouth.edu>
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @author		Vasken Hauri <vkhauri@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */ 
require_once 'PSUController.class.php';

class MyController_tab extends PSUController
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->portal = new MyPortal($GLOBALS['identifier']);

		$new_themes = PSUTheme::new_themes( $this->portal->person->wp_id );
		$this->tpl->assign('new_themes', $new_themes );

		$this->tpl->body_style_classes[] = 'myplymouth';

		if( $this->portal->is_fluid() ) {
			$this->tpl->body_style_classes[] = 'fluid';
		}//end if

		MyController::_detect_disabled_chat( $this->portal, $this->tpl );
	}//end __construct

	/**
	 * delegates page rendering
	 *
	 * @param $path \b path stuff
	 */
	public static function delegate( $path = null, $class = __CLASS__ ) {
		parent::delegate($path, $class);
	}//end delegate

	/**
	 * logic to display a given tab and portal structure for the user
	 *
	 * @param $tab \b tab to load
	 */
	public function index( $tab = null )
 	{
		if( ! $tab ) {
			PSU::redirect( $GLOBALS['BASE_URL'] . '/tab/welcome' );
		}
	}//end index 

	/**
	 * Custom 404 handler.
	 */
	public function handle_404() {
		$args = func_get_args();
		$tab = $args[0] ? $args[0] : 'welcome';

		$oTab = $this->portal->tabs($tab);

		if( $oTab == false ) {
			header('HTTP/1.1 404 Not Found');
			die('Not a valid tab: ' . $tab);
		}

		$this->tpl->assign('portal', $this->portal);
		$this->tpl->assign('current_tab', $oTab);

		$oTab->log_hit();

		$this->display('index.tpl');
	}//end handle_404
}//end MyController_tab
