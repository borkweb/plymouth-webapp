<!-- BEGIN: main -->

	<!-- BEGIN: call_history -->
	<div id="call_history_detail_div">
	<div style="text-align:left; font-size: 10px; float: left;"><a href="{CALL_LOG_WEB_HOME}/ticket/{this_call_id}/">Edit This Call</a></div>
	<div style="text-align:right; font-size: 10px;"><a href="javascript: void(0);" onClick="viewCallHistorySummary('{caller}', false);">&#171; Back</a></div>
		<table class="grid">
			<tr><th align="center" colspan="2">Call Details</th></tr>
				<!-- BEGIN: call_log_details -->
					<tr><td>Call ID</td><td>{call_id}</td></tr>
					<tr><td>Caller Username</td><td>{caller_username}</td></tr>
					<tr><td>Call Name</td><td>{caller_name}</td></tr>
					<tr><td>Call Phone Number</td><td>{caller_phone_number}</td></tr>
					<tr><td>Call Log Username</td><td>{calllog_username}</td></tr>
					<tr><td>Call Date &amp; Time</td><td>{call_date_time}</td></tr>
					<tr><td>Keywords</td><td>{keywords}</td></tr>
					<tr><td>Location</td><td>{location}</td></tr>
					<tr><td>Logged From</td><td>{call_logged_from}</td></tr>
				<!-- END: call_log_details -->
		</table>
		<div id="call_history_assignment_loop" style="height:150px; overflow:auto;">
		<table width="100%" valign="top" cellpadding="3" cellspacing="1">
		<tr><th align="center" colspan="2">Call History</th></tr>
		<!-- BEGIN: call_num_assigned -->
			<tr style="background-color: {priority_color};">
				<td colspan="1">{call_date} at {call_time}</td><td style="text-align: right;"><b>{priority} Priority</b></td>
			</tr>
			<!-- BEGIN: call_history_details -->
				<tr><td>Updated By</td><td>{updated_by}</td></tr>
				<tr><td>TLC Assigned To</td><td>{tlc_assigned_to}</td></tr>
				<tr><td>ITS Assigned Group</td><td>{its_assigned_group}</td></tr>
				<tr><td>Comments</td><td>{comments}</td></tr>
				<tr><td>Call Status</td><td>{call_status}</td></tr>
			<!-- END: call_history_details -->
		 <tr>
		  <td width="100%" colspan="2">
		   <hr />
		  </td>
		 </tr>		
		<!-- END: call_num_assigned -->	
		</table>
		</div>
	</div>
	<!-- END: call_history -->

	<!-- BEGIN: highlight_call_history -->
	<script type="text/javascript">
	new Effect.Highlight('CallHistoryRow1', {startcolor:'#D19275', endcolor:'#FFEFD5', restorecolor:'#FFEFD5'});
	</script>
	<!-- END: highlight_call_history -->

	<!-- BEGIN: restore_request -->
		<!-- BEGIN: restore_request_error -->
		<script type="text/javascript">
			restoreRequestError();
		</script>
		<div id="restore_request_error" style="text-align: center; font-weight:bold;">
		Please Complete All Fields
		</div>
		<!-- END: restore_request_error -->

		<!-- BEGIN: restore_request_complete -->
		Thank You For Submitting a Restore Request<br/>
		<a href='javascript: newRestoreRequest();'>File Another Restore Request</a>
		<!-- END: restore_request_complete -->
	<!-- END: restore_request -->


	<!-- BEGIN: queueEmailMessage -->
	Your message has been sent<br/>
	<a href="javascript: sendHelpDeskMail('{caller_user_name}@{caller_class}plymouth.edu',0,'reset');">Back to Caller Information</a>
	<!-- END: queueEmailMessage -->

	<!-- BEGIN: group_names -->
	{group_names}
	<!-- END: group_names -->

	<!-- BEGIN: assign_reorder -->
		<!-- BEGIN: call_assignment_history -->
		<ul>
			<li><label class="inline">Updated by:</label> {call_history.updated_by} on {call_history.date_assigned} @ {call_history.time_assigned}</li>
			<li><label class="inline">Call Status:</label> {call_history.call_status}</li>
			<li><label class="inline">Call Priority:</label> {call_history.call_priority}</li>
			<li><label class="inline">Assigned To:</label> {call_history.tlc_assigned_to}</li>
			<li><label class="inline">Details:</label> {call_history.comments}</li>
		</ul>
		<hr style="width: 75%;" align="left">
		<!-- END: call_assignment_history -->
	<!-- END: assign_reorder -->

<!-- END: main -->
