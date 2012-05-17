{if !$is_running}
{box size="16" title="Verify SPRADDR"}
<form method="post" action="">

<ul>
	<li>
		<label>Do you want this run to update records that it can update?</label>
		<ul>
			<li><input type="radio" name="fb_update" value="true" id="fb_update_true" /> <label class="control-label" for="fb_update_true">Yes, update records</label></li>
			<li><input type="radio" name="fb_update" value="false" id="fb_update_false" checked /> <label class="control-label" for="fb_update_false">No, leave records alone</label></li>
		</ul>
	</li>
	<li>
	<label>Scan only one address type?</label>
		<ul>
			<li><input type="radio" name="fv_address_type" id="fv_address_type_null"value="null" checked/> <label class="control-label" for="fv_address_type_null">Scan all <em>eligible</em> types</label></li>
			<li><input type="radio" name="fv_address_type" id="fv_address_type_true" value="true" /> <label class="control-label" for="fv_address_type_true">Scan this address type
			<li><input type="text" name="fv_address_type_val" size="4" maxlength="2" /></label></li>
		</ul>
	</li>
	<li>
	<label>Only look at addresses updated in the last number of days?</label>
		<ul>
			<li><input type="radio" name="fn_days_back" id="fn_days_back_null" value="null" checked/> <label class="control-label" for="fn_days_back_null">Do not restrict how far back we scan</label></li>
			<li><input type="radio" name="fn_days_back" id="fn_days_back_true" value="true" /> <label class="control-label" for="fn_days_back_true">Scan records added/updated in the last 
			<li><input type="text" name="fn_days_back_val" size="4" maxlength="2" /><label class="control-label">  days</label></li>
		</ul>
	</li>
	<li>
	<label>Do you want to restrict the scan to a date range (leave blank to ignore)</label>
		<ul>
			<li><label class="control-label">Begin Date: <input type="text" class="datepicker" name="fd_from_date" /></label></li>
			<li><label class="control-label">End Date: <input type="text" class="datepicker" name="fd_to_date" /></label></li>
		</ul>
	</li>
	<li>
	<label>Do you want scan only unverified records?</label>
		<ul>
			<li><input type="radio" name="fb_only_unverified" id="fb_only_unverified_true" value="true" /> <label class="control-label" for="fb_only_unverified_true">Yes, scan only unverified records</label></li>
			<li><input type="radio" name="fb_only_unverified" id="fb_only_unverified_false" value="false" checked /> <label class="control-label" for="fb_only_unverified_false">No, scan all records</label></li>
		</ul>
	</li>
	<li>
	<label>Do you want to skip international addresses?</label>
		<ul>
			<li><input type="radio" name="fb_skip_international" id="fb_skip_international_true" value="true" checked /> <label class="control-label" for="fb_skip_international_true">Yes, skip international records in this scan</label></li>
			<li><input type="radio" name="fb_skip_international" id="fb_skip_international_false" value="false" /> <label class="control-label" for="fb_skip_international_false">No, include international records</label></li>
		</ul>
	</li>
	<li>
	<label>Do you want to scan inactive addresses?</label>
		<ul>
			<li><input type="radio" name="fb_verify_inactive" id="fb_verify_inactive_true" value="true" /> <label class="control-label" for="fb_verify_inactive_true">Yes, scan inactive records</label></li>
			<li><input type="radio" name="fb_verify_inactive" id="fb_verify_inactive_false" value="false" checked /> <label class="control-label" for="fb_verify_inactive_false">No, scan only active records</label></li>
		</ul>
	</li>
	<li>
		<ul>
			<li><input type="submit"></li>
		</ul>
	</li>
</ul>
</form>
{/box}
{/if}
