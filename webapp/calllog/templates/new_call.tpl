{box class="noprint"}
	{if 'searchUser' == $go_back.action }
	<a href="{$PHP.BASE_URL}/index.html?search_type={$go_back.find_type}&amp;search_string={$go_back.option}" class="btn btn-danger">&laquo; Back to {$go_back.name}</a>
	{else}
	<a href="{$PHP.BASE_URL}/calls.html?action={$go_back.action}&option={$go_back.option}&group={$go_back.group}&find_type={$go_back.find_type}" class="btn btn-danger">&laquo; Back to {$go_back.name}</a>
	{/if}
{/box}

{if $call_id}
	<form name="new_call" method="post" action="{$PHP.BASE_URL}/update_call_details.html" id="edit_call" enctype="multipart/form-data">
	<input type="hidden" name="call_history_id" value="{$call_history_id}" />
	<input type="hidden" name="call_id" id="call_id" value="{$call_id}" />
	<input type="hidden" name="action" id="action" value="{$go_back.action}" />
	<input type="hidden" name="option" id="option" value="{$go_back.option}" />
	<input type="hidden" name="group" id="group" value="{$go_back.group}" />
	<input type="hidden" name="find_type" id="find_type" value="{$go_back.find_type}" />
	<input type="hidden" name="page" id="page" value="{$go_back.page}" />
{else}
	<form name="new_call" method="post" action="{$PHP.BASE_URL}/add_new_call.html" id="new_call" enctype="multipart/form-data">
{/if}

{col size=6 id="call-information"}
	{box title="Caller Information"}
		<div id="caller_information_div">
			{$caller_information}
		</div>
		<div class="center">
			<a href=#" id="change-caller-toggle" class="replace-toggle">Re-attach Call</a>
			<div id="change_caller" style="display:none;">
				Change Caller To: <input type="text" name="attach_to"/><br/>
				<small><em>Enter username to re-attach this ticket</em></small>
			</div>
		</div>
	{/box}

	{box title="Files" class="noprint"}
		<input type="hidden" name="MAX_FILE_SIZE" value="3145728" />
		Attach File: <input type="file" name="attachment"/><br/>
		<small><em>Supports: .doc, .docx, .gif, .jpg, .pdf, .png,<br/>.txt, .xls., .xlsx (3MB maximum)</em></small>
		{if $files}
		<h3>Attached Files</h3>
		<ul>
			{foreach from=$files item=file}
				<li><a href="{$file.url}" target="_blank">{$file.name}</a>
			{/foreach}
		</ul>
		{/if}
	{/box}

	{box title="Hardware Info" class="noprint"}
		<div id='hardwareInfoDiv'>
			{$hardware_info}
		</div>
	{/box}

	{box title="Ticket History" id="call-history" class="noprint"}
		{$call_history_summary}
	{/box}

	{box title="Restore Request" class="noprint"}
		<a href="#" class="replace-toggle">Enter Restore Request</a>
		<div id="restore_request_div">
			{$restore_request_func}
		</div>
	{/box}
{/col}

{col size=10}
	{box title=$details_name}
		{include file="ticket_form.tpl"}
		<label class="label">Keywords:</label>
		<input type="text" name="keywords_list" id="keywords_list" size="51" value="{$call_assignment_history_keywords}"/>
		<div id="keywordsList"></div>
	{/box}

	{if $history}
		{box title="Assignment History"}
			Re-order: 
				<a href="javascript: reorder_assign_history('old', '{$person.username}', {$call_id});" class="btn">Old-New</a> | 
				<a href="javascript: reorder_assign_history('new', '{$person.username}', {$call_id});" class="btn">New-Old</a>
			<br /><br />
			<div id="call_assignment_history">
			{include file="ticket-history.tpl"}
			</div>
		{/box}
	{/if}
		
	{box title="Ticket Information" class="noprint"}
		<div id="call_information_div">
			{$call_information}
		</div>
	{/box}

	{box title="Assign Ticket To" class="noprint"}
		<ul>
			<li class="center">
				<label for="tlc_assigned_to" class="inline">Assign To</label>
				<select name="tlc_assigned_to" id="tlc_assigned_to" onChange="change_status('tlc_assigned_to');">
					<option value="unassigned">Unassigned</option>
					<option value="caller">The Caller</option>
					{$tlc_select_list}
				</select>
			</li>
			<li class="center">
				<label for="its_assigned_group" class="inline">ITS Group</label>
				<select name="its_assigned_group" id="its_assigned_group" onChange="change_status('its_assigned_group');" >
					<option value="0">Unassigned</option>
					{$its_select_group_list}
				</select>
				(<a href="{$PHP.BASE_URL}/images/help-desk-small.png" id="its-group-help" title="ITS Group Responsibilities">?</a>)
			</li>
			<li class="form-actions center">
			<button type="submit" class="btn primary">{if $call_id}Update{else}Submit{/if} Ticket</button>
			</li>
		</ul>
	{/box}
{/col}
</form>
