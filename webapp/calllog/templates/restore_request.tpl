<!-- BEGIN: main -->
	Server: 
	<select id='restore_system' name='restore_system'>
	<option>Select Server</option>
	<option>-------------</option>
	{restore_system_options}
	</select>

	<br/>
	Path: <input type='text' id='restore_path' name='restore_path' size='40' value='{restore_path}'/>
	<br/>&nbsp;&nbsp;&nbsp;just list folders inside Drive
	<br/>&nbsp;&nbsp;&nbsp;ex: Dir1/Dir2 not \\server\jpseudo\Dir1\Dir2

	<br/>
	Filenames: <input type='text' id='restore_filenames' name='restore_filenames' size='40' value='{restore_filenames}'/>

	<br/>
	Last Known Good:<br/>Date: 
	<select name='restore_month' id='restore_month'>
	{date_time_month_options}
	</select>
	/
	<select name='restore_date' id='restore_date'>
	{date_time_date_options}
	</select>
	/
	<select name='restore_year' id='restore_year'>
	{date_time_year_options}
	</select>
	<br/>
	Time:
	<select name='restore_hour' id='restore_hour'>
	{date_time_hour_options}
	</select>
	:
	<select name='restore_minute' id='restore_minute'>
	{date_time_minute_options}
	</select>
	<br/>
	Additional Details: 
	<textarea name='restore_details' id='restore_details' cols='40' rows='2'>{restore_details}</textarea>
	<br/>
	<a href="javascript: void(0);" onClick="restoreRequest();" name="restore_submit" class="action btn">Submit Restore</a>
<!-- END: main -->
