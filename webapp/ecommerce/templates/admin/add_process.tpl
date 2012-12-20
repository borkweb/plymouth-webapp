{box title="Add Process" size="16"}
	<fieldset>
		<legend>Add Process</legend>
		
		{foreach from=$errors item=error}
			<div class="error">{$error}</div>
		{/foreach}

		<form method="post" action="{$PHP.BASE_URL}/manage.html?add=1#edit">
			<ul>
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
					<input type="submit" value="Submit" />
				</li>
			</ul>
		</form>
	</fieldset>
{/box}
