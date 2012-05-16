<!-- BEGIN: main -->
<div align="center">

	<label for="tlc_assigned_to" class="inline">Assign To</label>
	<select name="tlc_assigned_to" id="tlc_assigned_to" onChange="change_status('tlc_assigned_to');">
		<option value="unassigned">Unassigned</option>
		<option value="caller">The Caller</option>
		{tlc_select_list}
	</select>

	<br style="clear: left;">

	<label for="its_assigned_group" class="inline">ITS Group</label>
	<select name="its_assigned_group" id="its_assigned_group" onChange="change_status('its_assigned_group');" >
		<option value="0">Unassigned</option>
		{its_select_group_list}
	</select>
	<small>(<a href="{PHP.BASE_URL}/images/help-desk-small.png" id="its-group-help" title="ITS Group Responsibilities">?</a>)</small>
</div>
<!-- END: main -->
