<!-- BEGIN: main -->
<script>
var group_options = '{js_its_select_group_list}';
</script>
<form name="tlc_user_add" id="tlc_user_add" method="post" action="{form_action}">
<input type="hidden" id="user_id" value="{key.call_log_user_id}">
<table width="100%" align="left" valign="top" cellpadding="3" cellspacing="1" id="editUserTable">
<tbody id="junk">
<tr>
<th align="left" valign="top" class="content_width">
	User ID
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<label class="label">{key.call_log_user_id}</label>
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	User Name
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="calllog_username" id="calllog_username" size="40" value="{key.user_name}"/>
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Last Name
</th>
<td width="550" align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="last_name" id="last_name" size="40" value="{key.last_name}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	First Name
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="first_name" id="first_name" size="40" value="{key.first_name}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Position
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="position" id="position" size="40" value="{key.position}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Work Phone
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="work_phone" id="work_phone" size="20" value="{key.work_phone}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Cell Phone
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="cell_phone" id="cell_phone" size="20" value="{key.cell_phone}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Home Phone
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<input type="text" name="home_phone" id="home_phone" size="20" value="{key.home_phone}" />
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	Class
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<select name="class_options" id="class_options">
	<option>Select Class</option>
	{class_options}
	</select>
</td>
</tr>

<tr>
<th align="left" valign="top" class="content_width">
	Comments
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<textarea name="comments" id="comments" cols="39" rows="7">{key.comments}</textarea>
</td>
</tr>
<tr>
<th align="left" valign="top" class="content_width">
	User Privileges
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<select name="user_privileges" id="user_privileges">
	<option>Select Privileges</option>
	{tlc_employee_positions}
	</select>
</td>
</tr>

<tr>
	<th align="left" valign="top" class="content_width">
		Signed FERPA
	</th>
	<td align="left" valign="top" class="content" nowrap="nowrap">
		<select name="ferpa_select" id="ferpa_select">
		<option>Select Yes/No</option>
		{signed_ferpa}
		</select>
	</td>
</tr>

<tr>
<th align="left" valign="top" class="content_width">
	User Status
</th>
<td align="left" valign="top" class="content" nowrap="nowrap">
	<select name="status" id="status">
	<option>Select Status</option>
	{user_status}
	</select>
</td>
</tr>
<tr>
<th width="100%" valign="top" colspan="2">
	<!-- BEGIN: add_tlc_user -->
	<a href="javascript:void(0);" onClick="addUserProfile();" title="Add TLC User." class="action"> ++ Add User </a>
	<!-- END: add_tlc_user -->
	<!-- BEGIN: update_tlc_user -->
	<a href="javascript: void(0);" onClick="updateUserProfile({i});" title="Update TLC User." class="action"> >> Update User </a>
	<!-- END: update_tlc_user -->		
	&nbsp;&nbsp;
	<a href="tlc_users_admin.html?display=activeusers" title="Cancel." class="action"> &#151; Cancel &#151; </a>	
</th>
</tr>
</tbody>
</table>
</form>
<!-- END: main -->