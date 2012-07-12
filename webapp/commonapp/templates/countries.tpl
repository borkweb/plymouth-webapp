{box title="Upload Countries CSV"}
	<form enctype="multipart/form-data" action="{$PHP.BASE_URL}/actions/countries-upload.php" method="POST">
		<input type="hidden" name="MAX_FILE_SIZE" value="512000" />
		<p><input name="uploadedfile" type="file" /><input type="submit" value="Upload" /><p>
	</form>
{/box}

{box title="Country Translation Table Status"}
	<ul>
		<li>{$matches} countries were matched.</li>
		<li>{$failures|@count} countries failed to match.</li>
		<li>{$duplicates|@count} duplicate countries found in STVNATN.</li>
	</ul>
	<h3>Failures</h3>
	<ul>
		{foreach from=$failures item=failure}
			<li>{$failure.country} ({$failure.coa_code})</li>
		{/foreach}
	</ul>
	<h3>STVNATN Duplicates</h3>
	<table class="grid">
		<thead>
			<tr>
				<th>STVNATN_NATION</th>
				<th>STVNATN_CODE</th>
				<th>STVNATN_CAPITAL</th>
				<th>STVNATN_SCOD_CODE_ISO</th>
				<th>STVNATN_ACTIVITY_DATE</th>
			</tr>
		</thead>
		{foreach from=$duplicates item=dup}
			<tr>
				<td>{$dup.stvnatn_nation}</td>
				<td>{$dup.stvnatn_code}</td>
				<td>{$dup.stvnatn_capital}</td>
				<td>{$dup.stvnatn_scod_code_iso}</td>
				<td>{$dup.stvnatn_activity_date|date_format:"%m/%d/%Y"}</td>
			</tr>
		{/foreach}
	</table>
{/box}
