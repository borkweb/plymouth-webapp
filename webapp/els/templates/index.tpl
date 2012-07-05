{box title="ELS Roster"}
	{if $roster_file}
		<ul>
			<li><a href="{$PHP.BASE_URL}/roster/{$roster_file.name|escape:'urlpathinfo'}">{$roster_file.name|escape}</a> ({$roster_file.size} bytes)</li>
			<li>Uploaded {$roster_file.uploaded|date_format} by {$roster_file.uploader->formatName('f l')}.</li>
		</ul>
	{else}
		<p>No roster file has been uploaded.</p>
	{/if}
	<h3>Upload</h3>
	<form action="{$PHP.BASE_URL}/upload.php" method="post" enctype="multipart/form-data">
		<input id="roster" name="roster" type="file">
		<input type="submit" value="Upload" />
	</form>
{/box}

{box title="ELS Students"}
	<table class="grid">
		<tr>
			<th>ID Card</th>
			{if $AUTHZ.permission.ape_limited_identifiers}
			<th>Pidm</th>
			{/if}
			<th>PSU ID</th>
			<th>Username</th>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Certification Number</th>
			<th>Marked as ELS</th>
			<th>Account Created</th>
		</tr>
		{foreach from=$students item=student}
			<tr>
				<td>
					<img width="98" height="130" src="{$student.idcard}">
				</td>
				{if $AUTHZ.permission.ape_limited_identifiers}
				<td>
						<a href="http://go.plymouth.edu/ape/{$student.pid}">{$student.pid}</a>
				</td>
				{/if}
				<td>{$student.psu_id}</td>
				<td>{$student.username}</td>
				<td>{$student.first_name}</td>
				<td>{$student.last_name}</td>
				<td>{$student.certification_number}</td>
				<td>{$student.start_date|date_format}</td>
				<td>{$student.account_creation_date|date_format}</td>
			</tr>
		{/foreach}
	</table>
{/box}
