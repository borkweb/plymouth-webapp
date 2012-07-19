	{col size=4}
		<div class="alpha">
			<label for="call_priority" class="label">Call Priority</label>
			<select name="call_priority" id="call_priority">
			{call_priority_select_list}
			</select>
		</div>
	{/col}

	{col size=2}
		<div class="grid_2">
			<label for="call_status" class="label">Resnet Call? </label>
			<input type="checkbox" name="resnet_check" id="resnet_check" {is_resnet}/>
		</div>
	{/col}

	{col size=4}
		<div class="grid_4 omega">
			<label for="call_location" class="label">Call Location</label>
			<select name="call_location">
			<option value="n/a">No Building Selected</option>
			{building_select_list}
			</select>
		</div>
	{/col}
	<div class="clear"></div>
