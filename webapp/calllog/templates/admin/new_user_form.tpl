<!-- BEGIN: main -->
<script>
var group_options = '{js_its_select_group_list}';
</script>

<form name="tlc_user_add" id="tlc_user_add" method="post" action="{form_action}">
<input type="hidden" id="user_id" value="{key.call_log_user_id}">
<fieldset>
	<legend>Add/edit User</legend>
	<ul>
		<li><label class="required">User ID:</label> {key.call_log_user_id}</li>
		<li><label class="required">User Name:</label> <input type="text" name="calllog_username" id="calllog_username" size="40" value="{key.user_name}"/></li>
		<li><label class="required">Last Name:</label> <input type="text" name="last_name" id="last_name" size="40" value="{key.last_name}" /></li>
		<li><label class="required">First Name</label> <input type="text" name="first_name" id="first_name" size="40" value="{key.first_name}" /></li>
		<li><label class="required">Position</label> <input type="text" name="position" id="position" size="40" value="{key.position}" /></li>
		<li><label class="required">Work Phone</label> <input type="text" name="work_phone" id="work_phone" size="20" value="{key.work_phone}" /></li>
		<li><label class="required">Cell Phone</label> <input type="text" name="cell_phone" id="cell_phone" size="20" value="{key.cell_phone}" /></li>
		<li><label class="required">Home Phone</label> <input type="text" name="home_phone" id="home_phone" size="20" value="{key.home_phone}" /></li>
		<li>
			<label class="required">Class</label>
			<select name="class_options" id="class_options">
			<option>Select Class</option>
			{class_options}
			</select>
		</li>
		<li>
			<label class="required">Comments</label>
			<textarea name="comments" id="comments" cols="39" rows="7">{key.comments}</textarea>
		</li>
		<li>
			<label class="required">User Privileges</label>
			<select name="user_privileges" id="user_privileges">
			<option>Select Privileges</option>
			{tlc_employee_positions}
			</select>
		</li>
		<li>
			<label class="required">Signed FERPA
			<select name="ferpa_select" id="ferpa_select">
				<option>Select Yes/No</option>
				{signed_ferpa}
			</select>
		</li>
		<li>
			<label class="required">User Status
			<select name="status" id="status">
			<option>Select Status</option>
			{user_status}
			</select>
		</li>
		<li class="well">
			<!-- BEGIN: add_tlc_user -->
			<a href="javascript:void(0);" onClick="addUserProfile();" title="Add TLC User." class="btn"> ++ Add User </a>
			<!-- END: add_tlc_user -->
			<!-- BEGIN: update_tlc_user -->
			<a href="javascript: void(0);" onClick="updateUserProfile({i});" title="Update TLC User." class="btn"> >> Update User </a>
			<!-- END: update_tlc_user -->		
			&nbsp;&nbsp;
			<a href="manage_users.html?display=activeusers" title="Cancel." class="btn danger"> &#151; Cancel &#151; </a>
		</li>
	</ul>
</fieldset>
</form>
<!-- END: main -->
