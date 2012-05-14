<?php
require_once 'PSUController.class.php';
/**
 * SupportController.class.php
 *
 * Support Controller controls the delegation page loads of this application
 *
 * @version		1.0.0
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2010, Plymouth State University, ITS
 */ 
class SupportController extends PSUController
{
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->myuser = new PSUPerson( $_SESSION['username'] );
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
	 * Default handler to display index
	 */
	public function index(){
		IDMObject::authN();
		$this->tpl->assign('open_calls', $this->_get_calls('open', $_GET['open_page'] ? $_GET['open_page'] : 1));
		$this->tpl->assign('closed_calls', $this->_get_calls('closed', $_GET['closed_page'] ? $_GET['closed_page'] : 1));
		$this->tpl->display('index.tpl');
	}//end index

	/**
	 * Handles the retrieval of IP information
	 */
	public function ip(){
		$this->tpl->assign('host', gethostbyaddr( $_SERVER['REMOTE_ADDR'] ));
		$this->tpl->display('ip.tpl');
	}//end ip

	/**
	 * Handles the session dump
	 */
	public function session_dump( $state = null ){
		$args = func_get_args();

		$session = $_SESSION;

		if( $session['_'] ) {
			$session['_'] = 'SOMETHING ENCRYPTED';
		}//end if

		if( $state == 'hide' && in_array( 'authz', $args ) ) {
			unset( $session['AUTHZ'] );	
		}//end if

		if( $state == 'hide' && in_array( 'pw', $args ) ) {
			unset( $session['pw'] );	
		}//end if

		unset( $session['messages'] );
		unset( $session['successes'] );
		unset( $session['warnings'] );
		unset( $session['errors'] );

		$this->tpl->assign('session', print_r( $session, true ));
		$this->tpl->display('session-dump.tpl');
	}//end session_dump

	/**
	 * handles the ticket submission page
	 */
	public function submit(){
		IDMObject::authN();

		$tpl = new PSUSmarty();
		$form = $tpl->fetch( PSU_BASE_DIR . '/webapp/calllog/templates/ticket_form.tpl');

		$this->tpl->assign('form', $form);
		$this->tpl->display('submit.tpl');
	}//end submit

	/**
	 * displays a ticket's public updates
	 */
	public function ticket($ticket){
		IDMObject::authN();

		$sql = "SELECT *,
									 CONCAT(date_assigned, ' ', time_assigned) update_date
			        FROM call_history
						 WHERE call_id = ?
               AND (updated_by = ?
						        OR
						        tlc_assigned_to = ?
										OR
										updated_by = ?
										OR
										tlc_assigned_to = ?
									 )
						 ORDER BY date_assigned, time_assigned";
		$args = array(
			$ticket,
			$this->myuser->login_name,
			$this->myuser->login_name,
			$this->myuser->wp_id,
			$this->myuser->wp_id
		);

		$details = PSU::db('calllog')->GetAll($sql, $args);
		foreach( $details as &$detail ) {
			$p = new PSUPerson( $detail['updated_by'] );
			$detail['updated_by_name'] = $p->wp_id == $_SESSION['wp_id'] ? 'You' : $p->formatName('f');
			$p->destroy();
			unset($p);
		}//end foreach

		$sql = "SELECT call_status
			        FROM call_history
						 WHERE call_id = ? AND current = 1";
		$args = array(
			$ticket
		);

		$this->tpl->assign('call_status', PSU::db('calllog')->GetOne($sql, $args));
		$this->tpl->assign('details', $details);
		$this->tpl->assign('ticket', $ticket);

		$tpl = new PSUSmarty();
		$tpl->assign('hide_checklist', true);
		$tpl->assign('details_title', 'Update Ticket');
		$form = $tpl->fetch( PSU_BASE_DIR . '/webapp/calllog/templates/ticket_form.tpl' );

		$this->tpl->assign('form', $form);

		$this->tpl->display('ticket.tpl');
	}//end ticket

	protected function _get_calls( $type = 'open', $page = 1, $start = 0 ) {
		$sql = "SELECT c.call_id,
									 h.id history_id,
									 h.call_status status,
									 h.tlc_assigned_to,
									 h.updated_by,
									 h.comments,
									 CONCAT(c.call_date, ' ', c.call_time) call_date,
									 CONCAT(h.date_assigned, ' ', h.time_assigned) update_date,
									 h2.updated_by original_submitter,
									 h2.tlc_assigned_to first_assigned_to,
									 h2.comments original_comment
							FROM call_log c
									 INNER JOIN call_history h
										ON h.call_id = c.call_id
										AND h.current = 1
										AND h.call_status = ?
									 INNER JOIN call_history h2
										ON h2.call_id = c.call_id
						 WHERE (c.caller_username = ? OR c.wp_id = ?)
							 AND h2.id = (
										SELECT min(h3.id)
											FROM call_history h3
										 WHERE h3.call_id = h2.call_id
									 )
						 ORDER BY h.date_assigned DESC,
						 			 h.time_assigned DESC
									 {$limit}";
		$args = array(
			'status' => $type,
			'username' => $this->myuser->username,
			'wp_id' => $this->myuser->wp_id
		);

		$results = psu::db('calllog')->PageExecute( $sql, 10, $page, $args);
		$data = array();
		foreach( $results as $row ) {
			$data[] = $row;
		}//end foreach
		return psu::paginationResults( psu::paginationInfo( $_GET, $results ), $data );
	}//end get_calls
}//end SupportController
