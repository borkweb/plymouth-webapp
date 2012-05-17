{if !$is_running}
{box size="16" title="Verify SPRADDR"}
<form method="post" action="">

<label>Do you want this run to update records that it can update?</label>
<input type="radio" name="fb_update" value="true" /> Yes, update records<br />
<input type="radio" name="fb_update" value="false" checked /> No, leave records alone<br />
<br />
<label>Scan only one address type?</label>
<input type="radio" name="fv_address_type" value="null" checked/> Scan all <em>eligible</em> types<br />
<input type="radio" name="fv_address_type" value="true" /> Scan this address type
<input type="text" name="fv_address_type_val" size="4" maxlength="2" /><br />
<br />
<label>Only look at addresses updated in the last number of days?</label>
<input type="radio" name="fn_days_back" value="null" checked/> Do not restrict how far back we scan<br />
<input type="radio" name="fn_days_back" value="true" /> Scan records added/updated in the last 
<input type="text" name="fn_days_back_val" size="4" maxlength="2" /> days<br />
<br />
<label>Do you want to restrict the scan to a date range (leave blank to ignore)</label>
Begin Date: <input type="text" class="datepicker" name="fd_from_date" />
End Date: <input type="text" class="datepicker" name="fd_to_date" /><br />
<br />
<label>Do you want scan only unverified records?</label>
<input type="radio" name="fb_only_unverified" value="true" /> Yes, scan only unverified records<br />
<input type="radio" name="fb_only_unverified" value="false" checked /> No, scan all records<br />
<br />
<label>Do you want to skip international addresses?</label>
<input type="radio" name="fb_skip_international" value="true" checked /> Yes, skip international records in this scan<br />
<input type="radio" name="fb_skip_international" value="false" /> No, include international records<br />
<br />
<label>Do you want to scan inactive addresses?</label>
<input type="radio" name="fb_verify_inactive" value="true" /> Yes, scan inactive records<br />
<input type="radio" name="fb_verify_inactive" value="false" checked /> No, scan only active records<br />
<br />
<input type="submit">
</form>
{/box}
{/if}
