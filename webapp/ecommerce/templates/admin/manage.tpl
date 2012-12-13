<div class="block">

	<h2>{$page_title}</h2>
	
	{if $success}
		<div class="styled_notice">
			<h2>Process successfully {$success}ed.</h2>
		</div>
	{/if}

	{if $processes}
		<table class="grid">
			<tr>
				<th>Name</th>
				<th>Code</th>
				<th>Type</th>
				<th>Class</th>
				<th>Action</th>
			</tr>
			{foreach from=$processes item=process}
				<tr>
					<td>{$process.name}</td>
					<td>{$process.code}</td>
					<td>{$process.type}</td>
					<td>{$process.class}</td>
					<td><a href="{$PHP.BASE_URL}/admin/manage.html?edit={$process.id}#edit" class="button">Edit</a> / <a href="{$PHP.BASE_URL}/admin/manage.html?delete={$process.id}" class="button" onClick="javascript:return confirm('Are you sure you want to delete that process?')">Delete</a></td>
				</tr>
			{/foreach}
			<tr>
				<td colspan=5><a href="{$PHP.BASE_URL}/admin/manage.html?add=1#edit" class="button">Add Process</a></td>
			</tr>
		</table>

	{else}		
		<div class="styled_notice">
			<h2>There is no data to be displayed in the management section at this time.</h2>
		</div>
	{/if}

</div>
