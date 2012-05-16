<!-- BEGIN: main -->
<a href="{PHP.BASE_URL}/{go_back.page}?action={go_back.action}&option={go_back.option}&group={go_back.group}&find_type={go_back.find_type}" class="noprint btn danger">&laquo; Back to {go_back.name}</a>
<!-- BEGIN: new_call_user_name -->

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

	<div class="grid_6 alpha">

		<fieldset>
			<legend id="caller_email_legend">Caller Information</legend>
				<div id="caller_information_div">
					{caller_information}
				</div>
				<div id="caller_email_div" style="display:none;">
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

		<fieldset class="noprint">
			<legend>Files</legend>
			<input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
			Attach File: <input type="file" name="attachment"/><br/>
			<small><em>Supports: .doc, .docx, .gif, .jpg, .pdf, .png,<br/>.txt, .xls., .xlsx (3MB maximum)</em></small>
		</fieldset>

		<!-- BEGIN: files -->
		<fieldset class="noprint">
			<legend>Attached Files</legend>
			<ul>
				<!-- BEGIN: file -->
				<li><a href="{file.url}" target="_blank">{file.name}</a>
				<!-- END: file -->
			</ul>
		</fieldset>
		<!-- END: files -->

		<fieldset class="noprint">
			<legend>Hardware Info</legend>
			<div id='hardwareInfoDiv'>
				{hardware_info}
			</div>
		</fieldset>

		<fieldset class="noprint">
			<legend>Call History</legend>
			<div id="CallHistoryDiv">
				{call_history_summary}
			</div>
		</fieldset>

		<fieldset class="noprint">
			<legend onClick="calllog_toggle('restore_request');">
			<span id="restore_request_symbol">+</span> Restore Request</legend>
			<div id="restore_request_div">
				{restore_request_func}
			</div>
		</fieldset>
	</div>

	<div class="grid_10 omega">
		<!-- BEGIN: call_closed -->
		<div class="message message-warnings">This call has been closed.</div>
		<!-- END: call_closed -->

		<fieldset class="noprint">
			<legend>{details_name}</legend>
			{ticket_form}
			<label class="label">Keywords:</label>
			<input type="text" name="keywords_list" id="keywords_list" size="51" value="{call_assignment_history_keywords}"/>
			<div id="keywordsList"></div>
		</fieldset>
		
		<!-- BEGIN: call_assignment_check -->
		<fieldset>
			<legend>Assignment History</legend>
			Re-order: <a href="javascript: reorder_assign_history('old', '{person.username}', {call_id});" class="btn">Old-New</a> | <a href="javascript: reorder_assign_history('new', '{person.username}', {call_id});" class="btn">New-Old</a><br /><br />
			<div id="call_assignment_history">
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
			</div>
		</fieldset>
		<!-- END: call_assignment_check -->

		<fieldset class="noprint">
			<legend>Call Information</legend>
			<div id="call_information_div">
				{call_information}
			</div>
		</fieldset>

		<fieldset class="noprint">
			<legend>Assign Call To</legend>
			<div id="assign_to_div" style="display: block;">
				{call_assigned_to}
			</div>
		</fieldset>

		<div id="submit_reset" class="well noprint">
			<a href="#" class="btn primary" id="new_call_submit_button" name="new_call_submit_button">Submit Call</a>
		</div>

	</div>

</form>
</body>
<!-- END: new_call_user_name -->

<!-- END: main -->
