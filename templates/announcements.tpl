{box size = 16 title="Announcements"}

<form action="{$PHP.BASE_URL}/admin/admincp/announcements/save" method="POST">
	<table class="table table-bordered table-striped">
		<thead>
			<th>Message</th>
			<th>Viewable</th>
			<th>Edit</th>
			<th>Remove</th>
		</thead>
		<tbody>
		{foreach from=$announcements key=id item=announcement}
		<tr>
			<td>{$announcement.message}</td>
			{if $announcement.form_viewable == "no"}
				<td><input type="checkbox" name="{$id}"></td>
			{else}
				<td><input type="checkbox" name="{$id}" checked></td>
			{/if}

			<td><a class="btn" href="{$PHP.BASE_URL}/admin/admincp/announcements/{$id}/edit">Edit</a></td>
	<td><a class="btn btn-danger" href="{$PHP.BASE_URL}/admin/admincp/announcements/{$id}/delete">Delete</a><td>
		</tr>
		{/foreach}
		</tbody>
	</table>
	<input type="Submit" value="Save">
</form>
{/box}

{box title="Add New Announcement"}
<form class="label-left" action="{$PHP.BASE_URL}/admin/admincp/announcements/add" method="POST">
<ul>
	<li><label class="required"><em>*</em>Message: </label><textarea rows="5" cols="50" name="message"></textarea></li>
	<li class="form-actions"><input type="Submit" name="submit" value="Add New"></li>
</ul>
</form>
{/box}
