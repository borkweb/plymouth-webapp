<!-- BEGIN: main -->
<script type="text/javascript" language="javascript" charset="utf-8" src="{JS_WEB_DIR}/new_call_username.js"></script>
<!-- BEGIN: new_call_user_name -->
<!--body onLoad="auto_save_info('{call_log_username}');"-->
<!-- BEGIN: new_call_form -->
<form name="new_call" method="post" action="{PHP.BASE_URL}/add_new_call.html" id="new_call" enctype="multipart/form-data">
<!-- END: new_call_form -->

<!-- BEGIN: edit_call_form -->
<form name="new_call" method="post" action="{PHP.BASE_URL}/update_call_details.html" id="edit_call" enctype="multipart/form-data">
<input type="hidden" name="call_history_id" value="{call_history_id}" />
<input type="hidden" name="call_id" id="call_id" value="{call_id}" />

<input type="hidden" name="action" id="action" value="{go_back.action}" />
<input type="hidden" name="option" id="option" value="{go_back.option}" />
<input type="hidden" name="group" id="group" value="{go_back.group}" />
<input type="hidden" name="find_type" id="find_type" value="{go_back.find_type}" />
<input type="hidden" name="page" id="page" value="{go_back.page}" />
<!-- END: edit_call_form -->
<a href="{PHP.BASE_URL}/{go_back.page}?action={go_back.action}&option={go_back.option}&group={go_back.group}&find_type={go_back.find_type}" class="noprint">Back to {go_back.name}</a>

<div id="userEmailSend"></div>

<div class="new-call-user-table-outside">
	<!-- START FIRST COLUMN DATA -->
	<div id="new-call-user-left-col" class="new-call-user-table-left new-call-user-table-main">

		<div id="caller_information_outer_div" class="new-call-user-table-inside">
			<fieldset>
			<legend id="caller_email_legend">Caller Information</legend>
				<div id="caller_information_div">
					{caller_information}
				</div>
				<div id="caller_email_div" style="display: none;">
					{caller_email}
				</div>
			</fieldset>
			<fieldset class="noprint">
			<legend id="caller_email_legend" onclick="document.getElementById('change_caller').style.display = 'block';">+ Re-Attach Call</legend>
				<div id="change_caller" style="display:none;">
					Change Caller To: <input type="text" name="attach_to"/><br/>
					<small><em>Enter username to re-attach this call</em></small>
				</div>
			</fieldset>
		</div>

		<div id="call_assignment_history_hidden_div" class="noprint">
			<!-- BEGIN: call_assignment_history_hidden -->
			<div id="call_assignment_history_inner_hidden_div" class="new-call-user-table-inside">
				<fieldset>
				<legend>Assignment History</legend>
					<div id="call_assignment_history_details_hidden_div" style="text-align:left;">
						<label class="label">Updated by: {call_history.updated_by} <br/>on {call_history.date_assigned} @ {call_history.time_assigned}</label><br/><br/>
						<label class="label">Details: {call_history.comments}</label><br/>
						<hr style="width: 50%;">
						<label class="label">Call Priority:</label> {call_history.call_priority}<br/>
						<label class="label">Call Status:</label> {call_history.call_status}<br/>
						<label class="label">Assigned To:</label> {call_history.tlc_assigned_to}<br/>
					</div>
				</fieldset>
			</div>
			<!-- END: call_assignment_history_hidden -->
		</div>

		<div id="user_quota_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend>Files (beta)</legend>
				<div id="call_files">
					<input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
					Attach File: <input type="file" name="attachment"/><br/>
					<small><em>Supports: .doc, .docx, .gif, .jpg, .pdf, .png,<br/>.txt, .xls., .xlsx (3MB maximum)</em></small>
				</div>
			</fieldset>
			<!-- BEGIN: files -->
			<fieldset>
				<legend>Attached Files</legend>
				<div id="user_quotas_div">
						<ul>
							<!-- BEGIN: file -->
								<li><a href="{file.url}" target="_blank">{file.name}</a>
							<!-- END: file -->
						</ul>
				</div>
			</fieldset>
			<!-- END: files -->
		</div>

		<div id="hardware_info_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend>Hardware Info</legend>
				<div id='hardwareInfoDiv'>
					{hardware_info}
				</div>
			</fieldset>
		</div>

		<div id="caller_history_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend>Call History</legend>
				<div id="CallHistoryDiv">
					{call_history_summary}
				</div>
			</fieldset>
		</div>

		<div id="caller_media_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend onClick="calllog_toggle('media_loans');">
			<span id="media_loans_symbol">+</span> Media Loans History</legend>
				<div id="media_loans_div">
					{media_loans_summary}
				</div>
			</fieldset>
		</div>
		<!-- BEGIN: webCTCourses -->
		<div id="caller_webct_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend onClick="calllog_toggle('webCT');">
			<span id="webCT_symbol">+</span> WebCT Courses</legend>
				<div id="webCT_div" style="display:none;">
					{webCT_summary}
				</div>
			</fieldset>
		</div>
		<!-- END: webCTCourses -->

		<div id="restore_request_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend onClick="calllog_toggle('restore_request');">
			<span id="restore_request_symbol">+</span> Restore Request</legend>
				<div id="restore_request_div">
					{restore_request_func}
				</div>
			</fieldset>
		</div>
	</div>

	<!-- START SECOND COLUMN DATA -->
	<div id="new-call-user-middle-col" class="new-call-user-table-center new-call-user-table-main">
		<!-- BEGIN: call_closed -->
		<div class="warning-call-closed">
			This call has been closed.
		</div>
		<!-- END: call_closed -->

		<div id="problem_details_outer_div" class="new-call-user-table-inside">
			<fieldset>
			<legend>{details_name}</legend>
				<!-- div id="displayAutoSave">Auto Save Every 2 minutes</div-->
				{ticket_form}
				<br/>
				<label class="label">Keywords:</label><br/>
				<div id="keywordsList"></div>
				<div id="webctDetails"></div>
				<input type="text" name="keywords_list" id="keywords_list" size="51" value="{call_assignment_history_keywords}"/>
			</fieldset>
		</div>

		<div id="knowledgebase_outer_div" class="new-call-user-table-inside">
			<fieldset>
			<legend onClick="calllog_toggle('knowledgebase'); problem_array();"><span id="knowledgebase_symbol">+</span> Knowledge Base</legend>
			<div id="knowledgebase_div" style="display:none; text-align:center; font-size:12px;">
			Type text into Problem Details to<br/>Search the Knowledgebase</div>
			</fieldset>
		</div>
		
		<div id="call_assignment_history_div">
		<!-- BEGIN: call_assignment_check -->
			<div class="new-call-user-table-inside" id="call_assignment_history_reorder">
				<div id="re-order-div" style="padding-bottom:10px;">
				Re-order: <a href="javascript: reorder_assign_history('old', '{person.username}', {call_id});" class="current_link">Old-New</a> | <a href="javascript: reorder_assign_history('new', '{person.username}', {call_id});" class="current_link">New-Old</a></div>
				<div id="assign-order-div" class="new-call-user-table-inside" style="border:0px; display:inline;">
					<!-- BEGIN: call_assignment_history -->
					<div id="call_assignment_history_inner_div" class="new-call-user-table-inside" style="border:0px; display:inline;">
						<fieldset>
						<legend>Assignment History</legend>
							<label class="label"><em>Updated by:</em> {call_history.updated_by} on {call_history.date_assigned} @ {call_history.time_assigned}</label><br/><br/>
							<label class="label"><em>Details:</em> {call_history.comments}</label><br/>
							<hr style="width: 75%;" align="left">
							<label class="label"><em>Call Priority:</em></label> {call_history.call_priority}<br/>
							<label class="label"><em>Call Status:</em></label> {call_history.call_status}<br/>
							<label class="label"><em>Assigned To:</em></label> {call_history.tlc_assigned_to}<br/>
						</fieldset>
					</div>
					<!-- END: call_assignment_history -->
				</div>
			</div>
		<!-- END: call_assignment_check -->
		</div>

		<div id="call_information_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend>Call Information</legend>
				<div id="call_information_div">
					{call_information}
				</div>
			</fieldset>
		</div>

		<div id="assigned_to_outer_div" class="new-call-user-table-inside noprint">
			<fieldset>
			<legend onClick="calllog_toggle('assign_to');">
			<span id="assign_to_symbol">+</span> Assign Call To</legend>
			<div id="assign_to_div" style="display: block;">
				{call_assigned_to}
			</div>
			</fieldset>
		</div>

		<div id="submit_reset" class="noprint">
			<!--<input type="button" name="submit_button" id="submit_button" onClick="checkNewCallForm();" class="action" value="Submit Call">-->
			<a href="#" class="action" id="new_call_submit_button" name="new_call_submit_button">Submit Call</a>
			&nbsp; | &nbsp;
			<a href="javascript:document.new_call.reset()" class="action">Reset Form</a>
		</div>

	</div>
	<!-- FINISH SECOND COLUMN DATA -->

</form>
</body>
<!-- END: new_call_user_name -->

<!-- BEGIN: no_call_user_name -->
	<a href="{PHP.BASE_URL}/{page}?action={action}&option={option}&group={group}&find_type={find_type}">Back to {back_to_name}</a><br/><br/>
	THIS CALL DOES NOT CONTAIN A VALID USERNAME
<!-- END: no_call_user_name -->

<!-- END: main -->
