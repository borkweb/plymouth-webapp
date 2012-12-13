<div class="block">

	<h2 id="edit">Edit Process</h2>
	<fieldset>
		<legend>Editing</legend>
		
		{foreach from=$errors item=error}
			<div class="error">{$error}</div>
		{/foreach}

		<form method="post" action="{$PHP.BASE_URL}/admin/manage.html?edit={$process.id}">
			<ul>
				<li>
					<label>Process ID:</label>
					<span>{$process.id}</span>
					<input type="hidden" name="id" value="{$process.id}" />
				</li>
				<li>
					<label>Process Name:</label>
					<input type="text" name="name" value="{$process.name}" />
				</li>
				<li>
					<label>Process Code:</label>
					<input type="text" name="code" value="{$process.code}" />
				</li>
				<li>
					<label>Process Type:</label>
					<input type="text" name="type" value="{$process.type}" />
				</li>
				<li>
					<label>Process Class:</label>
					<input type="text" name="class" value="{$process.class}" />
				</li>
				<li>
					<input type="submit" value="Update" />
				</li>
			</ul>
		</form>
	</fieldset>
</div>
