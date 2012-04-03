{box title="Equipment"}
<table class="grid">
	<thead>
		<th>ID</th>
		<th>Equipment</th>
		<th>Equipment Description</th>
		<th>Remove</th>
	</thead>
	<tbody>
	{foreach from=$categories key=k item=device}
	<tr>
		<td>{$k}</td>
		<td>{$device.category_name}</td>
		<td>{$device.description}</td>
		<td><a href="{$PHP.BASE_URL}/admin/admincp/equipment/{$k}/remove">Remove</a></td>
	</tr>
	{/foreach}
	</tbody>
</table>
{/box}
{box title="Add New Equipment Item"}
<form class="label-left" action="{$PHP.BASE_URL}/admin/admincp/equipment/add" method="POST">
<ul>
	<li><label class="required"><em>*</em>Equipment: </label><input type="text" name="new_equipment" size="35"></li>
	<li><label class="required"><em>*</em>Description: </label><textarea rows="5" cols="50" name="description"></textarea></li>
	<li><input type="Submit" name="submit" value="Add New"></li>
</ul>
</form>
{/box}
