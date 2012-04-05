{box title="Subitems"}
<table class="grid">
	<thead>
		<th>ID</th>
		<th>Item</th>
		<th>Remove</th>
	</thead>
	<tbody>
	{foreach from=$subitems key=k item=device}
	<tr>
		<td>{$k}</td>
		<td>{$device}</td>
		<td><a href="{$PHP.BASE_URL}/admin/admincp/subitems/{$k}/remove">Remove</a></td>
	</tr>
	{/foreach}
	</tbody>
</table>
{/box}
{box title="Add New SubItem"}
<form class="label-left" action="{$PHP.BASE_URL}/admin/admincp/subitems/add" method="POST">
<ul>
	<li><label class="required"><em>*</em>SubItem Name: </label><input type="text" name="new_subitem" size="35"></li>
	<li><input type="Submit" name="submit" value="Add New"></li>
</ul>
</form>
{/box}
