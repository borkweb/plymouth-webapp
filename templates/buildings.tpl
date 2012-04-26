{box title="Buildings"}
	<table class="grid">
		<thead>
			<th>Building Index</th>
			<th>Building Name</th>
			<th>Remove</th>
		</thead>
		<tbody>
		{foreach from=$buildings key=id item=building}
		<tr>
			<td>{$id}</td>
			<td>{$building}</td>
	<td><a class="btn btn-danger" href="{$PHP.BASE_URL}/admin/admincp/buildings/{$id}/delete">Delete</a><td>
		</tr>
		{/foreach}
		</tbody>
	</table>
	<input type="Submit" value="Save">
{/box}

{box title="Add New Building"}
<form class="label-left" action="{$PHP.BASE_URL}/admin/admincp/buildings/add" method="POST">
<ul>
	<li><label class="required"><em>*</em>Building Name: </label><input type="text" name="building_name"></li>
	<li><input type="Submit" name="submit" value="Add New"></li>
</ul>
</form>
{/box}
