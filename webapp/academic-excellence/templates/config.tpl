{* Called by index.tpl if is_admin is true *}

{box title="Site Configuration"}
<form method="post" action="{$PHP.BASE_URL}/admin_update_config.php" class="label-left">
	<ul>
		<li>
			<label>Term:</label>
			<input type="text" name="term" maxlength="6" size="6" value="{$PHP.TERM|escape|truncate:6}"/>
		</li>
		<li>
			<label>Acceping data?</label>
			<select name="accepting">
				<option value="1" {if $PHP.ACCEPTING_DATA == true}selected="selected"{/if}>Yes</option>
				<option value="0" {if $PHP.ACCEPTING_DATA == false}selected="selected"{/if}>No</option>
			</select>
		</li>
		<li>
			<label>Dinner date:</label>
			<input type="text" size="15" name="dinner" value="{$PHP.DINNER_DATE|date_format:'%Y-%m-%d'}"/> (YYYY-MM-DD)
		</li>
		<li>
			<label>&nbsp;</label>
			<input type="submit" name="save" value="Save changes">
		</li>
	</ul>
	<p><a href="{$PHP.BASE_URL}">Home</p>
</form>
{/box}
