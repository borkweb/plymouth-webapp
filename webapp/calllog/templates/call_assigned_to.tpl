<!-- BEGIN: main -->
<div align="center">

	<span class="label">Assign To</span>
	<select name="tlc_assigned_to" id="tlc_assigned_to" onChange="change_status('tlc_assigned_to');">
		<option value="unassigned">Unassigned</option>
		<option value="caller">The Caller</option>
		{tlc_select_list}
	</select>

	<br style="clear: left;">

	<span class="label">ITS Group</span>
	<select name="its_assigned_group" id="its_assigned_group" onChange="change_status('its_assigned_group');" >
		<option value="0">Unassigned</option>
		{its_select_group_list}
	</select>
	<small>(<a href="{PHP.BASE_URL}/images/help-desk-small.png" id="its-group-help">?</a>)</small>
</div>
<!-- END: main -->