{col size=8}
{box title="Statistics"}
	<ul>
		<li>Students visiting: {$users_visiting}</li>
		<li>Confirmations: {$confirmed}</li>
		<li>Most recent update: {$last_update}</li>
	</ul>
{/box}

{box title="Links"}
	<ul>
		<li><a href="{$PHP.BASE_URL}/aeconfirm_{$PHP.TERM}.csv">Download .csv</a></li>
		<li><a href="{$PHP.BASE_URL}/config.html">Edit site options</a></li>
	</ul>
{/box}
{/col}

{col size=8}
{box title="Previews"}
	<ul>
		<li><a href="{$PHP.BASE_URL}/preview.html?low_gpa=1">GPA &lt; 3.5</a></li>
		<li><a href="{$PHP.BASE_URL}/preview.html?closed=1">Not accepting data</a></li>
	</ul>
	<form action="preview.html" method="get" class="label-left">
	<ul class="form_fields">
		<li>
			<label>Semester:</label>
			<select name="summer" id="summer">
				<option value="1">Spring</option>
				<option value="0">Fall</option>
			</select>
		</li>
		<li>
			<label>Still editing?</label>
			<select name="editing" id="editing">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
		</li>
		<li id="event">
			<label>Event:</label>
			<select name="confirmed" id="confirmed">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
			<span>No response</span>
		</li>
		<li>
			<label>Certificate:</label>
			<select name="confirmed_cert" id="confirmed_cert">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
			<span>No response</span>
		</li>
		<li>
			<label>&nbsp;</label>
			<input type="submit" value="View Page"/>
	</ul>
	</form>
{/box}
{/col}
