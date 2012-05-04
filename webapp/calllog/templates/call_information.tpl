<!-- BEGIN: main -->
<div align="center">
	<div class="call-information">
	<label for="call_priority" class="label">Call Priority</label><br/>
	<select name="call_priority" id="call_priority">
	{call_priority_select_list}
	</select>
	</div>

	<div class="call-information">
	<label for="call_status" class="label">Resnet Call? </label><br/>
	<input type="checkbox" name="resnet_check" id="resnet_check" {is_resnet}/>
	</div>

	<div class="call-information">
	<label for="call_status" class="label">Call Status</label><br/>
	<select name="call_status" id="call_status">
	{call_status_select_list}
	</select>
	</div>

	<div class="call-information call-information-building">
	<label for="call_location" class="label">Call Location</label><br/>
	<select name="call_location">
	<option value="n/a">No Building Selected</option>
	{building_select_list}
	</select>
	</div>
</div>
<!-- END: main -->